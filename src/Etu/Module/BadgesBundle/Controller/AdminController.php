<?php

namespace Etu\Module\BadgesBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\UserBundle\Model\BadgesManager;
use Etu\Core\UserBundle\Entity\Badge;
// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

/**
 * @Route("/admin/badges")
 */
class AdminController extends Controller
{
  /**
   * @Route("", name="admin_badges_index")
   * @Template()
   */
  public function indexAction()
  {
    $this->denyAccessUnlessGranted('ROLE_BADGE_ADMIN');
    $series = BadgesManager::findBadgesList();
    return [
      'series' => $series,
    ];
  }

  /**
   * @Route("/{id}/edit", name="admin_badges_edit")
   * @Template()
   * @param mixed $id
   */
  public function editAction($id, Request $request)
  {
    $this->denyAccessUnlessGranted('ROLE_BADGE_ADMIN');
    $badge = BadgesManager::findById($id);
    $form = $this->createFormBuilder($badge)
      ->add('name', null, ['required' => true, 'label' => 'badges.admin.form.name'])
      ->add('description', null, ['required' => true, 'label' => 'badges.admin.form.description'])
      ->add('serie', null, ['required' => false, 'label' => 'badges.admin.form.serie', 'disabled' => true])
      ->add('level', null, ['required' => false, 'label' => 'badges.admin.form.level', 'disabled' => true])
      ->add('submit', SubmitType::class, ['label' => 'badges.admin.form.edit'])
      ->getForm();

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      $em = $this->getDoctrine()->getManager();

      $em->persist($badge);
      $em->flush();

      $this->get('session')->getFlashBag()->set('message', [
        'type' => 'success',
        'message' => 'badges.admin.edit.confirm',
      ]);

      return $this->redirect($this->generateUrl('admin_badges_index'));
    }

    // Picture lightbox
    $pictureForm = $this->createFormBuilder($badge, ['attr' => ['id' => 'picture-upload-form']])
      ->setAction($this->generateUrl('badge_picture', ['id' => $id]))
      ->add('file', FileType::class)
      ->getForm();

    return [
      'badge' => $badge,
      'form' => $form->createView(),
      'pictureForm' => $pictureForm->createView(),
      'id' => $id
    ];
  }


  /**
   * @Route("/{id}/edit/picture", name="badge_picture")
   * @param mixed $id
   */
  public function pictureAction($id, Request $request)
  {
    $this->denyAccessUnlessGranted('ROLE_BADGE_ADMIN');
    $badge = BadgesManager::findById($id);
    $user = $this->getUser();


    $form = $this->createFormBuilder($badge)
      ->add('file', FileType::class, ['label' => 'badges.admin.form.file'])
      ->add('submit', SubmitType::class, ['label' => 'badges.admin.form.edit'])
      ->getForm();

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      $em = $this->getDoctrine()->getManager();

      $badge->upload();
      $em->persist($user);
      $em->flush();

      $this->get('session')->getFlashBag()->set('message', [
        'type' => 'success',
        'message' => 'user.profile.profileAvatar.confirm',
      ]);

      return $this->redirect($this->generateUrl('admin_badges_edit', ['id' => $id]));
    }

    return [
      'form' => $form->createView(),
    ];
  }


  /**
   * @Route("/new/{name}", name="admin_badges_new_in_serie")
   * @Template()
   * @param mixed $name
   */
  public function addAction($name, Request $request)
  {
    $this->denyAccessUnlessGranted('ROLE_BADGE_ADMIN');
    /** @var $em EntityManager */
    $em = $this->getDoctrine()->getManager();
    $serie = '';
    if ($name == 'undefined') {
      $serie = '';
    } else {
      $serie = $name;
    }
    $form = $this->createFormBuilder()
      ->add('name', null, ['required' => true, 'label' => 'badges.admin.form.name'])
      ->add('description', null, ['required' => true, 'label' => 'badges.admin.form.description'])
      ->add('serie', null, ['required' => false, 'label' => 'badges.admin.form.serie', 'disabled' => $serie != '', 'data' => $serie])
      ->add('submit', SubmitType::class, ['label' => 'badges.admin.form.add'])
      ->getForm();

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      $form_data = $form->getData();
      if ($name == 'undefined') {
        $serie = $form_data['serie'];
      } else {
        $serie = $name;
      }

      /** @var $query QueryBuilder */
      $query = $em->createQueryBuilder()
        ->select('b')
        ->from('EtuUserBundle:Badge', 'b')
        ->where('b.serie = :serie')
        ->setParameter('serie', $serie)
        ->orderBy('b.level');
      $sameseriebadge = $query->getQuery()->getResult();



      $badge = new Badge($serie, $form_data['name'], $form_data['description'], 'default-badge', count($sameseriebadge) + 1);

      $em->persist($badge);
      $em->flush();
      $this->get('session')->getFlashBag()->set('message', [
        'type' => 'success',
        'message' => 'badges.admin.add.confirm',
      ]);

      return $this->redirect($this->generateUrl('admin_badges_index'));
    }


    return [
      'form' => $form->createView(),
      'serie' => $serie
    ];
  }
  /**
   * @Route("/new", name="admin_badges_new")
   * @param mixed $name
   */
  public function addWithoutSerieAction()
  {
    $this->denyAccessUnlessGranted('ROLE_BADGE_ADMIN');
    return $this->redirect($this->generateUrl('admin_badges_new_in_serie', ['name' => 'undefined']));
  }


  /**
   * @Route("/{id}/delete", name="admin_badges_delete")
   * @param mixed $name
   */
  public function deleteAction($id)
  {
    $this->denyAccessUnlessGranted('ROLE_BADGE_ADMIN');

    /** @var $em EntityManager */
    $em = $this->getDoctrine()->getManager();
    $badge = BadgesManager::findById($id);

    $badge->setDeletedAt(new \DateTime());

    $em->persist($badge);
    $em->flush();
    return $this->redirect($this->generateUrl('admin_badges_index'));
  }
}
