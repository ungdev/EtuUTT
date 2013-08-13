<?php

namespace Etu\Module\BuckUTTBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Module\BuckUTTBundle\Soap\SoapManager;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
	/**
	 * @Route("/buckutt/history", name="buckutt_history")
	 * @Template()
	 */
	public function indexAction()
	{
		if (! $this->getUserLayer()->isConnected()) {
			return $this->createAccessDeniedResponse();
		}

		if (! $this->get('session')->get(SoapManager::cookie_name)) {
			return $this->redirect($this->generateUrl('buckutt_connect'));
		}

		$history = array();
		$history_dates = array();
		/* $history -> array($type, $date, $obj_name, $poi_name, $fun_name, $price)
		type= rec/buy */

		define('DATE_FORMAT', 'Y-m-d H:i');

		$startTime = time() - (365*24*3600);// Whole last year
		$endTime = time();

		$clientSADMIN = new SoapManager('SADMIN', $this->get('session'));
		$achats = $clientSADMIN->getHistoriqueAchats($startTime, $endTime);

		if ((int) $achats == 400) {
			$achats = array();
		}

		foreach ($achats as $a){
			$history[] = array(
				'type' => 'buy',
				'date' => date(DATE_FORMAT, $a[0]), // pur_date
				'user' => ($a[2].' '.$a[3]), // usr_firstname  usr_lastname
				'object' => $a[1], // obj_name
				'point' => $a[4], // poi_name
				'fundation' => $a[5], // fun_name
				'amount' => number_format($a[6]/100, 2) // pur_price
			);

			$history_dates[] = $a[0];
		}

		$recharges = $clientSADMIN->getHistoriqueRecharge($startTime, $endTime);
		//-> array($don['rec_date'], $don['rty_name'], $don['usr_firstname'], $don['usr_lastname'], $don['poi_name'], $don['rec_credit'])

		if ((int) $recharges == 400) {
			$recharges = array();
		}

		foreach($recharges as $r){
			$history[] = array(
				'type' => 'rec',
				'date' => date(DATE_FORMAT, $r[0]), // pur_date
				'user' => null,
				'object' => $r[1], // obj_name
				'point' => $r[4], // poi_name
				'fundation' => null,
				'amount' => number_format($r[5]/100, 2) // pur_price
			);

			$history_dates[] = $r[0];
		}

		array_multisort($history_dates, SORT_DESC, $history);

		$SBUY = new SoapManager('SBUY', $this->get('session'));
		$credit = $SBUY->getCredit();

		$name = $this->getUser()->getFullName();

		$history = $this->get('etu.buckutt.layer')->getHistoryBetween(new \DateTime('1 year ago'), new \DateTime());
		$history = array_slice($history, 0, 20);

		return array(
			'name' => $name,
			'credit' => number_format($credit / 100, 2),
			'history' => $history
		);
	}

	/**
	 * @Route("/buckutt/connect/{action}", name="buckutt_connect", defaults={"action" = "connect"})
	 * @Template()
	 */
	public function connectAction($action)
	{
		if (! $this->getUserLayer()->isConnected()) {
			return $this->createAccessDeniedResponse();
		}

		if ($action == 'disconnect'){
			$this->get('session')->remove(SoapManager::cookie_name);
			return $this->redirect($this->generateUrl('buckutt_history'));
		}

		$form = $this->createFormBuilder()
			->add('pin', 'password', array('required' => true, 'max_length' => 4))
			->getForm();

		if ($form->bind($this->getRequest())->isValid()) {

			$SBUY = new SoapManager('SBUY', $this->get('session'));
			$SADMIN = new SoapManager('SADMIN', $this->get('session'));

			$login = $this->getUser()->getLogin();
			$data = $form->getData();
			$pin = (int)$data['pin'];

			if($SADMIN->_login($login, $pin) == 1
			&& $SBUY->_login($login, $pin) == 1){

				return $this->redirect($this->generateUrl('buckutt_history'));
			}
			else{
				$this->get('session')->getFlashBag()->set('message', array(
					'type' => 'error',
					'message' => 'Code pin incorrect'
				));
			}
		}

		$name = $this->getUser()->getFullName();
		return array(
			'form' => $form->createView(),
			'name' => $name
		);

	}

	/**
	 * @Route("/buckutt/reload/{step}", name="buckutt_reload", defaults={"step" = 0})
	 * @Template()
	 */
	public function reloadAction($step)
	{
		/*
		 * Step 0 -> form where we choose how many we charge
		 * Step 1 -> check amount is ok and conform from user
		 * Step 2 -> make transaction thrue the server
		 * */
		if (! $this->getUserLayer()->isConnected()) {
			return $this->createAccessDeniedResponse();
		}

		if (!$this->get('session')->get(SoapManager::cookie_name)) {
			return $this->redirect($this->generateUrl('buckutt_connect'));
		}

		define('MAX_AMOUNT', 10000);
		$clientSBUY = new SoapManager('SBUY', $this->get('session'));
		$credit = $clientSBUY->getCredit();
		$possible_amount = MAX_AMOUNT - $credit;

		$name = $this->getUser()->getFullName();
		$form = $this->createFormBuilder()
			->add('amount', 'money', array('required' => true, 'max_length' => 5))
			->getForm();

		if($step == 1){

			if ($form->bind($this->getRequest())->isValid()) {

				$login = $this->getUser()->getLogin();
				$data = $form->getData();
				$amount = $data['amount']*100;

				$param = '';
				$param .=' normal_return_url=http://openutt.utt.fr/buckutt/reload/2';
				$param .=' cancel_return_url=http://openutt.utt.fr/buckutt/reload';
				$param .=' automatic_response_url=http://openutt.utt.fr/buckutt/sherlocks/return';
				$param .=' language=fr';
				$param .=' payment_means=CB,2,VISA,2,MASTERCARD,2';
				$param .=' header_flag=yes';
				$param .=' target=_self';
				$param .=' customer_ip_address='.$this->get('request')->getClientIp();

				$table = $clientSBUY->transactionEncode($amount, $param);

				return array(
					'name' => $name,
					'step' => $step,
					'form' => $form->createView(),
					'amount' => number_format($amount/100, 2),
					'amount_total' => number_format(($credit+$amount)/100, 2),
					'credit' => number_format($credit/100, 2),
					'htmlForm' => base64_decode($table[0][1])
				);
			}
		}
		elseif($step == 2){// step 2, step final

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'Rechargement correctement effectué'
			));

			$credit = $clientSBUY->getCredit();
			$possible_amount = MAX_AMOUNT - $credit;
		}

		return array(
			'name' => $name,
			'step' => $step,
			'form' => $form->createView(),
			'credit' => number_format($credit/100, 2),
			'possible_amount' => number_format($possible_amount/100, 2),
			'max_amount' => number_format(MAX_AMOUNT/100, 2)
		);
	}

	/**
	 * @Route("/buckutt/sherlocks/return", name="buckutt_sherlocks")
	 * @Template()
	 */
	public function sherlocksAction()
	{
		/*
		 * Cette page est appelé par le serveur de sherlocks pour confirmer une rechargement
		 * */

		$clientSBUY = new SoapManager('SBUY', $this->get('session'));

		$clientSBUY->transactionDecode(base64_encode($_POST['DATA']));

		//return sfView::NONE;
		//return array();
		// Render nothing ?
		//*
		return $this->render('EtuModuleBuckUTTBundle:Default:index.html.twig', array(
			'name' => $_POST['DATA'],
			'credit' => 0,
			'history_date' => array('start' => '', 'end' => ''),
			'history' => array()
		));//*/
	}

}
