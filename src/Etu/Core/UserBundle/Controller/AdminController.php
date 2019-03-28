<?php

namespace Etu\Core\UserBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Etu\Core\CoreBundle\Form\BirthdayPickerType;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\UserBundle\Entity\Organization;
use Etu\Core\UserBundle\Entity\User;
use Etu\Core\UserBundle\Model\BadgesManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin")
 */
class AdminController extends Controller
{
    /**
     * @Route("/users/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="admin_users_index")
     * @Template()
     *
     * @param mixed $page
     */
    public function usersIndexAction($page, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_ADMIN_PROFIL');

        $user = new User();
        $search = false;
        $users = [];

        $form = $this->createFormBuilder($user)
            ->setMethod('get')
            ->add('fullName', null, ['required' => false, 'label' => 'user.admin.userIndex.name'])
            ->add('studentId', null, ['required' => false, 'label' => 'user.admin.userIndex.studentId'])
            ->add('phoneNumber', null, ['required' => false, 'label' => 'user.admin.userIndex.phone'])
            ->add('uvs', null, ['required' => false, 'label' => 'user.admin.userIndex.uv'])
            ->add('filiere', ChoiceType::class, ['choices' => User::$branches, 'required' => false, 'label' => 'user.admin.userIndex.name'])
            ->add('niveau', ChoiceType::class, ['choices' => User::$levels, 'required' => false, 'label' => 'user.admin.userIndex.name'])
            ->add('personnalMail', null, ['required' => false, 'label' => 'user.admin.userIndex.personnalMail'])
            ->add('submit', SubmitType::class, ['label' => 'user.admin.userIndex.search'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $search = true;

            /** @var $em EntityManager */
            $em = $this->getDoctrine()->getManager();

            /** @var $users QueryBuilder */
            $users = $em->createQueryBuilder()
                ->select('u')
                ->from('EtuUserBundle:User', 'u')
                ->where('u.isStudent = 1')
                ->orderBy('u.lastName');

            if (!$user->getFullName() && !$user->getStudentId() && !$user->getPhoneNumber() && !$user->getUvs() &&
                !$user->getFiliere() && !$user->getNiveau() && !$user->getPersonnalMail()) {
                return $this->redirect($this->generateUrl('trombi_index'));
            }

            if ($user->getFullName()) {
                $where = 'u.login = :login ';
                $users->setParameter('login', $user->getFullName());

                $where .= 'OR u.surnom LIKE :surnom OR (';
                $users->setParameter('surnom', '%'.$user->getFullName().'%');

                $terms = explode(' ', $user->getFullName());

                foreach ($terms as $key => $term) {
                    $where .= 'u.fullName LIKE :name_'.$key.' AND ';
                    $users->setParameter('name_'.$key, '%'.$term.'%');
                }

                $where = mb_substr($where, 0, -5).')';

                $users->andWhere($where);
            }

            if ($user->getStudentId()) {
                $users->andWhere('u.studentId = :id')
                    ->setParameter('id', $user->getStudentId());
            }

            if ($user->getPhoneNumber()) {
                $users->andWhere('u.phoneNumber = :phone')
                    ->setParameter('phone', $user->getPhoneNumber());
            }

            if ($user->getUvs()) {
                $uvs = array_map('trim', explode(',', $user->getUvs()));

                foreach ($uvs as $key => $uv) {
                    $users->andWhere('u.uvs LIKE :uv'.$key)
                        ->setParameter('uv'.$key, '%'.$uv.'%');
                }
            }

            if ($user->getFiliere() && $user->getNiveau()) {
                $users->andWhere('u.niveau = :niveau')
                    ->setParameter('niveau', $user->getFiliere().$user->getNiveau());
            } elseif ($user->getFiliere()) {
                $users->andWhere('u.niveau LIKE :niveau')
                    ->setParameter('niveau', $user->getFiliere().'%');
            } elseif ($user->getNiveau()) {
                $users->andWhere('u.niveau LIKE :niveau')
                    ->setParameter('niveau', '%'.$user->getNiveau());
            }

            if ($user->getPersonnalMail()) {
                $users->andWhere('u.personnalMail = :personnalMail')
                    ->setParameter('personnalMail', $user->getPersonnalMail());
            }

            $users = $this->get('knp_paginator')->paginate($users->getQuery(), $page, 20);
        }

        return [
            'form' => $form->createView(),
            'search' => $search,
            'pagination' => $users,
        ];
    }

    /**
     * @Route("/user/{login}/edit", name="admin_user_edit")
     * @Template()
     *
     * @param mixed $login
     */
    public function userEditAction($login, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_ADMIN_PROFIL');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        /** @var $user User */
        $user = $em->getRepository('EtuUserBundle:User')->findOneBy(['login' => $login]);

        if (!$user) {
            throw $this->createNotFoundException('Login "'.$login.'" not found');
        }

        $privacyChoice = [
            'choices' => [
                'user.privacy.public' => User::PRIVACY_PUBLIC,
                'user.privacy.private' => User::PRIVACY_PRIVATE,
            ],
            'attr' => [
                'class' => 'profileEdit-privacy-select',
            ],
            'placeholder' => false,
            'required' => false,
            'label' => 'user.profile.profileEdit.privacy',
        ];

        $form = $this->createFormBuilder($user)
            ->add('phoneNumber', null, ['required' => false, 'label' => 'user.profile.profileEdit.phoneNumber'])
            ->add('phoneNumberPrivacy', ChoiceType::class, $privacyChoice)
            ->add('sex', ChoiceType::class, ['choices' => [
                'base.user.sex.male' => User::SEX_MALE,
                'base.user.sex.female' => User::SEX_FEMALE,
            ], 'required' => false, 'label' => 'user.profile.profileEdit.sex'])
            ->add('sexPrivacy', ChoiceType::class, $privacyChoice)
            ->add('nationality', null, ['required' => false, 'label' => 'user.profile.profileEdit.nationality'])
            ->add('nationalityPrivacy', ChoiceType::class, $privacyChoice)
            ->add('address', null, ['required' => false, 'label' => 'user.profile.profileEdit.address'])
            ->add('addressPrivacy', ChoiceType::class, $privacyChoice)
            ->add('postalCode', null, ['required' => false, 'label' => 'user.profile.profileEdit.postalCode'])
            ->add('postalCodePrivacy', ChoiceType::class, $privacyChoice)
            ->add('city', null, ['required' => false, 'label' => 'user.profile.profileEdit.city'])
            ->add('cityPrivacy', ChoiceType::class, $privacyChoice)
            ->add('country', null, ['required' => false, 'label' => 'user.profile.profileEdit.country'])
            ->add('countryPrivacy', ChoiceType::class, $privacyChoice)
            ->add('birthday', BirthdayPickerType::class, [
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'required' => false,
                'label' => 'user.profile.profileEdit.birthday',
            ])
            ->add('birthdayPrivacy', ChoiceType::class, $privacyChoice)
            ->add('birthdayDisplayOnlyAge', null, [
                'required' => false,
                'label' => 'user.profile.profileEdit.birthdayOnlyAge.label',
                'attr' => [
                    'help' => 'user.profile.profileEdit.birthdayOnlyAge.desc',
                ], ])
            ->add('personnalMail', EmailType::class, ['required' => false, 'label' => 'user.profile.profileEdit.personnalMail'])
            ->add('personnalMailPrivacy', ChoiceType::class, $privacyChoice)
            ->add('website', null, ['required' => false, 'label' => 'user.profile.profileEdit.website'])
            ->add('facebook', null, ['required' => false, 'label' => 'user.profile.profileEdit.facebook'])
            ->add('twitter', null, ['required' => false, 'label' => 'user.profile.profileEdit.twitter'])
            ->add('linkedin', null, ['required' => false, 'label' => 'user.profile.profileEdit.linkedin'])
            ->add('viadeo', null, ['required' => false, 'label' => 'user.profile.profileEdit.viadeo'])
            ->add('submit', SubmitType::class, ['label' => 'user.profile.profileEdit.edit'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if ($user->getProfileCompletion() == 100) {
                BadgesManager::userAddBadge($user, 'profile_completed');
            } else {
                BadgesManager::userRemoveBadge($user, 'profile_completed');
            }

            if ($user->getTrombiCompletion() == 100) {
                BadgesManager::userAddBadge($user, 'trombi_completed');
            } else {
                BadgesManager::userRemoveBadge($user, 'trombi_completed');
            }

            BadgesManager::userPersistBadges($user);
            $em->persist($user);
            $em->flush();

            $logger = $this->get('monolog.logger.admin');
            $logger->info('`'.$this->getUser()->getLogin().'` update profil of `'.$user->getLogin().'`');

            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'user.admin.userEdit.confirm',
            ]);

            return $this->redirect($this->generateUrl('user_view', ['login' => $user->getLogin()]));
        }

        // Avatar lightbox
        $avatarForm = $this->createFormBuilder($user, ['attr' => ['id' => 'avatar-upload-form']])
            ->setAction($this->generateUrl('admin_user_edit_avatar', ['login' => $user->getLogin()]))
            ->add('file', FileType::class)
            ->getForm();

        return [
            'user' => $user,
            'form' => $form->createView(),
            'avatarForm' => $avatarForm->createView(),
        ];
    }

