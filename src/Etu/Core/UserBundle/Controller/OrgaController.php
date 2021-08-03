<?php

namespace Etu\Core\UserBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Form\EditorType;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\UserBundle\Entity\Member;
use Etu\Core\UserBundle\Entity\OrganizationGroup;
use Etu\Core\UserBundle\Entity\OrganizationGroupAction;
use Etu\Core\UserBundle\Entity\User;
use Etu\Core\UserBundle\Form\UserAutocompleteType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
                if (Member::ROLE_PRESIDENT == $member->getRole()) {
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
            ->add('presidentWanted', CheckboxType::class, ['required' => false, 'label'=>'user.orga.index.presidentWanted.label'])
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
            'rand' => mb_substr(md5(uniqid(true)), 0, 5),
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
     *
     * @param mixed $page
     */
    public function membersAction($page, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ORGA');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $member = new Member();
        $member->setOrganization($this->getUser());

        $rolesAvailables = Member::getAvailableRoles();
        $roles = [];

        foreach ($rolesAvailables as $key => $role) {
            $roles['user.orga.role.'.$role] = $key;
        }

        $form = $this->createFormBuilder($member)
            ->add('user', UserAutocompleteType::class, ['label' => 'user.orga.members.add_member_user'])
            ->add('group', ChoiceType::class, [
                'choices' => $this->getUser()->getGroups(),
                'required' => true,
                'choice_label' => function ($value, $key, $choiceValue) {
                    return $value->getName();
                },
                ])
            ->add('role', ChoiceType::class, ['choices' => $roles])
            ->add('submit', SubmitType::class, ['label' => 'user.orga.members.add_member_btn'])
            ->getForm();

        $orgaGroup = new OrganizationGroup();
        $orgaGroup->setOrganization($this->getUser());
        $groupForm = $this->createFormBuilder($orgaGroup)
            ->add('name', TextType::class, ['label' => 'Nom du groupe'])
            ->add('submit', SubmitType::class, ['label' => 'Créer un groupe utilisateur'])
            ->getForm();

        // User formulaire
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
                    if (Member::ROLE_PRESIDENT == $member->getRole()) {
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

        $groupForm->handleRequest($request);
        if ($groupForm->isSubmitted() && $groupForm->isValid()) {
            $em->persist($orgaGroup);
            $em->flush();

            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'Groupe créer !',
            ]);

            return $this->redirect($this->generateUrl(
                'orga_admin_group_edit', ['slug' => $orgaGroup->getSlug()]
            ));
        }

        return [
            'groups' => $this->getUser()->getGroups(),
            'orga' => $this->getUser(),
            'form' => $form->createView(),
            'groupForm' => $groupForm->createView(),
        ];
    }

    /**
     * @Route("/orga/group/{slug}", name="orga_admin_group_edit")
     * @Template()
     *
     * @param mixed $slug
     */
    public function groupEditAction($slug, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ORGA');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        /** @var $group OrganizationGroup */
        $group = $em->createQueryBuilder()
            ->select('o')
            ->from('EtuUserBundle:OrganizationGroup', 'o')
            ->where('o.slug = :slug')
            ->setParameter('slug', $slug)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$group) {
            throw $this->createNotFoundException('Group not found');
        }

        if(!$this->getUser()->getIsOrga() && $group->getOrganization()->getId() !== $this->getUser()->getId())
        {
            throw $this->createAccessDeniedException("Bien tenté jeune padawan... - Autorisation d'accès au groupe refusée");
        }

        /**
         * GROUP EDITION.
         */
        $groupEditForm = $this->createFormBuilder($group)
            ->add('name', TextType::class, ['label' => 'Nom du groupe', 'disabled' => true])
            ->add('slug', TextType::class, ['label' => 'Nom de groupe interne', 'disabled' => true])
            ->add('position', IntegerType::class, ['label' => 'Ordre dans la liste (plus petit en début)', 'required' => true])
            ->add('description', TextareaType::class, ['label' => 'Description (visible sur la page membres)', 'required' => false])
            ->add('submit', SubmitType::class, ['label' => 'Modifier les informations du groupe'])
            ->getForm();

        $groupEditForm->handleRequest($request);
        if ($groupEditForm->isSubmitted() && $groupEditForm->isValid()) {
            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'Groupe édité !',
            ]);
            $em->persist($group);
            $em->flush();
        }

        /**
         * ADD MAILIST FONCTIONNALITY.
         */
        $mailistActionForm = $this->createFormBuilder()
            ->add('mailist_name', TextType::class, ['required' => true, 'label' => 'Identifiant de la mailing-list'])
            ->add('mail_admin', EmailType::class, ['disabled' => true, 'data' => $group->getOrganization()->getSympaMail(), 'label' => 'Compte mail adminstrateur'])
            ->add('submit', SubmitType::class, ['label' => 'Ajout de la souscription automatique'])
            ->getForm();

        $mailistActionForm->handleRequest($request);
        if ($mailistActionForm->isSubmitted() && $mailistActionForm->isValid()) {
            $mailistAction = new OrganizationGroupAction();
            $mailistAction->setGroup($group)
                ->setAction(OrganizationGroupAction::ACTION_MAILIST_ADD_MEMBER)
                ->setData(['mailist' => $mailistActionForm->getData()['mailist_name']]);
            $em->persist($mailistAction);
            $em->flush();

            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'Ajout de la souscription automatique reussit !',
            ]);

            return $this->redirectToRoute('orga_admin_group_edit', ['slug' => $group->getSlug()]);
        }

        return [
            'groupEditForm' => $groupEditForm->createView(),
            'mailistActionForm' => $mailistActionForm->createView(),
            'group' => $group,
            'user' => $this->getUser(),
        ];
    }

    /**
     * @Route("/orga/group/{slug}/delete", name="orga_admin_group_delete")
     *
     * @param mixed $slug
     */
    public function groupDeleteAction($slug, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ORGA');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        /** @var $group OrganizationGroup */
        $group = $em->createQueryBuilder()
            ->select('o')
            ->from('EtuUserBundle:OrganizationGroup', 'o')
            ->where('o.slug = :slug')
            ->setParameter('slug', $slug)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ( !$group) {
            throw $this->createNotFoundException('Group not found');
        }

        if ( !$this->getUser()->getIsOrga() && $group->getOrganization()->getId() !== $this->getUser()->getId()) {
            throw $this->createAccessDeniedException("Bien tenté jeune padawan... - Autorisation d'accès au groupe refusée");
        }

        if($group->getName() === "Bureau" || $group->getName() === "Membres") {
            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'danger',
                'message' => 'Vous ne pouvez pas supprimer le groupe bureau ou membres !',
            ]);

            return $this->redirectToRoute('orga_admin_group_edit', ['slug' => $group->getSlug()]);
        }

        $slugToDelete = $group->getSlug();

        $em->remove($group);
        $em->flush();

        try {
            $ipa = $this->get('etu.sia.ldap');
            $ipa->deleteGroup($slugToDelete);
        } catch (\Exception $e) {
            $logger = $this->get('logger');
            $logger->error('IPA Group deletion fail: '.$e->getMessage());
        }

        $this->get('session')->getFlashBag()->set('message', [
            'type' => 'success',
            'message' => 'Vous avez supprimé le groupe '.$slugToDelete,
        ]);

        return $this->redirectToRoute('orga_admin_members');

    }

    /**
     * @Route("/orga/group/action/{id}/delete", name="orga_admin_group_action_delete")
     * @Template()
     *
     * @param mixed $slug
     * @param mixed $id
     */
    public function organizationGroupActionDeleteAction($id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ORGA');
        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        /** @var $member Member */
        $action = $this->getDoctrine()->getRepository('EtuUserBundle:OrganizationGroupAction')
            ->find($id);

        if (!$action || ($action->getGroup()->getOrganization()->getId() != $this->getUser()->getId())) {
            throw $this->createNotFoundException('Action not found');
        }

        $em->remove($action);
        $em->flush();

        $this->get('session')->getFlashBag()->set('message', [
            'type' => 'success',
            'message' => 'Action supprimé !',
        ]);

        return $this->redirectToRoute('orga_admin_group_edit', ['slug' => $action->getGroup()->getSlug()]);
    }

    /**
     * @Route("/orga/members/{login}", name="orga_admin_members_edit")
     * @Template()
     *
     * @param mixed $login
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

        $availableGroups = $this->getUser()->getGroups();

        $groups = [];
        foreach ($availableGroups as $group) {
            $groups[$group->getId()] = [
                'identifier' => $group,
                'name' => $group->getName(),
                'selected' => $group == $member->getGroup(),
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

            if (0 == $i) {
                $permissions1[] = $permission;
            } else {
                $permissions2[] = $permission;
                --$i;
            }
        }

        if ('POST' == $request->getMethod()) {
            if (null != $request->get('role') && in_array((int) ($request->get('role')), Member::getAvailableRoles())) {
                $member->setRole((int) ($request->get('role')));
            }

            if (null != $request->get('group') || in_array((int) ($request->get('group')), array_keys($groups))) {
                $member->setGroup($groups[(int) ($request->get('group'))]['identifier']);
            }

            if (Member::ROLE_PRESIDENT == $member->getRole()) {
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
            'groups' => $groups,
            'permissions1' => $permissions1,
            'permissions2' => $permissions2,
        ];
    }

    /**
     * @Route("/orga/members/{login}/delete/{confirm}", defaults={"confirm" = ""}, name="orga_admin_members_delete")
     * @Template()
     *
     * @param mixed $login
     * @param mixed $confirm
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

        if ('confirm' == $confirm) {
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
