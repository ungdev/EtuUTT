<?php

namespace Etu\Core\UserBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Framework\Definition\Controller;

use Etu\Core\UserBundle\Entity\User;
use Etu\Core\UserBundle\Form\UserAutocompleteType;
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

		if ($orga->getPresident()) {
			$orga->setPresident($orga->getPresident()->getFullName());
		}

		// Classic form
		$form = $this->createFormBuilder($orga)
			->add('name')
			->add('president', 'user')
			->add('contactMail', 'email')
			->add('contactPhone', null, array('required' => false))
			->add('description', 'redactor')
			->add('descriptionShort', 'textarea')
			->add('website', null, array('required' => false))
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
			/** @var $em EntityManager */
			$em = $this->getDoctrine()->getManager();

			// Search for the president
			/** @var $assignee User */
			$president = $em->createQueryBuilder()
				->select('u')
				->from('EtuUserBundle:User', 'u')
				->where('u.fullName = :fullName')
				->setParameter('fullName', $orga->getPresident())
				->setMaxResults(1)
				->getQuery()
				->getOneOrNullResult();

			if (! $president) {
				$this->get('session')->getFlashBag()->set('message', array(
					'type' => 'error',
					'message' => 'user.orga.index.president_not_found'
				));
			} else {
				$orga->setPresident($president);

				$em->persist($orga);
				$em->flush();

				$this->get('session')->getFlashBag()->set('message', array(
					'type' => 'success',
					'message' => 'user.orga.index.confirm'
				));
			}

			return $this->redirect($this->generateUrl('orga_admin'));
		}

		// Avatar lightbox
		$avatarForm = $this->createFormBuilder($orga)
			->add('file', 'file')
			->getForm();

		return array(
			'form' => $form->createView(),
			'avatarForm' => $avatarForm->createView(),
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
	 * @Route("/orga/members", name="orga_admin_members")
	 * @Template()
	 */
	public function membersAction()
	{
		if (! $this->getUserLayer()->isOrga()) {
			return $this->createAccessDeniedResponse();
		}

		return array();
	}
}
