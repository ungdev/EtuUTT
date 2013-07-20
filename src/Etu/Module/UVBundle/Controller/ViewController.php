<?php

namespace Etu\Module\UVBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\UserBundle\Entity\User;
use Etu\Core\UserBundle\Model\Badge;
use Etu\Module\UVBundle\Entity\Review;
use MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__\stdClass;
use Symfony\Component\HttpFoundation\Request;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Etu\Module\UVBundle\Entity\UV;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/uvs")
 */
class ViewController extends Controller
{
	/**
	 * @Route("/{slug}-{name}", name="uvs_view")
	 * @Template()
	 */
	public function viewAction($slug, $name)
	{
		if (! $this->getUserLayer()->isUser()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var EntityManager $em */
		$em = $this->getDoctrine()->getManager();

		/** @var UV $uv */
		$uv = $em->getRepository('EtuModuleUVBundle:UV')
			->findOneBy(array('slug' => $slug));

		if (! $uv) {
			throw $this->createNotFoundException(sprintf('UV for slug %s not found', $slug));
		}

		if (StringManipulationExtension::slugify($uv->getName()) != $name) {
			return $this->redirect($this->generateUrl('uvs_view', array(
				'slug' => $uv->getSlug(), 'name' => StringManipulationExtension::slugify($uv->getName())
			)), 301);
		}

		/** @var Review[] $results */
		$results = $em->createQueryBuilder()
			->select('r, s')
			->from('EtuModuleUVBundle:Review', 'r')
			->leftJoin('r.sender', 's')
			->where('r.uv = :uv')
			->setParameter('uv', $uv->getId())
			->orderBy('r.semester', 'DESC')
			->addOrderBy('r.validated', 'DESC')
			->getQuery()
			->getResult();

		$reviews = array();

		foreach ($results as $result) {
			if (! isset($reviews[$result->getSemester()]['count'])) {
				$reviews[$result->getSemester()]['count'] = 0;
			}

			if (! isset($reviews[$result->getSemester()]['validated'])) {
				$reviews[$result->getSemester()]['validated'] = array();
			}

			if (! isset($reviews[$result->getSemester()]['pending'])) {
				$reviews[$result->getSemester()]['pending'] = array();
			}

			$key = ($result->getValidated()) ? 'validated' : 'pending';
			$reviews[$result->getSemester()][$key][] = $result;
			$reviews[$result->getSemester()]['count']++;
		}

		return array(
			'uv' => $uv,
			'semesters' => $reviews
		);
	}

	/**
	 * @Route("/{slug}-{name}/send-review", name="uvs_view_send_review")
	 * @Template()
	 */
	public function sendReviewAction(Request $request, $slug, $name)
	{
		if (! $this->getUserLayer()->isUser()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var EntityManager $em */
		$em = $this->getDoctrine()->getManager();

		/** @var UV $uv */
		$uv = $em->getRepository('EtuModuleUVBundle:UV')
			->findOneBy(array('slug' => $slug));

		if (! $uv) {
			throw $this->createNotFoundException(sprintf('UV for slug %s not found', $slug));
		}

		if (StringManipulationExtension::slugify($uv->getName()) != $name) {
			return $this->redirect($this->generateUrl('uvs_view_send_review', array(
				'slug' => $uv->getSlug(), 'name' => StringManipulationExtension::slugify($uv->getName())
			)), 301);
		}

		$review = new Review();
		$review->setUv($uv)
			->setSender($this->getUser())
			->setSemester(User::currentSemester());

		$form = $this->createFormBuilder($review)
			->add('type', 'choice', array('choices' => Review::$types))
			->add('semester', 'choice', array('choices' => Review::availableSemesters()))
			->add('file')
			->getForm();

		if ($request->getMethod() == 'POST' && $form->submit($request)->isValid()) {
			$review->upload();

			$em->persist($review);
			$em->flush();

			// Notify subscribers
			$notif = new Notification();

			$review->file = null;

			$entity = new \stdClass();
			$entity->uv = $uv;
			$entity->review = $review;

			$notif
				->setModule($this->getCurrentBundle()->getIdentifier())
				->setHelper('uv_new_review')
				->setAuthorId($this->getUser()->getId())
				->setEntityType('uv')
				->setEntityId($uv->getId())
				->addEntity($entity);

			$this->getNotificationsSender()->send($notif);

			// Add badges
			$count = $em->createQueryBuilder()
				->select('COUNT(r) as nb')
				->from('EtuModuleUVBundle:Review', 'r')
				->where('r.sender = :user')
				->setParameter('user', $this->getUser()->getId())
				->getQuery()
				->getSingleScalarResult();

			$this->getUser()->removeBadge('uvs_reviews');

			if ($count >= 1) {
				$this->getUser()->addBadge(new Badge('uvs_reviews', 1));
			}
			if ($count >= 2) {
				$this->getUser()->getBadge('uvs_reviews')->setLevel(2);
			}
			if ($count >= 4) {
				$this->getUser()->getBadge('uvs_reviews')->setLevel(3);
			}
			if ($count >= 10) {
				$this->getUser()->getBadge('uvs_reviews')->setLevel(4);
			}

			$em->persist($this->getUser());
			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'uvs.main.sendReview.confirm'
			));

			return $this->redirect($this->generateUrl('uvs_view', array(
				'slug' => $slug,
				'name' => $name
			)));
		}

		return array(
			'uv' => $uv,
			'form' => $form->createView()
		);
	}
}

