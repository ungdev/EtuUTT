<?php

namespace Etu\Module\BuckUTTBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/buckutt/history/{date_start}/{date_end}", defaults={"date_start" = 0, "date_end" = 0}, name="buckutt_history")
     * @Template()
     */
    public function indexAction($date_start, $date_end)
    {
		if (! $this->getUserLayer()->isConnected()) {
			return $this->createAccessDeniedResponse();
		}

		if (!$this->get('session')->get('buckutt_soap_cookie')) {
			return $this->redirect($this->generateUrl('buckutt_connect'));
		}
		
		$history = array();
		$history_dates = array();
		/* $history -> array($type, $date, $obj_name, $poi_name, $fun_name, $price) 
		type= rec/buy */
        
		define('BUCKUTT_BUY', 0);
		define('BUCKUTT_REC', 1);
		define('DATE_FORMAT', 'Y-m-d H:i');
        $date_start_int = 0;// format timestamp
        $date_end_int = 0;// format timestamp
		
		// si on spécifie pas la date ou qu'elle n'a pas le bon format
		if($date_start == 0 || !preg_match('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/',$date_start)){
			$date_start_int = time() - (100*24*3600);// j-10
            $date_start = date(DATE_FORMAT,$date_start_int);
        }
		else{
            $date_start_int = strtotime($date_start);
        }

		if($date_end == 0 || !preg_match('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/',$date_end)){
			$date_end_int = time();
            $date_end = date(DATE_FORMAT,$date_end_int);
        }
        else{
            $date_end_int = strtotime($date_end);
        }

        $clientSADMIN = $this->getSoapClient('SADMIN');

		$achats = $this->str_getcsv_buckutt($clientSADMIN->getHistoriqueAchats($date_start_int, $date_end_int));

        if((int)$achats == 400){
            return array(
                'name' => 'erreur'.$date_start.', '.$date_end,
                'amount' => '0',
                'history' => array()
            );
        }

        foreach($achats as $a){
			$history[] = array(
				'type' => BUCKUTT_BUY, 
				'date' => date(DATE_FORMAT, $a[0]), // pur_date
				'user' => ($a[2].' '.$a[3]), // usr_firstname  usr_lastname
				'object' => $a[1], // obj_name
				'point' => $a[4], // poi_name
				'fundation' => $a[5], // fun_name
				'amount' => substr($a[6], 0, -2).','.substr($a[6], -2) // pur_price
			);
			$history_dates[] = $a[0];
		}
		
		$recharges = $this->str_getcsv_buckutt($clientSADMIN->getHistoriqueRecharge($date_start_int, $date_end_int));
        //-> array($don['rec_date'], $don['rty_name'], $don['usr_firstname'], $don['usr_lastname'], $don['poi_name'], $don['rec_credit'])
		foreach($recharges as $r){
            $history[] = array(
                'type' => BUCKUTT_REC,
                'date' => date(DATE_FORMAT, $r[0]), // pur_date
                'user' => null,
                'object' => $r[1], // obj_name
                'point' => $r[4], // poi_name
                'fundation' => null,
                'amount' => substr($r[5], 0, -2).','.substr($r[5], -2) // pur_price
            );
			$history_dates[] = $r[0];
		}

		array_multisort($history_dates, SORT_DESC, $history);
		
		$amount = $this->getSoapClient('SBUY')->getCredit();
		
		$name = $this->getUser()->getFullName();
		$amount_str = substr($amount, 0, -2).','.substr($amount, -2);
		
        return array(
            'name' => $name,
            'amount' => $amount_str,
            'history_date' => array('start' => $date_start, 'end' => $date_end),
            'history' => $history
        );
    }

    /**
     * @Route("/buckutt/connect", name="buckutt_connect")
     * @Template()
     */
    public function connectAction()
    {
        if (! $this->getUserLayer()->isConnected()) {
            return $this->createAccessDeniedResponse();
        }

        $form = $this->createFormBuilder()
            ->add('pin', null, array('required' => true))
            ->getForm();

        if ($form->bind($this->getRequest())->isValid()) {

            $SBUY = $this->getSoapClient('SBUY');
            $SADMIN = $this->getSoapClient('SADMIN');

            $login = $this->getUser()->getLogin();
            $data = $form->getData();
            $pin = $data['pin'];

            if($SADMIN->login($login, 1, $pin, 'etu.utt.fr') == 1
            && $SBUY->login($login, 1, $pin, 'etu.utt.fr') == 1){
                    $this->get('session')->set('buckutt_soap_cookie', array(
                        'SADMIN' => $SADMIN->_cookies["PHPSESSID"][0],
                        'SBUY' => $SBUY->_cookies["PHPSESSID"][0]
                    ));

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
            'name' => $name);

    }

    /**
     * @Route("/buckutt/reload", name="buckutt_reload")
     * @Template()
     */
    public function reloadAction()
    {
        $name = "name";
        return array('name' => $name);
    }

    private function getSoapClient($wsdlName)
    {
        $wsdl = array(
            'SBUY' => 'http://10.10.10.1:8080/SBUY.class.php?wsdl',
            'SADMIN' => 'http://10.10.10.1:8080/SADMIN.class.php?wsdl'
        );

        $client = null;
        $cookie = $this->get('session')->get('buckutt_soap_cookie');
        if (!$cookie) {
            $client = new \SoapClient($wsdl[$wsdlName], array("cache_wsdl" => WSDL_CACHE_NONE));
        } else {
            $client = @new \SoapClient($wsdl[$wsdlName], array("cache_wsdl" => WSDL_CACHE_BOTH));
            $client->__setCookie("PHPSESSID", $cookie[$wsdlName]);
        }

        return $client;
    }

    private function formatData($data) {
        if (strpos($data, '","') != false) {
            $data = $this->str_getcsv_lines($data);
            if (count($data) == 1) {
                $data = array_pop($data);
            }
        }
        return $data;
    }

    private function str_getcsv_lines($string_csv) {
        $array0 = explode(";\n", $string_csv, -1);
        $array2 = array();
        foreach ($array0 as $array1) {
            $array2[] = str_getcsv($array1, ',', '"');
        }
        return $array2;
    }

    private function str_getcsv_buckutt($string_csv) {
        $pattern = ',';
        if (!empty($string_csv) && is_string($string_csv)) {
            $array0 = explode(";\n", $string_csv, -1);
            foreach ($array0 as $i=>$array1) {
                $array2 = substr($array1, 1, -1);
                $array0[$i] = explode('"'.$pattern.'"', $array2);
                foreach ($array0[$i] as $j=>$array3) {
                    $array0[$i][$j] = $array3;
                }
            }
            return $array0;
        } else { return 400; }
    }


}