    /**
     * @Route("/users/permissions", name="admin_user_roles_list")
     * @Template()
     */
    public function userRolesListAction()
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_ADMIN_ROLES');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $qb = $em->getRepository('EtuUserBundle:User')->createQueryBuilder('u');
        $qb->where('u.storedRoles != \'a:0:{}\'');
        $users = $qb->getQuery()->getResult();

        // Retrieve role list
        $predefinedRoles = ['ROLE_READONLY', 'ROLE_BANNED', 'ROLE_USER', 'ROLE_ORGA', 'ROLE_STUDENT', 'ROLE_STAFFUTT', 'ROLE_EXTERNAL', 'ROLE_CAS'];
        $hierarchy = $this->getParameter('security.role_hierarchy.roles');
        $roles = array_keys($hierarchy);
        $roles = array_diff($roles, $predefinedRoles);

        return [
            'users' => $users,
            'hierarchy' => $hierarchy,
        ];
    }

    /**
     * @Route("/user/{login}/permissions", name="admin_user_roles")
     * @Template()
     *
     * @param mixed $login
     */
    public function userRolesAction($login, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_ADMIN_ROLES');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        /** @var $user User */
        $user = $em->getRepository('EtuUserBundle:User')->findOneBy(['login' => $login]);

        if (!$user) {
            throw $this->createNotFoundException('Login "'.$login.'" not found');
        }

        // Get 'from' to choose the right back button
        $from = null;
        if (in_array($request->get('from'), ['profile', 'admin', 'organizations', 'badges', 'schedule'])) {
            $from = $request->get('from');
        }

        // Retrieve role list
        $predefinedRoles = ['ROLE_READONLY', 'ROLE_BANNED', 'ROLE_USER', 'ROLE_ORGA', 'ROLE_STUDENT', 'ROLE_STAFFUTT', 'ROLE_EXTERNAL', 'ROLE_CAS'];
        $hierarchy = $this->getParameter('security.role_hierarchy.roles');
        $roles = array_keys($hierarchy);
        $roles = array_diff($roles, $predefinedRoles);

        // See what role user already have
        $storedRoles = $user->getStoredRoles();
        $roleArray = [];
        foreach ($roles as $role) {
            $roleArray[] = [
                'role' => $role,
                'checked' => (in_array($role, $storedRoles)),
                'subroles' => $hierarchy[$role],
            ];
        }

        if ($request->getMethod() == 'POST') {
            $roles = [];
            $postedRoles = $request->get('role');
            if (empty($postedRoles)) {
                $postedRoles = [];
            }

            foreach ($postedRoles as $key => $value) {
                if (isset($hierarchy[$key])) {
                    $roles[] = $key;
                }
            }
            $user->setStoredRoles($roles);
            $em->persist($user);
            $em->flush();

            $logger = $this->get('monolog.logger.admin');
            $logger->warn('`'.$this->getUser()->getLogin().'` update roles of `'.$user->getLogin().'`');

            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'user.admin.userRoles.confirm',
            ]);

            return $this->redirect($this->generateUrl('admin_user_roles', ['login' => $user->getLogin(), 'from' => $from]));
        }

        return [
            'user' => $user,
            'roles' => $roleArray,
            'fromVar' => $from,
        ];
    }

    /**
     * @Route("/user/{login}/avatar", name="admin_user_edit_avatar", options={"expose"=true})
     * @Template()
     *
     * @param mixed $login
     */
    public function userAvatarAction($login, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_ADMIN_PROFIL');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        /** @var $user User */
        $user = $em->getRepository('EtuUserBundle:User')->findOneBy(['login' => $login]);

        if (!$user) {
            throw $this->createNotFoundException('Login "'.$login.'" not found');
        }

        $form = $this->createFormBuilder($user)
            ->add('file', FileType::class, ['label' => 'user.admin.userAvatar.file'])
            ->add('submit', SubmitType::class, ['label' => 'user.admin.userAvatar.submit'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $user->upload();

            $em->persist($user);
            $em->flush();

            $logger = $this->get('monolog.logger.admin');
            $logger->info('`'.$this->getUser()->getLogin().'` update avatar of `'.$user->getLogin().'`');

            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'user.admin.userEdit.confirm',
            ]);

            return $this->redirect($this->generateUrl('user_view', ['login' => $user->getLogin()]));
        }

        return [
            'user' => $user,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/user/{login}/toggle-readonly", name="admin_user_toggle_readonly")
     * @Template()
     *
     * @param mixed $login
     */
    public function userReadOnlyAction($login)
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_ADMIN_PROFIL');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        /** @var $user User */
        $user = $em->getRepository('EtuUserBundle:User')->findOneBy(['login' => $login]);

        if (!$user) {
            throw $this->createNotFoundException('Login "'.$login.'" not found');
        }

        if (!$user->isReadOnly()) {
            $expiration = new \DateTime();
            $expiration->add(new \DateInterval('P1Y'));
            $user->setReadOnlyExpirationDate($expiration);

            $logger = $this->get('monolog.logger.admin');
            $logger->warn('`'.$this->getUser()->getLogin().'` put `'.$user->getLogin().'` on read only ');

            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'user.admin.userReadOnly.confirm_set',
            ]);
        } else {
            $user->setReadOnlyExpirationDate(null);

            $logger = $this->get('monolog.logger.admin');
            $logger->warn('`'.$this->getUser()->getLogin().'` remove `'.$user->getLogin().'` from read only');

            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'user.admin.userReadOnly.confirm_unset',
            ]);
        }

        $em->persist($user);
        $em->flush();

        return $this->redirect($this->generateUrl('user_view', ['login' => $user->getLogin()]).'?from=admin');
    }

    /**
     * @Route("/user/create", name="admin_user_create")
     * @Template()
     */
    public function userCreateAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_ADMIN_PROFIL');

        /** @var $user User */
        $user = new User();
        $user->setIsStudent(true);
        $user->setIsInLDAP(false);

        $privacyChoice = [
            'choices' => [
                'user.privacy.public' => User::PRIVACY_PUBLIC,
                'user.privacy.private' => User::PRIVACY_PRIVATE,
            ],
            'placeholder' => false,
            'attr' => [
                'class' => 'profileEdit-privacy-select',
            ],
            'required' => false,
            'label' => 'user.admin.userCreate.privacy',
        ];
        $form = $this->createFormBuilder($user)
            ->add('fullName', null, ['required' => true, 'label' => 'user.admin.userCreate.name'])
            ->add('mail', EmailType::class, ['required' => true, 'label' => 'user.admin.userCreate.mail', 'attr' => ['help' => 'user.admin.userCreate.mail_desc']])
            ->add('password', PasswordType::class, ['required' => true, 'label' => 'user.admin.userCreate.password'])
            ->add('phoneNumber', null, ['required' => false, 'label' => 'user.admin.userCreate.phoneNumber'])
            ->add('phoneNumberPrivacy', ChoiceType::class, $privacyChoice)
            ->add('sex', ChoiceType::class, ['choices' => [
                User::SEX_MALE => 'base.user.sex.male',
                User::SEX_FEMALE => 'base.user.sex.female',
            ], 'required' => false, 'label' => 'user.admin.userCreate.sex'])
            ->add('sexPrivacy', ChoiceType::class, $privacyChoice)
            ->add('nationality', null, ['required' => false, 'label' => 'user.admin.userCreate.nationality'])
            ->add('nationalityPrivacy', ChoiceType::class, $privacyChoice)
            ->add('address', null, ['required' => false, 'label' => 'user.admin.userCreate.address'])
            ->add('addressPrivacy', ChoiceType::class, $privacyChoice)
            ->add('postalCode', null, ['required' => false, 'label' => 'user.admin.userCreate.postalCode'])
            ->add('postalCodePrivacy', ChoiceType::class, $privacyChoice)
            ->add('city', null, ['required' => false, 'label' => 'user.admin.userCreate.city'])
            ->add('cityPrivacy', ChoiceType::class, $privacyChoice)
            ->add('country', null, ['required' => false, 'label' => 'user.admin.userCreate.country'])
            ->add('countryPrivacy', ChoiceType::class, $privacyChoice)
            ->add('birthday', BirthdayType::class, [
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'required' => false,
                'label' => 'user.admin.userCreate.birthday',
                'attr' => [
                    'placeholder' => 'jj/mm/aaaa',
                ],
            ])
            ->add('birthdayPrivacy', ChoiceType::class, $privacyChoice)
            ->add('birthdayDisplayOnlyAge', null, [
                'required' => false,
                'label' => 'user.admin.userCreate.birthday_only_age_label',
                'attr' => ['help' => 'user.admin.userCreate.birthday_only_age_desc'],
            ])
            ->add('personnalMail', EmailType::class, ['required' => false, 'label' => 'user.admin.userCreate.personnalMail'])
            ->add('personnalMailPrivacy', ChoiceType::class, $privacyChoice)
            ->add('website', null, ['required' => false, 'label' => 'user.admin.userCreate.website'])
            ->add('facebook', null, ['required' => false, 'label' => 'user.admin.userCreate.facebook'])
            ->add('twitter', null, ['required' => false, 'label' => 'user.admin.userCreate.twitter'])
            ->add('linkedin', null, ['required' => false, 'label' => 'user.admin.userCreate.linkedin'])
            ->add('viadeo', null, ['required' => false, 'label' => 'user.admin.userCreate.viadeo'])
            ->add('isStudent', null, [
                'required' => false,
                'label' => 'user.admin.userCreate.is_student_label',
                'attr' => ['help' => 'user.admin.userCreate.is_student_desc'],
            ])
            ->add('isStaffUTT', null, [
                'required' => false,
                'label' => 'user.admin.userCreate.is_staffutt_label',
                'attr' => ['help' => 'user.admin.userCreate.is_staffutt_desc'],
            ])
            ->add('submit', SubmitType::class, ['label' => 'user.admin.userCreate.submit'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if ($user->getProfileCompletion() == 100) {
                BadgesManager::userAddBadge($user, 'profile_completed');
            } else {
                BadgesManager::userRemoveBadge($user, 'profile_completed');
            }

            if ($user->getTrombiCompletion() == 100) {
                BadgesManager::userAddBadge($user, 'trombi_completed');
            } else {
                BadgesManager::userRemoveBadge($user, 'trombi_completed');
            }

            BadgesManager::userPersistBadges($user);
            $user->setPassword($this->get('security.password_encoder')->encodePassword($user, $user->getPassword()));
            $user->setIsInLDAP(false);

            // Set external user login and studentId
            $user->setLogin($user->getMail());
            $lowestId = $em->createQueryBuilder()
                ->select('MIN(u.studentId)')
                ->from('EtuUserBundle:User', 'u')
                ->getQuery()
                ->getSingleScalarResult();
            $user->setStudentId($lowestId - 1);

            $em->persist($user);
            $em->flush();

            $logger = $this->get('monolog.logger.admin');
            $logger->info('`'.$this->getUser()->getLogin().'` create an user `'.$user->getLogin().'`');

            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'user.admin.userCreate.confirm',
            ]);

            return $this->redirect($this->generateUrl('user_view', ['login' => $user->getLogin()]));
        }

        return [
            'user' => $user,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/user/{login}/delete/{confirm}", defaults={"confirm" = ""}, name="admin_user_delete")
     * @Template()
     *
     * @param mixed $login
     * @param mixed $confirm
     */
    public function userDeleteAction($login, $confirm = '')
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_ADMIN_PROFIL');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        /** @var $user User */
        $user = $em->getRepository('EtuUserBundle:User')->findOneBy(['login' => $login]);

        if (!$user) {
            throw $this->createNotFoundException('Login "'.$login.'" not found');
        }

        if ($confirm == 'confirm') {
            $user->setDeletedAt(new \DateTime());

            $em->persist($user);
            $em->flush();

            $logger = $this->get('monolog.logger.admin');
            $logger->warn('`'.$this->getUser()->getLogin().'` delete an user `'.$user->getLogin().'`');

            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'user.admin.userDelete.confirm',
            ]);

            return $this->redirect($this->generateUrl('admin_users_index'));
        }

        return [
            'user' => $user,
        ];
    }

    /**
     * @Route("/orgas/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="admin_orgas_index")
     * @Template()
     *
     * @param mixed $page
     */
    public function orgasIndexAction($page = 1)
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_ADMIN_PROFIL');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $orgas = $em->createQueryBuilder()
            ->select('o, p')
            ->from('EtuUserBundle:Organization', 'o')
            ->leftJoin('o.president', 'p')
            ->orderBy('o.name')
            ->getQuery();

        $orgas = $this->get('knp_paginator')->paginate($orgas, $page, 20);

        return [
            'pagination' => $orgas,
        ];
    }

    /**
     * @Route("/orgas/create", name="admin_orgas_create")
     * @Template()
     */
    public function orgasCreateAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_ADMIN_PROFIL');

        /** @var $orga Organization */
        $orga = new Organization();

        $form = $this->createFormBuilder($orga)
            ->add('login', null, ['required' => true, 'label' => 'user.admin.orgasCreate.login'])
            ->add('name', null, ['required' => true, 'label' => 'user.admin.orgasCreate.name'])
            ->add('sympaMail', EmailType::class, ['required' => false, 'label' => 'user.admin.orgasCreate.sympaMail'])
            ->add('descriptionShort', TextareaType::class, ['required' => true, 'label' => 'user.admin.orgasCreate.descriptionShort'])
            ->add('submit', SubmitType::class, ['label' => 'user.admin.orgasCreate.submit'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($orga);
            $em->flush();

            $logger = $this->get('monolog.logger.admin');
            $logger->info('`'.$this->getUser()->getLogin().'` create organization `'.$orga->getLogin().'`');

            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'user.admin.orgasCreate.confirm',
            ]);

            return $this->redirect($this->generateUrl('admin_orgas_index'));
        }

        return [
            'orga' => $orga,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/orgas/{login}/delete", name="admin_orgas_delete")
     * @Template()
     *
     * @param mixed $login
     */
    public function orgasDeleteAction($login)
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_ADMIN_PROFIL');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        /** @var $orga Organization */
        $orga = $em->getRepository('EtuUserBundle:Organization')->findOneBy(['login' => $login]);

        if (!$orga) {
            throw $this->createNotFoundException(sprintf('Login %s not found', $login));
        }

        /** @var $members Members of the organisation to be removed */
        $members = $em->getRepository('EtuUserBundle:Member')
                ->findBy(['organization' => $orga]);

        foreach ($members as $member) {
            $member->setDeletedAt(new \DateTime());
            $em->persist($member);
        }

        $orga->setDeletedAt(new \DateTime());

        $em->persist($orga);
        $em->flush();

        $logger = $this->get('monolog.logger.admin');
        $logger->warn('`'.$this->getUser()->getLogin().'` delete organization `'.$orga->getLogin().'`');

        $this->get('session')->getFlashBag()->set('message', [
            'type' => 'success',
            'message' => 'user.admin.orgasDelete.confirm',
        ]);

        return $this->redirect($this->generateUrl('admin_orgas_index'));
    }

    /**
     * @Route("/log-as", name="admin_log-as")
     * @Template()
     */
    public function logAsAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_SWITCH');

        $em = $this->getDoctrine()->getManager();

        if ($request->getMethod() == 'POST') {
            if (!empty($request->get('orga'))) {
                $orga = $em->createQueryBuilder()
                    ->select('o')
                    ->from('EtuUserBundle:Organization', 'o')
                    ->where('o.name = :input')
                    ->orWhere('o.login = :input')
                    ->setParameter('input', $request->get('orga'))
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult();

                if (!$orga) {
                    $this->get('session')->getFlashBag()->set('message', [
                        'type' => 'error',
                        'message' => 'user.admin.logAs.orga_not_found',
                    ]);
                } else {
                    $logger = $this->get('monolog.logger.admin');
                    $logger->warn('`'.$this->getUser()->getLogin().'` login as organization `'.$orga->getLogin().'`');

                    $this->get('session')->getFlashBag()->set('message', [
                        'type' => 'success',
                        'message' => 'user.auth.connect.confirm',
                    ]);

                    return $this->redirect($this->generateUrl('homepage', ['_switch_user' => $orga->getLogin()]));
                }
            } else {
                $user = $em->createQueryBuilder()
                    ->select('u')
                    ->from('EtuUserBundle:User', 'u')
                    ->where('u.fullName = :input')
                    ->orWhere('u.login = :input')
                    ->setParameter('input', $request->get('user'))
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult();

                if (!$user) {
                    $this->get('session')->getFlashBag()->set('message', [
                        'type' => 'error',
                        'message' => 'user.admin.logAs.user_not_found',
                    ]);
                } else {
                    $logger = $this->get('monolog.logger.admin');
                    $logger->warn('`'.$this->getUser()->getLogin().'` login as an user `'.$user->getLogin().'`');

                    $this->get('session')->getFlashBag()->set('message', [
                        'type' => 'success',
                        'message' => 'user.auth.connect.confirm',
                    ]);

                    return $this->redirect($this->generateUrl('homepage', ['_switch_user' => $user->getLogin()]));
                }
            }
        }
    }

    /**
     * @Route("/log-as/back", name="admin_log-as_back")
     * @Template()
     */
    public function logAsBackAction()
    {
        $this->denyAccessUnlessGranted('ROLE_PREVIOUS_ADMIN');

        $this->get('session')->getFlashBag()->set('message', [
            'type' => 'success',
            'message' => 'user.admin.logAs.welcomeBack',
        ]);

        return $this->redirect($this->generateUrl('homepage', ['_switch_user' => '_exit']));
    }
}
