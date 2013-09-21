<?php

namespace Etu\Core\UserBundle\Controller;

use Doctrine\ORM\EntityManager;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\UserBundle\Entity\Member;
use Etu\Core\UserBundle\Entity\User;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class OrgaController extends Controller
{
	/**
	 * @Route("/orga", name="orga_admin")
	 * @Template()
	 */
	public function indexAction()
	{
		if (! $this->getUserLayer()->isOrga()) {
			return $this->createAccessDeniedResponse();
		}

		$orga = $this->getUser();

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		// Try to find a president
		if (! $orga->getPresident()) {
			/** @var $members Member[] */
			$members = $em->createQueryBuilder()
				->select('m, u')
				->from('EtuUserBundle:Member', 'm')
				->leftJoin('m.user', 'u')
				->where('m.organization = :orga')
				->setParameter('orga', $this->getUser()->getId())
				->getQuery();

			foreach ($members as $member) {
				if ($member->getRole() == Member::ROLE_PRESIDENT) {
					$orga->setPresident($member->getUser());
					$em->persist($orga);
					$em->flush();
					break;
				}
			}
		}

		// Classic form
		$form = $this->createFormBuilder($orga)
			->add('name')
			->add('contactMail', 'email')
			->add('contactPhone', null, array('required' => false))
			->add('description', 'redactor', array('required' => false))
			->add('descriptionShort', 'textarea')
			->add('website', null, array('required' => false))
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
			$em->persist($orga);
			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'user.orga.index.confirm'
			));

			return $this->redirect($this->generateUrl('orga_admin'));
		}

		// Avatar lightbox
		$avatarForm = $this->createFormBuilder($orga)
			->add('file', 'file')
			->getForm();

		return array(
			'form' => $form->createView(),
			'avatarForm' => $avatarForm->createView(),
			'rand' => substr(md5(uniqid(true)), 0, 5),
		);
	}

	/**
	 * @Route("/orga/avatar", name="orga_admin_avatar")
	 * @Template()
	 */
	public function avatarAction()
	{
		if (! $this->getUserLayer()->isOrga()) {
			return $this->createAccessDeniedResponse();
		}

		$orga = $this->getUser();

		// Avatar lightbox
		$form = $this->createFormBuilder($orga)
			->add('file', 'file')
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
			/** @var $em EntityManager */
			$em = $this->getDoctrine()->getManager();

			$orga->upload();

			$em->persist($orga);
			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'user.orga.avatar.confirm'
			));

			return $this->redirect($this->generateUrl('orga_admin'));
		}

		return array(
			'form' => $form->createView()
		);
	}

	/**
	 * @Route("/orga/members/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="orga_admin_members")
	 * @Template()
	 */
	public function membersAction($page)
	{
		if (! $this->getUserLayer()->isOrga()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		$members = $em->createQueryBuilder()
			->select('m, u')
			->from('EtuUserBundle:Member', 'm')
			->leftJoin('m.user', 'u')
			->where('m.organization = :orga')
			->setParameter('orga', $this->getUser()->getId())
			->orderBy('u.lastName')
			->getQuery();

		$members = $this->get('knp_paginator')->paginate($members, $page, 20);

		$member = new Member();
		$member->setOrganization($this->getUser());

		$roles = Member::getAvailableRoles();

		foreach ($roles as $key => $role) {
			$roles[$key] = 'user.orga.role.'.$role;
		}

		$form = $this->createFormBuilder($member)
			->add('user', 'user')
			->add('role', 'choice', array('choices' => $roles))
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
			/** @var $user User */
			$user = $em->createQueryBuilder()
				->select('u')
				->from('EtuUserBundle:User', 'u')
				->where('u.login = :login')
				->orWhere('u.fullName = :fullName')
				->setParameter('login', $member->getUser())
				->setParameter('fullName', $member->getUser())
				->setMaxResults(1)
				->getQuery()
				->getOneOrNullResult();

			if (! $user) {
				$this->get('session')->getFlashBag()->set('message', array(
					'type' => 'error',
					'message' => 'user.orga.members.error_user_not_fount'
				));
			} else {
				$member->setUser($user);

				// Keep the membership as unique
				$membership = $em->getRepository('EtuUserBundle:Member')->findOneBy(array(
					'user' => $member->getUser(),
					'organization' => $member->getOrganization()
				));

				if (! $membership) {
					if ($member->getRole() == Member::ROLE_PRESIDENT) {
						$this->getUser()->setPresident($member->getUser());
					}

					$this->getUser()->addCountMembers();
					$em->persist($this->getUser());

					$this->getSubscriptionsManager()->subscribe($member->getUser(), 'orga', $this->getUser()->getId());

					$em->persist($member);
					$em->flush();

					$this->get('session')->getFlashBag()->set('message', array(
						'type' => 'success',
						'message' => 'user.orga.members.confirm_add'
					));
				} else {
					$this->get('session')->getFlashBag()->set('message', array(
						'type' => 'error',
						'message' => 'user.orga.members.error_exists'
					));
				}
			}

			return $this->redirect($this->generateUrl(
				'orga_admin_members', array('page' => $page)
			));
		}

		return array(
			'pagination' => $members,
			'form' => $form->createView()
		);
	}

	/**
	 * @Route("/orga/members/{login}", name="orga_admin_members_edit")
	 * @Template()
	 */
	public function memberEditAction($login)
	{
		if (! $this->getUserLayer()->isOrga()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $member Member */
		$member = $em->createQueryBuilder()
			->select('m, u')
			->from('EtuUserBundle:Member', 'm')
			->leftJoin('m.user', 'u')
			->where('m.organization = :orga')
			->andWhere('u.login = :login')
			->setParameter('orga', $this->getUser()->getId())
			->setParameter('login', $login)
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();

		if (! $member) {
			throw $this->createNotFoundException(sprintf('Login %s or membership not found', $login));
		}

		$availableRoles = Member::getAvailableRoles();

		foreach ($availableRoles as $key => $role) {
			$availableRoles[$key] = array(
				'identifier' => $role,
				'name' => 'user.orga.role.'.$role,
				'selected' => $role == $member->getRole()
			);
		}

		$availablePermissions = $this->getKernel()->getAvailableOrganizationsPermissions()->toArray();

		$permissions1 = array();
		$permissions2 = array();

		$i = floor(count($availablePermissions) / 2);

		foreach ($availablePermissions as $permission) {
			if ($member->hasPermission($permission->getName())) {
				$permission = array('definition' => $permission, 'checked' => true);
			} else {
				$permission = array('definition' => $permission, 'checked' => false);
			}

			if ($i == 0) {
				$permissions1[] = $permission;
			} else {
				$permissions2[] = $permission;
				$i--;
			}
		}

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST') {
			if ($request->get('role') != null && in_array(intval($request->get('role')), Member::getAvailableRoles())) {
				$member->setRole(intval($request->get('role')));
			}

			if ($member->getRole() == Member::ROLE_PRESIDENT) {
				$this->getUser()->setPresident($member->getUser());
				$em->persist($this->getUser());
				$em->flush();
			}

			if (is_array($request->get('permissions'))) {
				$userPermissions = array();

				foreach ($request->get('permissions') as $permission => $value) {
					$userPermissions[] = $permission;
				}

				$member->setPermissions($userPermissions);
			} else {
				$member->setPermissions(array());
			}

			$em->persist($member);
			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'user.orga.memberEdit.confirm'
			));

			return $this->redirect($this->generateUrl(
				'orga_admin_members_edit', array('login' => $member->getUser()->getLogin())
			));
		}

		return array(
			'member' => $member,
			'user' => $member->getUser(),
			'roles' => $availableRoles,
			'permissions1' => $permissions1,
			'permissions2' => $permissions2
		);
	}

	/**
	 * @Route("/orga/members/{login}/delete/{confirm}", defaults={"confirm" = ""}, name="orga_admin_members_delete")
	 * @Template()
	 */
	public function memberDeleteAction($login, $confirm = '')
	{
		if (! $this->getUserLayer()->isOrga()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $member Member */
		$member = $em->createQueryBuilder()
			->select('m, u')
			->from('EtuUserBundle:Member', 'm')
			->leftJoin('m.user', 'u')
			->where('m.organization = :orga')
			->andWhere('u.login = :login')
			->setParameter('orga', $this->getUser()->getId())
			->setParameter('login', $login)
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();

		if (! $member) {
			throw $this->createNotFoundException(sprintf('Login %s or membership not found', $login));
		}

		if ($confirm == 'confirm') {
			$user = $member->getUser();

			$em->persist($user);
			$em->remove($member);
			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'user.orga.memberDelete.confirm'
			));

			return $this->redirect($this->generateUrl('orga_admin_members'));
		}

		return array(
			'member' => $member,
			'user' => $member->getUser()
		);
	}
}
