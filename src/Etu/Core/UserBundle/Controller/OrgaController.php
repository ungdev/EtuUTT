<?php

namespace Etu\Core\UserBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\UserBundle\Entity\Member;
use Etu\Core\UserBundle\Entity\User;
use Etu\Core\UserBundle\Form\UserAutocompleteType;
use Etu\Core\CoreBundle\Form\EditorType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;

class OrgaController extends Controller
{
    /**
     * @Route("/orga", name="orga_admin")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ORGA');

        $orga = $this->getUser();

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        // Try to find a president
        if (!$orga->getPresident()) {
            /** @var $members Member[] */
            $members = $em->createQueryBuilder()
                ->select('m, u')
                ->from('EtuUserBundle:Member', 'm')
                ->leftJoin('m.user', 'u')
                ->where('m.organization = :orga')
                ->setParameter('orga', $this->getUser()->getId())
                ->orderBy('m.role', 'DESC')
                ->addOrderBy('u.lastName')
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
            ->add('name', TextType::class, ['label' => 'user.orga.index.name.label'])
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
                'message' => 'user.orga.index.confirm',
            ]);

            return $this->redirect($this->generateUrl('orga_admin'));
        }

        // Avatar lightbox
        $avatarForm = $this->createFormBuilder($orga, ['attr' => ['id' => 'avatar-upload-form']])
            ->setAction($this->generateUrl('orga_admin_avatar', ['login' => $orga->getLogin()]))
            ->add('file', FileType::class)
            ->getForm();

        return [
            'form' => $form->createView(),
            'avatarForm' => $avatarForm->createView(),
            'rand' => substr(md5(uniqid(true)), 0, 5),
        ];
    }

    /**
     * @Route("/orga/avatar", name="orga_admin_avatar")
     * @Template()
     */
    public function avatarAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ORGA');

        $orga = $this->getUser();

        // Avatar lightbox
        $form = $this->createFormBuilder($orga)
            ->add('file', FileType::class, ['label' => 'user.orga.avatar.file'])
            ->add('submit', SubmitType::class, ['label' => 'user.orga.avatar.submit'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var $em EntityManager */
            $em = $this->getDoctrine()->getManager();

            $orga->upload();

            $em->persist($orga);
            $em->flush();

            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'user.orga.avatar.confirm',
            ]);

            return $this->redirect($this->generateUrl('orga_admin'));
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/orga/members/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="orga_admin_members")
     * @Template()
     */
    public function membersAction($page, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ORGA');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $members = $em->createQueryBuilder()
            ->select('m, u')
            ->from('EtuUserBundle:Member', 'm')
            ->leftJoin('m.user', 'u')
            ->where('m.organization = :orga')
            ->setParameter('orga', $this->getUser()->getId())
            ->orderBy('m.role', 'DESC')
            ->addOrderBy('u.lastName')
            ->getQuery();

        $members = $this->get('knp_paginator')->paginate($members, $page, 20);

        $member = new Member();
        $member->setOrganization($this->getUser());

        $rolesAvailables = Member::getAvailableRoles();
        $roles = [];

        foreach ($rolesAvailables as $key => $role) {
            $roles['user.orga.role.'.$role] = $key;
        }

        $form = $this->createFormBuilder($member)
            ->add('user', UserAutocompleteType::class, ['label' => 'user.orga.members.add_member_user'])
            ->add('role', ChoiceType::class, ['choices' => $roles])
            ->add('submit', SubmitType::class, ['label' => 'user.orga.members.add_member_btn'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
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

            if (!$user) {
                $this->get('session')->getFlashBag()->set('message', [
                    'type' => 'error',
                    'message' => 'user.orga.members.error_user_not_fount',
                ]);
            } else {
                $member->setUser($user);

                // Keep the membership as unique
                $membership = $em->getRepository('EtuUserBundle:Member')->findOneBy([
                    'user' => $member->getUser(),
                    'organization' => $member->getOrganization(),
                ]);

                if (!$membership) {
                    if ($member->getRole() == Member::ROLE_PRESIDENT) {
                        $this->getUser()->setPresident($member->getUser());
                    }

                    $this->getUser()->addCountMembers();
                    $em->persist($this->getUser());

                    $this->getSubscriptionsManager()->subscribe($member->getUser(), 'orga', $this->getUser()->getId());

                    $em->persist($member);
                    $em->flush();

                    $this->get('session')->getFlashBag()->set('message', [
                        'type' => 'success',
                        'message' => 'user.orga.members.confirm_add',
                    ]);
                } else {
                    $this->get('session')->getFlashBag()->set('message', [
                        'type' => 'error',
                        'message' => 'user.orga.members.error_exists',
                    ]);
                }
            }

            return $this->redirect($this->generateUrl(
                'orga_admin_members', ['page' => $page]
            ));
        }

        return [
            'pagination' => $members,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/orga/members/{login}", name="orga_admin_members_edit")
     * @Template()
     */
    public function memberEditAction($login, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ORGA');

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

        if (!$member) {
            throw $this->createNotFoundException(sprintf('Login %s or membership not found', $login));
        }

        $availableRoles = Member::getAvailableRoles();

        foreach ($availableRoles as $key => $role) {
            $availableRoles[$key] = [
                'identifier' => $role,
                'name' => 'user.orga.role.'.$role,
                'selected' => $role == $member->getRole(),
            ];
        }

        $availablePermissions = $this->getKernel()->getAvailableOrganizationsPermissions()->toArray();

        $permissions1 = [];
        $permissions2 = [];

        $i = floor(count($availablePermissions) / 2);

        foreach ($availablePermissions as $permission) {
            if ($member->hasPermission($permission->getName())) {
                $permission = ['definition' => $permission, 'checked' => true];
            } else {
                $permission = ['definition' => $permission, 'checked' => false];
            }

            if ($i == 0) {
                $permissions1[] = $permission;
            } else {
                $permissions2[] = $permission;
                --$i;
            }
        }

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
                'message' => 'user.orga.memberEdit.confirm',
            ]);

            return $this->redirect($this->generateUrl(
                'orga_admin_members_edit', ['login' => $member->getUser()->getLogin()]
            ));
        }

        return [
            'member' => $member,
            'user' => $member->getUser(),
            'roles' => $availableRoles,
            'permissions1' => $permissions1,
            'permissions2' => $permissions2,
        ];
    }

    /**
     * @Route("/orga/members/{login}/delete/{confirm}", defaults={"confirm" = ""}, name="orga_admin_members_delete")
     * @Template()
     */
    public function memberDeleteAction($login, $confirm = '')
    {
        $this->denyAccessUnlessGranted('ROLE_ORGA');

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

        if (!$member) {
            throw $this->createNotFoundException(sprintf('Login %s or membership not found', $login));
        }

        if ($confirm == 'confirm') {
            $user = $member->getUser();

            $em->persist($user);
            $em->remove($member);
            $em->flush();

            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'user.orga.memberDelete.confirm',
            ]);

            return $this->redirect($this->generateUrl('orga_admin_members'));
        }

        return [
            'member' => $member,
            'user' => $member->getUser(),
        ];
    }
}
