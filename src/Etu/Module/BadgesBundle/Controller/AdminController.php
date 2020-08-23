<?php

namespace Etu\Module\BadgesBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\UserBundle\Entity\Badge;
use Etu\Core\UserBundle\Entity\UserBadge;
use Etu\Core\UserBundle\Form\UserAutocompleteType;
// Import annotations
use Etu\Core\UserBundle\Model\BadgesManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

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
     *
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
      'id' => $id,
    ];
    }

    /**
     * @Route("/{id}/edit/picture", name="badge_picture")
     *
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
     *
     * @param mixed $name
     */
    public function addAction($name, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_BADGE_ADMIN');
        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();
        $serie = '';
        if ('undefined' == $name) {
            $serie = '';
        } else {
            $serie = $name;
        }
        $form = $this->createFormBuilder()
      ->add('name', null, ['required' => true, 'label' => 'badges.admin.form.name'])
      ->add('description', null, ['required' => true, 'label' => 'badges.admin.form.description'])
      ->add('serie', null, ['required' => false, 'label' => 'badges.admin.form.serie', 'disabled' => '' != $serie, 'data' => $serie])
      ->add('submit', SubmitType::class, ['label' => 'badges.admin.form.add'])
      ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $form_data = $form->getData();
            if ('undefined' == $name) {
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
      'serie' => $serie,
    ];
    }

    /**
     * @Route("/new", name="admin_badges_new")
     *
     * @param mixed $name
     */
    public function addWithoutSerieAction()
    {
        $this->denyAccessUnlessGranted('ROLE_BADGE_ADMIN');

        return $this->redirect($this->generateUrl('admin_badges_new_in_serie', ['name' => 'undefined']));
    }

    /**
     * @Route("/{id}/delete", name="admin_badges_delete")
     *
     * @param mixed $name
     * @param mixed $id
     */
    public function deleteAction($id)
    {
        $this->denyAccessUnlessGranted('ROLE_BADGE_ADMIN');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();
        $badge = BadgesManager::findById($id);

        $em->remove($badge);
        $em->flush();

        return $this->redirect($this->generateUrl('admin_badges_index'));
    }

    /**
     * @Route("/{id}/users", name="admin_badges_users")
     * @Template()
     *
     * @param mixed $id
     */
    public function usersAction($id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_BADGE_ADMIN');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();
        $badge = BadgesManager::findById($id);

        $memberships = $em->createQueryBuilder()
      ->select('m, u')
      ->from('EtuUserBundle:UserBadge', 'm')
      ->leftJoin('m.user', 'u')
      ->where('m.badge = :badge')
      ->setParameter('badge', $id)
      ->getQuery()
      ->getResult();

        $form = $this->createFormBuilder()
      ->add('user', UserAutocompleteType::class, ['label' => 'badges.admin.users.add_member_user'])
      ->add('submit', SubmitType::class, ['label' => 'badges.admin.users.add_member_btn'])
      ->getForm();

        // User formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /** @var $user User */
            $user = $em->createQueryBuilder()
        ->select('u')
        ->from('EtuUserBundle:User', 'u')
        ->where('u.fullName = :fullName')
        ->setParameter('fullName', $data['user'])
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();

            if (!$user) {
                $this->get('session')->getFlashBag()->set('message', [
          'type' => 'error',
          'message' => 'badges.admin.users.error_user_not_found',
        ]);
            } else {
                // Keep the membership as unique
                $membership = $em->getRepository('EtuUserBundle:UserBadge')->findOneBy([
          'user' => $user,
          'badge' => $badge,
        ]);

                if (!$membership) {
                    $member = new UserBadge($badge, $user);

                    //add badge to user ?

                    $em->persist($member);
                    $em->flush();

                    $this->get('session')->getFlashBag()->set('message', [
            'type' => 'success',
            'message' => 'badges.admin.users.confirm_add',
          ]);
                } else {
                    $this->get('session')->getFlashBag()->set('message', [
            'type' => 'error',
            'message' => 'badges.admin.users.error_exists',
          ]);
                }
            }

            return $this->redirect($this->generateUrl(
        'admin_badges_users',
        ['id' => $id, 'badge' => $badge]
      ));
        }

        return [
      'memberships' => $memberships,
      'form' => $form->createView(),
      'id' => $id,
      'badge' => $badge,
    ];
    }

    /**
     * @Route("/{id}/users/{userId}/delete", name="admin_badges_user_delete")
     *
     * @param mixed $userId
     * @param mixed $id
     */
    public function deleteUserAction($id, $userId)
    {
        $this->denyAccessUnlessGranted('ROLE_BADGE_ADMIN');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();
        $badge = BadgesManager::findById($id);

        $user = $em->createQueryBuilder()
      ->select('u')
      ->from('EtuUserBundle:User', 'u')
      ->where('u.id = :id')
      ->setParameter('id', $userId)
      ->setMaxResults(1)
      ->getQuery()
      ->getOneOrNullResult();
        if (!$user) {
            $this->get('session')->getFlashBag()->set('message', [
        'type' => 'error',
        'message' => 'badges.admin.users.error_user_not_found',
      ]);
            $this->redirect($this->generateUrl('admin_badges_users', ['id' => $id]));
        }
        if (!$badge) {
            $this->get('session')->getFlashBag()->set('message', [
        'type' => 'error',
        'message' => 'badges.admin.users.error_badge_no_exists',
      ]);
            $this->redirect($this->generateUrl('admin_badges_users', ['id' => $id]));
        }

        $membership = $em->createQueryBuilder()
      ->select('m')
      ->from('EtuUserBundle:UserBadge', 'm')
      ->where('m.badge = :badge')
      ->andWhere('m.user = :user')
      ->setParameter('badge', $id)
      ->setParameter('user', $userId)
      ->getQuery()
      ->getOneOrNullResult();

        if (!$membership) {
            $this->get('session')->getFlashBag()->set('message', [
        'type' => 'error',
        'message' => 'badges.admin.users.error_membership_no_exists',
      ]);
            $this->redirect($this->generateUrl('admin_badges_users', ['id' => $id]));
        }

        $em->remove($membership);
        $em->flush();

        return $this->redirect($this->generateUrl('admin_badges_users', ['id' => $id]));
    }
}
