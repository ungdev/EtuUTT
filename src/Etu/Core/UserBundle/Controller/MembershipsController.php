<?php

namespace Etu\Core\UserBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Form\EditorType;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Framework\Definition\OrgaPermission;
use Etu\Core\UserBundle\Entity\Member;
use Etu\Core\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;

class MembershipsController extends Controller
{
    /**
     * @Route("/user/memberships", name="memberships_index")
     * @Template()
     */
    public function indexAction()
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_MEMBERSHIPS');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->select('m, o')
            ->from('EtuUserBundle:Member', 'm')
            ->leftJoin('m.organization', 'o')
            ->andWhere('m.user = :user')
            ->setParameter('user', $this->getUser()->getId())
            ->orderBy('m.role', 'DESC')
            ->addOrderBy('o.name', 'ASC')
            ->getQuery();

        $query->useResultCache(true, 60);

        /** @var $memberships Member[] */
        $memberships = $query->getResult();

        return [
            'memberships' => $memberships,
        ];
    }

    /**
     * @Route("/user/membership/{login}", name="memberships_orga")
     * @Template()
     *
     * @param mixed $login
     */
    public function orgaAction($login)
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_MEMBERSHIPS');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->select('m, o')
            ->from('EtuUserBundle:Member', 'm')
            ->leftJoin('m.organization', 'o')
            ->andWhere('m.user = :user')
            ->setParameter('user', $this->getUser()->getId())
            ->orderBy('m.role', 'DESC')
            ->addOrderBy('o.name', 'ASC')
            ->getQuery();

        $query->useResultCache(true, 60);

        /** @var $memberships Member[] */
        $memberships = $query->getResult();

        $membership = null;

        foreach ($memberships as $m) {
            if ($m->getOrganization()->getLogin() == $login) {
                $membership = $m;
                break;
            }
        }

        if (!$membership) {
            throw $this->createNotFoundException('Membership or organization not found for login '.$login);
        }

        /** @var $availablePermissions OrgaPermission[] */
        $availablePermissions = $this->getKernel()->getAvailableOrganizationsPermissions()->toArray();
        $membershipPermissions = [];

        foreach ($availablePermissions as $availablePermission) {
            if (in_array($availablePermission->getName(), $membership->getPermissions())) {
                $membershipPermissions[] = $availablePermission;
            }
        }

        return [
            'memberships' => $memberships,
            'membership' => $membership,
            'permissions' => $membershipPermissions,
        ];
    }

    /**
     * @Route("/user/membership/{login}/description", name="memberships_orga_desc")
     * @Template()
     *
     * @param mixed $login
     */
    public function descAction($login, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_MEMBERSHIPS');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->select('m, o')
            ->from('EtuUserBundle:Member', 'm')
            ->leftJoin('m.organization', 'o')
            ->andWhere('m.user = :user')
            ->setParameter('user', $this->getUser()->getId())
            ->orderBy('m.role', 'DESC')
            ->addOrderBy('o.name', 'ASC')
            ->getQuery();

        $query->useResultCache(true, 60);

        /** @var $memberships Member[] */
        $memberships = $query->getResult();

        $membership = null;

        foreach ($memberships as $m) {
            if ($m->getOrganization()->getLogin() == $login) {
                $membership = $m;
                break;
            }
        }

        if (!$membership) {
            throw $this->createNotFoundException('Membership or organization not found for login '.$login);
        }

        if (!$membership->hasPermission('edit_desc')) {
            return $this->createAccessDeniedResponse();
        }

        $orga = $membership->getOrganization();

        // Classic form
        $form = $this->createFormBuilder($orga)
            ->add('contactMail', EmailType::class, ['label' => 'user.orga.index.contactMail.label'])
            ->add('contactPhone', null, ['required' => false, 'label' => 'user.orga.index.contactPhone.label'])
            ->add('website', null, ['required' => false, 'label' => 'user.orga.index.website.label'])
            ->add('descriptionShort', TextareaType::class, ['label' => 'user.orga.index.descriptionShort.label'])
            ->add('description', EditorType::class, ['required' => false, 'label' => 'user.orga.index.description.label', 'organization' => $orga->getLogin()])
            ->add('submit', SubmitType::class, ['label' => 'user.orga.index.submit'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($orga);
            $em->flush();

            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'user.memberships.desc.confirm',
            ]);

            return $this->redirect($this->generateUrl('memberships_orga_desc', ['login' => $login]));
        }

        return [
            'memberships' => $memberships,
            'membership' => $membership,
            'form' => $form->createView(),
            'orga' => $orga,
        ];
    }

    /**
     * @Route("/user/membership/{login}/permissions/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="memberships_orga_permissions")
     * @Template()
     *
     * @param mixed $login
     * @param mixed $page
     */
    public function permissionsAction($login, $page = 1)
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_MEMBERSHIPS');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->select('m, o')
            ->from('EtuUserBundle:Member', 'm')
            ->leftJoin('m.organization', 'o')
            ->andWhere('m.user = :user')
            ->setParameter('user', $this->getUser()->getId())
            ->orderBy('m.role', 'DESC')
            ->addOrderBy('o.name', 'ASC')
            ->getQuery();

        $query->useResultCache(true, 60);

        /** @var $memberships Member[] */
        $memberships = $query->getResult();

        $membership = null;

        foreach ($memberships as $m) {
            if ($m->getOrganization()->getLogin() == $login) {
                $membership = $m;
                break;
            }
        }

        if (!$membership) {
            throw $this->createNotFoundException('Membership or organization not found for login '.$login);
        }

        if (!$membership->hasPermission('deleguate')) {
            return $this->createAccessDeniedResponse();
        }

        $orga = $membership->getOrganization();

        $members = $em->createQueryBuilder()
            ->select('m, u')
            ->from('EtuUserBundle:Member', 'm')
            ->leftJoin('m.user', 'u')
            ->where('m.organization = :orga')
            ->setParameter('orga', $orga->getId())
            ->andWhere('u.id != :me')
            ->setParameter('me', $this->getUser()->getId())
            ->orderBy('m.role DESC, u.lastName')
            ->getQuery();

        $members = $this->get('knp_paginator')->paginate($members, $page, 20);

        return [
            'memberships' => $memberships,
            'membership' => $membership,
            'pagination' => $members,
            'orga' => $orga,
        ];
    }

    /**
     * @Route("/user/membership/{login}/permissions/{user}/edit", name="memberships_orga_permissions_edit")
     * @Template()
     *
     * @param mixed $login
     * @param mixed $user
     */
    public function permissionsEditAction($login, $user, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_MEMBERSHIPS');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->select('m, o')
            ->from('EtuUserBundle:Member', 'm')
            ->leftJoin('m.organization', 'o')
            ->andWhere('m.user = :user')
            ->setParameter('user', $this->getUser()->getId())
            ->orderBy('m.role', 'DESC')
            ->addOrderBy('o.name', 'ASC')
            ->getQuery();

        $query->useResultCache(true, 60);

        /** @var $memberships Member[] */
        $memberships = $query->getResult();

        $membership = null;

        foreach ($memberships as $m) {
            if ($m->getOrganization()->getLogin() == $login) {
                $membership = $m;
                break;
            }
        }

        if (!$membership) {
            throw $this->createNotFoundException('Membership or organization not found for login '.$login);
        }

        if (!$membership->hasPermission('deleguate')) {
            return $this->createAccessDeniedResponse();
        }

        $orga = $membership->getOrganization();

        $member = $em->createQueryBuilder()
            ->select('m, u')
            ->from('EtuUserBundle:Member', 'm')
            ->leftJoin('m.user', 'u')
            ->where('m.organization = :orga')
            ->setParameter('orga', $orga->getId())
            ->andWhere('u.login = :login')
            ->setParameter('login', $user)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$member) {
            throw $this->createNotFoundException('Membership not found for login '.$user);
        }

        // Current user can add/remove only permissions he/she own himself/herself

        /** @var $availablePermissions OrgaPermission[] */
        $availablePermissions = $this->getKernel()->getAvailableOrganizationsPermissions()->toArray();

        foreach ($availablePermissions as $key => $permission) {
            if (!$membership->hasPermission($permission->getName())) {
                unset($availablePermissions[$key]);
            }
        }

        $permissions = [];

        foreach ($availablePermissions as $permission) {
            if ($member->hasPermission($permission->getName())) {
                $permissions[] = ['definition' => $permission, 'checked' => true];
            } else {
                $permissions[] = ['definition' => $permission, 'checked' => false];
            }
        }

        if ($request->getMethod() == 'POST') {
            if (is_array($request->get('permissions'))) {
                $userPermissions = [];

                foreach ($request->get('permissions') as $permission => $value) {
                    $userPermissions[] = $permission;
                }

                $member->setPermissions($userPermissions);
            } else {
                $member->setPermissions([]);
            }

            $em->persist($member);
            $em->flush();

            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'user.memberships.permissionsEdit.confirm',
            ]);

            return $this->redirect($this->generateUrl(
                'memberships_orga_permissions_edit', ['login' => $login, 'user' => $user]
            ));
        }

        return [
            'memberships' => $memberships,
            'membership' => $membership,
            'member' => $member,
            'permissions' => $permissions,
            'orga' => $orga,
        ];
    }

    /**
     * @Route("/user/membership/{login}/notification", name="memberships_orga_notifications")
     * @Template()
     *
     * @param mixed $login
     */
    public function notificationAction($login, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_MEMBERSHIPS');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->select('m, o')
            ->from('EtuUserBundle:Member', 'm')
            ->leftJoin('m.organization', 'o')
            ->andWhere('m.user = :user')
            ->setParameter('user', $this->getUser()->getId())
            ->orderBy('m.role', 'DESC')
            ->addOrderBy('o.name', 'ASC')
            ->getQuery();

        $query->useResultCache(true, 60);

        /** @var $memberships Member[] */
        $memberships = $query->getResult();

        $membership = null;

        foreach ($memberships as $m) {
            if ($m->getOrganization()->getLogin() == $login) {
                $membership = $m;
                break;
            }
        }

        if (!$membership) {
            throw $this->createNotFoundException('Membership or organization not found for login '.$login);
        }

        if (!$membership->hasPermission('notify')) {
            return $this->createAccessDeniedResponse();
        }

        $orga = $membership->getOrganization();

        $notif = new \stdClass();
        $notif->link = null;
        $notif->content = '';
        $notif->orga_name = $orga->getName();

        $form = $this->createFormBuilder($notif)
            ->add('link', UrlType::class, ['required' => false, 'label' => 'user.memberships.notification.link.label'])
            ->add('content', TextareaType::class, ['required' => true, 'label' => 'user.memberships.notification.content.label', 'attr' => ['maxlength' => 140, 'help' => 'user.memberships.notification.content.desc']])
            ->add('submit', SubmitType::class, ['label' => $this->get('translator')->trans('user.memberships.notification.submit', ['%orga%' => $membership->getOrganization()->getName()])])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $notification = new Notification();
            $notification->setEntityType('orga')
                ->setEntityId($orga->getId())
                ->setModule('assos')
                ->setAuthorId($this->getUser()->getId())
                ->setHelper('orga_message')
                ->addEntity($notif);

            $this->getNotificationsSender()->send($notification, false);

            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'user.memberships.notification.confirm',
            ]);

            return $this->redirect($this->generateUrl('memberships_orga_notifications', ['login' => $login]));
        }

        return [
            'memberships' => $memberships,
            'membership' => $membership,
            'form' => $form->createView(),
        ];
    }
}
