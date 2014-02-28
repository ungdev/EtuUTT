<?php

namespace Etu\Module\TrombiBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\UserBundle\Entity\User;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MainController extends Controller
{
	/**
	 * @Route("/trombi/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="trombi_index")
	 * @Template()
	 */
	public function indexAction($page)
	{
		if (! $this->getUserLayer()->isStudent() && ! $this->getUserLayer()->isOrga()) {
			return $this->createAccessDeniedResponse();
		}

		$user = new User();
		$search = false;
		$users = array();

		$form = $this->createFormBuilder($user)
			->add('fullName', null, array('required' => false))
			->add('studentId', null, array('required' => false))
			->add('phoneNumber', null, array('required' => false))
			->add('uvs', null, array('required' => false))
			->add('branch', 'choice', array('choices' => User::$branches, 'required' => false))
			->add('niveau', 'choice', array('choices' => User::$levels, 'required' => false))
			->add('personnalMail', null, array('required' => false))
			->getForm();

		if ($this->getRequest()->query->has('form') && $form->submit($this->getRequest())->isValid()) {
			$search = true;

			/** @var $em EntityManager */
			$em = $this->getDoctrine()->getManager();

			/** @var $users QueryBuilder */
			$users = $em->createQueryBuilder()
				->select('u')
				->from('EtuUserBundle:User', 'u')
				->where('u.isStudent = 1')
				->orderBy('u.lastName');

			if (! $user->getFullName() && ! $user->getStudentId() && ! $user->getPhoneNumber() && ! $user->getUvs() &&
				! $user->getBranch() && ! $user->getNiveau() && ! $user->getPersonnalMail())
			{
				return $this->redirect($this->generateUrl('trombi_index'));
			}

			if ($user->getFullName()) {
				$where = 'u.login = :login ';
				$users->setParameter('login', $user->getFullName());

				$where .= 'OR u.surnom = :surnom OR (';
				$users->setParameter('surnom', '%'.$user->getFullName().'%');

				$terms = explode(' ', $user->getFullName());

				foreach ($terms as $key => $term) {
					$where .= 'u.fullName LIKE :name_'.$key.' AND ';
					$users->setParameter('name_'.$key, '%'.$term.'%');
				}

				$where = substr($where, 0, -5).')';

				$users->andWhere($where);
			}

			if ($user->getStudentId()) {
				$users->andWhere('u.studentId = :id')
					->setParameter('id', $user->getStudentId());
			}

			if ($user->getPhoneNumber()) {
				$phone = $user->getPhoneNumber();
				$parts = array();

				if (strpos($phone, '.') !== false) {
					$parts = explode('.', $phone);
				} elseif (strpos($phone, '-') !== false) {
					$parts = explode('-', $phone);
				} elseif (strpos($phone, ' ') !== false) {
					$parts = explode(' ', $phone);
				} else {
					$parts = str_split($phone, 2);
				}

				$users->andWhere('u.phoneNumber LIKE :phone')
					->setParameter('phone', implode('%', $parts));
			}

			if ($user->getUvs()) {
				$uvs = array_map('trim', explode(',', $user->getUvs()));

				foreach ($uvs as $key => $uv) {
					$users->andWhere('u.uvs LIKE :uv'.$key)
						->setParameter('uv'.$key, '%'.$uv.'%');
				}
			}

			if ($user->getBranch()) {
				$users->andWhere('u.branch = :branch')
					->setParameter('branch', $user->getBranch());
			}

			if ($user->getNiveau()) {
				$users->andWhere('u.niveau = :niveau')
					->setParameter('niveau', $user->getNiveau());
			}

			if ($user->getPersonnalMail()) {
				$users->andWhere('u.personnalMail = :personnalMail')
					->setParameter('personnalMail', $user->getPersonnalMail());
			}

			$query = $users->getQuery();
			$query->useResultCache(true, 3600*24);

            $users = $this->get('knp_paginator')->paginate($query, $page, 10);
		}

		return array(
			'form' => $form->createView(),
			'search' => $search,
			'pagination' => $users
		);
	}
}
