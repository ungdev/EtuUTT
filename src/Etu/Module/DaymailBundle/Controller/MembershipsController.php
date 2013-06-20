<?php

namespace Etu\Module\DaymailBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Framework\Definition\Controller;

// Import annotations
use Etu\Core\UserBundle\Entity\Member;
use Etu\Module\DaymailBundle\Entity\DaymailPart;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MembershipsController extends Controller
{
	/**
	 * @Route(
	 *      "/user/membership/{login}/daymail/{day}",
	 *      defaults={"day" = "current"},
	 *      name="memberships_orga_daymail"
	 * )
	 * @Template()
	 */
	public function daymailAction($login, $day)
	{
		if (! $this->getUserLayer()->isUser()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $memberships Member[] */
		$memberships = $em->createQueryBuilder()
			->select('m, o')
			->from('EtuUserBundle:Member', 'm')
			->leftJoin('m.organization', 'o')
			->where('o.deleted = 0')
			->andWhere('m.user = :user')
			->setParameter('user', $this->getUser()->getId())
			->orderBy('m.role', 'DESC')
			->addOrderBy('o.name', 'ASC')
			->getQuery()
			->getResult();

		$membership = null;

		foreach ($memberships as $m) {
			if ($m->getOrganization()->getLogin() == $login) {
				$membership = $m;
				break;
			}
		}

		if (! $membership) {
			throw $this->createNotFoundException('Membership or organization not found for login '.$login);
		}

		if (! $membership->hasPermission('notify')) {
			return $this->createAccessDeniedResponse();
		}

		$orga = $membership->getOrganization();

		// Test day validity using DateTime
		$tomorrow = new \DateTime();
		$tomorrow->add(new \DateInterval('P1D'));

		if ($day == 'current') {
			$day = $tomorrow;
		} else {
			$day = \DateTime::createFromFormat('d-m-Y', $day);

			if (! $day) {
				$day = $tomorrow;
			}
		}

		/**
		 * @todo View old daymails using $available and a SQL query (and can not edit them of course)
		 */

		$available = DaymailPart::createFutureAvailableDays();

		if (! isset($available[$day->format('d-m-Y')])) {
			throw $this->createNotFoundException('Day not found in available list');
		}

		$daymailPart = $em->createQueryBuilder()
			->select('d')
			->from('EtuModuleDaymailBundle:DaymailPart', 'd')
			->leftJoin('d.orga', 'o')
			->where('d.day = :day')
			->setParameter('day', $day->format('d-m-Y'))
			->andWhere('o.id = :orga')
			->setParameter('orga', $orga->getId())
			->getQuery()
			->setMaxResults(1)
			->getOneOrNullResult();

		if (! $daymailPart) {
			$daymailPart = new DaymailPart($orga, $day);
		}

		$form = $this->createFormBuilder($daymailPart)
			->add('title', 'text', array('required' => true, 'max_length' => 100))
			->add('body', 'redactor_limited', array('required' => true))
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
			$em->persist($daymailPart);
			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'daymail.memberships.daymail.confirm'
			));

			return $this->redirect($this->generateUrl('memberships_orga_daymail', array(
				'login' => $login,
				'day' => $day->format('d-m-Y')
			)));
		}

		return array(
			'memberships' => $memberships,
			'membership' => $membership,
			'orga' => $orga,
			'form' => $form->createView(),
			'available' => $available,
			'currentDay' => $day
		);
	}
}
