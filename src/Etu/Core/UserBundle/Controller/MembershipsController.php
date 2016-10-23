<?php

namespace Etu\Core\UserBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Framework\Definition\OrgaPermission;
use Etu\Core\CoreBundle\Form\RedactorType;
use Etu\Core\UserBundle\Entity\Member;
use Etu\Core\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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

        return array(
            'memberships' => $memberships,
        );
    }

    /**
     * @Route("/user/membership/{login}", name="memberships_orga")
     * @Template()
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
            ->add('contactMail', EmailType::class)
            ->add('contactPhone', null, array('required' => false))
            ->add('description', RedactorType::class, array('required' => false))
            ->add('descriptionShort', TextareaType::class)
            ->add('website', null, array('required' => false))
            ->getForm();

        if ($request->getMethod() == 'POST' && $form->handleRequest($request)->isValid()) {
            $em->persist($orga);
            $em->flush();

            $this->get('session')->getFlashBag()->set('message', array(
                'type' => 'success',
                'message' => 'user.memberships.desc.confirm',
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

        return array(
            'memberships' => $memberships,
            'membership' => $membership,
            'pagination' => $members,
            'orga' => $orga,
        );
    }

    /**
     * @Route("/user/membership/{login}/permissions/{user}/edit", name="memberships_orga_permissions_edit")
     * @Template()
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

        $permissions = array();

        foreach ($availablePermissions as $permission) {
            if ($member->hasPermission($permission->getName())) {
                $permissions[] = array('definition' => $permission, 'checked' => true);
            } else {
                $permissions[] = array('definition' => $permission, 'checked' => false);
            }
        }

        if ($request->getMethod() == 'POST') {
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
                'message' => 'user.memberships.permissionsEdit.confirm',
            ));

            return $this->redirect($this->generateUrl(
                'memberships_orga_permissions_edit', array('login' => $login, 'user' => $user)
            ));
        }

        return array(
            'memberships' => $memberships,
            'membership' => $membership,
            'member' => $member,
            'permissions' => $permissions,
            'orga' => $orga,
        );
    }

    /**
     * @Route("/user/membership/{login}/notification", name="memberships_orga_notifications")
     * @Template()
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
            ->add('link', UrlType::class, array('required' => false))
            ->add('content', TextareaType::class, array('required' => true, 'max_length' => 140))
            ->getForm();

        if ($request->getMethod() == 'POST' && $form->handleRequest($request)->isValid()) {
            $notification = new Notification();
            $notification->setEntityType('orga')
                ->setEntityId($orga->getId())
                ->setModule('assos')
                ->setAuthorId($this->getUser()->getId())
                ->setHelper('orga_message')
                ->addEntity($notif);

            $this->getNotificationsSender()->send($notification, false);

            $this->get('session')->getFlashBag()->set('message', array(
                'type' => 'success',
                'message' => 'user.memberships.notification.confirm',
            ));

            return $this->redirect($this->generateUrl('memberships_orga_notifications', array('login' => $login)));
        }

        return array(
            'memberships' => $memberships,
            'membership' => $membership,
            'form' => $form->createView(),
        );
    }
}
