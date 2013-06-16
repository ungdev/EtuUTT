<?php

namespace Etu\Core\UserBundle\Controller;

use Doctrine\ORM\EntityManager;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Framework\Definition\OrgaPermission;
use Etu\Core\UserBundle\Entity\Member;
use Etu\Core\UserBundle\Entity\User;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MembershipsController extends Controller
{
	/**
	 * @Route("/user/memberships", name="memberships_index")
	 * @Template()
	 */
	public function indexAction()
	{
		if (! $this->getUserLayer()->isUser()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

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

		return array(
			'memberships' => $memberships
		);
	}

	/**
	 * @Route("/user/membership/{login}", name="memberships_orga")
	 * @Template()
	 */
	public function orgaAction($login)
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

		/** @var $availablePermissions OrgaPermission[] */
		$availablePermissions = $this->getKernel()->getAvailableOrganizationsPermissions()->toArray();
		$membershipPermissions = array();

		foreach ($availablePermissions as $availablePermission) {
			if (in_array($availablePermission->getName(), $membership->getPermissions())) {
				$membershipPermissions[] = $availablePermission;
			}
		}

		return array(
			'memberships' => $memberships,
			'membership' => $membership,
			'permissions' => $membershipPermissions,
		);
	}

	/**
	 * @Route("/user/membership/{login}/description", name="memberships_orga_desc")
	 * @Template()
	 */
	public function descAction($login)
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

		if (! $membership->hasPermission('edit_desc')) {
			return $this->createAccessDeniedResponse();
		}

		$orga = $membership->getOrganization();

		// Classic form
		$form = $this->createFormBuilder($orga)
			->add('contactMail', 'email')
			->add('contactPhone', null, array('required' => false))
			->add('description', 'redactor')
			->add('descriptionShort', 'textarea')
			->add('website', null, array('required' => false))
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
			$em->persist($orga);
			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'user.memberships.desc.confirm'
			));

			return $this->redirect($this->generateUrl('memberships_orga_desc', array('login' => $login)));
		}

		return array(
			'memberships' => $memberships,
			'membership' => $membership,
			'form' => $form->createView(),
			'orga' => $orga,
		);
	}

	/**
	 * @Route("/user/membership/{login}/permissions/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="memberships_orga_permissions")
	 * @Template()
	 */
	public function permissionsAction($login, $page = 1)
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

		if (! $membership->hasPermission('deleguate')) {
			return $this->createAccessDeniedResponse();
		}

		$orga = $membership->getOrganization();

		$members = $em->createQueryBuilder()
			->select('m, u')
			->from('EtuUserBundle:Member', 'm')
			->leftJoin('m.user', 'u')
			->where('m.organization = :orga')
			->setParameter('orga', $this->getUser()->getId())
			->orderBy('u.lastName')
			->getQuery();

		$members = $this->get('knp_paginator')->paginate($members, $page, 20);

		return array(
			'memberships' => $memberships,
			'membership' => $membership,
			'members' => $members,
			'orga' => $orga,
		);
	}

	/**
	 * @Route("/user/membership/{login}/notifications", name="memberships_orga_notifications")
	 * @Template()
	 */
	public function notificationsAction($login)
	{
		if (! $this->getUserLayer()->isUser()) {
			return $this->createAccessDeniedResponse();
		}

		return array();
	}

	/**
	 * @Route("/user/membership/{login}/events", name="memberships_orga_events")
	 * @Template()
	 */
	public function eventsAction($login)
	{
		if (! $this->getUserLayer()->isUser()) {
			return $this->createAccessDeniedResponse();
		}

		return array();
	}

	/**
	 * @Route("/user/membership/{login}/daymail", name="memberships_orga_daymail")
	 * @Template()
	 */
	public function daymailAction($login)
	{
		if (! $this->getUserLayer()->isUser()) {
			return $this->createAccessDeniedResponse();
		}

		return array();
	}
}
