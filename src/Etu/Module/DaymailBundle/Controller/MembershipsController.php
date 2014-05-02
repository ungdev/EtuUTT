<?php

namespace Etu\Module\DaymailBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\UserBundle\Entity\Member;
use Etu\Module\DaymailBundle\Entity\DaymailPart;

// Import annotations
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

		if (! $membership->hasPermission('daymail')) {
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

		// Select old daymails
		/** @var $daymailsParts DaymailPart[] */
		$daymailsParts = $em->createQueryBuilder()
			->select('d')
			->from('EtuModuleDaymailBundle:DaymailPart', 'd')
			->leftJoin('d.orga', 'o')
			->where('o.id = :orga')
			->setParameter('orga', $orga->getId())
			->orderBy('d.date', 'DESC')
			->getQuery()
			->setMaxResults(10)
			->getResult();

		$available = array();
		$available['divider'] = 'divider';
		$future = DaymailPart::createFutureAvailableDays();

		$daymailPart = false;
		$canEdit = isset($future[$day->format('d-m-Y')]);

		foreach ($daymailsParts as $part) {
			if ($part->getDay() == $day->format('d-m-Y')) {
				$daymailPart = $part;
			}

			if (isset($future[$part->getDate()->format('d-m-Y')])) {
				$future[$part->getDate()->format('d-m-Y')]->name = $part->getTitle();
				continue;
			}

			$available[$part->getDate()->format('d-m-Y')] = $part->getDate();
			$available[$part->getDate()->format('d-m-Y')]->old = true;
			$available[$part->getDate()->format('d-m-Y')]->name = $part->getTitle();
		}

		if (count($available) == 1) {
			$available = array();
		}

		$available = array_merge(array_reverse($available), $future);

		if (! isset($available[$day->format('d-m-Y')])) {
			throw $this->createNotFoundException('Day not found in available list');
		}

		if (! $daymailPart) {
			$daymailPart = new DaymailPart($orga, $day);
		}

		$form = $this->createFormBuilder($daymailPart)
			->add('title', 'text', array('required' => true, 'max_length' => 100))
			->add('body', 'redactor_html', array('required' => true))
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->submit($request)->isValid() && $canEdit) {
            $daymailPart->setBody($this->get('etu_daymail.body_parser')->parse($daymailPart->getBody()));

			$em->persist($daymailPart);
			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'daymail.memberships.daymail.confirm'
			));

			if ($request->request->has('_preview')) {
				return $this->redirect($this->generateUrl('memberships_orga_daymail', array(
					'login' => $login,
					'day' => $day->format('d-m-Y')
				)).'?preview');
			} else {
				return $this->redirect($this->generateUrl('memberships_orga_daymail', array(
					'login' => $login,
					'day' => $day->format('d-m-Y')
				)));
			}
		}

		return array(
			'memberships' => $memberships,
			'membership' => $membership,
			'orga' => $orga,
			'form' => $form->createView(),
			'daymail' => $daymailPart,
			'available' => $available,
			'currentDay' => $day,
			'canEdit' => $canEdit,
			'wantPreview' => $request->query->has('preview'),
			'login' => $login,
			'day' => $day->format('d-m-Y')
		);
	}

	/**
	 * @Route(
	 *      "/user/membership/{login}/daymail/{day}/preview",
	 *      defaults={"day" = "current"},
	 *      name="memberships_orga_daymail_preview"
	 * )
	 * @Template()
	 */
	public function previewAction($login, $day)
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

		if (! $membership->hasPermission('daymail')) {
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

		/** @var $daymailPart DaymailPart */
		$daymailPart = $em->createQueryBuilder()
			->select('d, o')
			->from('EtuModuleDaymailBundle:DaymailPart', 'd')
			->leftJoin('d.orga', 'o')
			->where('o.id = :orga')
			->setParameter('orga', $orga->getId())
			->andWhere('d.day = :day')
			->setParameter('day', $day->format('d-m-Y'))
			->getQuery()
			->setMaxResults(1)
			->getOneOrNullResult();

		if (! $daymailPart) {
			throw $this->createNotFoundException('Daymail not found for this day');
		}

		return array(
			'daymail' => $daymailPart
		);
	}

	/**
	 * @Route(
	 *      "/user/membership/{login}/daymail/{day}/remove",
	 *      defaults={"day" = "current"},
	 *      name="memberships_orga_daymail_remove"
	 * )
	 * @Template()
	 */
	public function removeAction($login, $day)
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

		if (! $membership->hasPermission('daymail')) {
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

		/** @var $daymailPart DaymailPart */
		$daymailPart = $em->createQueryBuilder()
			->select('d, o')
			->from('EtuModuleDaymailBundle:DaymailPart', 'd')
			->leftJoin('d.orga', 'o')
			->where('o.id = :orga')
			->setParameter('orga', $orga->getId())
			->andWhere('d.day = :day')
			->setParameter('day', $day->format('d-m-Y'))
			->getQuery()
			->setMaxResults(1)
			->getOneOrNullResult();

		if (! $daymailPart) {
			throw $this->createNotFoundException('Daymail not found for this day');
		}

		$em->remove($daymailPart);
		$em->flush();

		return $this->redirect($this->generateUrl('memberships_orga_daymail', array(
			'login' => $login,
			'day' => $day->format('d-m-Y')
		)));
	}
}
