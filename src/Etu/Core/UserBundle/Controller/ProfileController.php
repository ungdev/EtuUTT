<?php

namespace Etu\Core\UserBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\ApiBundle\Entity\OauthClient;
use Etu\Core\CoreBundle\Form\BirthdayPickerType;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\UserBundle\Entity\Course;
use Etu\Core\UserBundle\Entity\Member;
use Etu\Core\UserBundle\Entity\User;
use Etu\Core\UserBundle\Model\BadgesManager;
use Etu\Core\UserBundle\Model\CountriesManager;
use Etu\Core\UserBundle\Schedule\Helper\ScheduleBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends Controller
{
    /**
     * @Route("/user/profile", name="user_profile")
     * @Template()
     */
    public function profileAction()
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $apps = $em->createQueryBuilder()
            ->select('a, c')
            ->from('EtuCoreApiBundle:OauthAuthorization', 'a')
            ->innerJoin('a.client', 'c')
            ->where('a.user = :user')
            ->setParameter('user', $this->getUser()->getId())
            ->groupBy('a.client')
            ->getQuery()
            ->getResult();

        $nativeApps = $em->createQueryBuilder()
            ->select('c')
            ->from('EtuCoreApiBundle:OauthClient', 'c')
            ->where('c.native = 1')
            ->andWhere('c.user = :user')
            ->setParameter('user', $this->getUser()->getId())
            ->getQuery()
            ->getResult();

        return [
            'apps' => $apps,
            'nativeApps' => $nativeApps,
        ];
    }

    /**
     * @Route("/user/apps/revoke/{clientId}", name="user_profile_revoke_app")
     */
    public function appRevokeAction(OauthClient $client)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        // Remove authorization
        $em->createQueryBuilder()
            ->delete()
            ->from('EtuCoreApiBundle:OauthAuthorization', 'a')
            ->where('a.client = :client')
            ->andWhere('a.user = :user')
            ->setParameter('client', $client->getId())
            ->setParameter('user', $this->getUser()->getId())
            ->getQuery()
            ->execute();

        // Remove access_tokens
        $em->createQueryBuilder()
            ->delete()
            ->from('EtuCoreApiBundle:OauthAccessToken', 't')
            ->where('t.client = :client')
            ->andWhere('t.user = :user')
            ->setParameter('client', $client->getId())
            ->setParameter('user', $this->getUser()->getId())
            ->getQuery()
            ->execute();

        // Remove refresh_tokens
        $em->createQueryBuilder()
            ->delete()
            ->from('EtuCoreApiBundle:OauthRefreshToken', 't')
            ->where('t.client = :client')
            ->andWhere('t.user = :user')
            ->setParameter('client', $client->getId())
            ->setParameter('user', $this->getUser()->getId())
            ->getQuery()
            ->execute();

        // Remove authrization_code
        $em->createQueryBuilder()
            ->delete()
            ->from('EtuCoreApiBundle:OauthAuthorizationCode', 't')
            ->where('t.client = :client')
            ->andWhere('t.user = :user')
            ->setParameter('client', $client->getId())
            ->setParameter('user', $this->getUser()->getId())
            ->getQuery()
            ->execute();

        return $this->redirect($this->generateUrl('user_profile'));
    }

    /**
     * @Route("/user/apps/revoke-native/{clientId}", name="user_profile_revoke_native_app")
     */
    public function nativeAppRevokeAction(OauthClient $client)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        // Remove client
        $em->remove($client);
        $em->flush();

        return $this->redirect($this->generateUrl('user_profile'));
    }

    /**
     * @Route("/user/profile/edit", name="user_profile_edit")
     * @Template()
     */
    public function profileEditAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var $user User */
        $user = $this->getUser();

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
            ->add('country', CountryType::class, ['choices' => CountriesManager::getCountriesList(), 'required' => false, 'label' => 'user.profile.profileEdit.country'])
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
            ->add('daymail', CheckboxType::class, [
                'required' => false,
                'label' => 'user.profile.profileEdit.daymail', ])
            ->add('submit', SubmitType::class, ['label' => 'user.profile.profileEdit.edit'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            // Badges
            if (100 == $user->getProfileCompletion()) {
                BadgesManager::userAddBadge($user, 'profile_completed');
            } else {
                BadgesManager::userRemoveBadge($user, 'profile_completed');
            }

            $em->persist($user);
            $em->flush();

            // (Un)Subscribe to daymail
            $mailer = $this->get('mailer');
            if (!empty($user->getMail())) {
                if ($user->getDaymail()) {
                    $message = \Swift_Message::newInstance('Daymail subscription')
                       ->setFrom(['ung@utt.fr' => 'UNG'])
                       ->setTo(['sympa@utt.fr'])
                       ->setBody('QUIET ADD daymail '.$user->getMail().' '.$user->getFullName());
                    $result = $mailer->send($message);
                } else {
                    $message = \Swift_Message::newInstance('Daymail subscription')
                       ->setFrom(['ung@utt.fr' => 'UNG'])
                       ->setTo(['sympa@utt.fr'])
                       ->setBody('QUIET DELETE daymail '.$user->getMail());
                    $result = $mailer->send($message);
                }
            }

            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'user.profile.profileEdit.confirm',
            ]);

            return $this->redirect($this->generateUrl('user_profile_edit'));
        }

        // Avatar lightbox
        $avatarForm = $this->createFormBuilder($user, ['attr' => ['id' => 'avatar-upload-form']])
            ->setAction($this->generateUrl('user_profile_avatar', ['login' => $user->getLogin()]))
            ->add('file', FileType::class)
            ->getForm();

        return [
            'form' => $form->createView(),
            'avatarForm' => $avatarForm->createView(),
        ];
    }

    /**
     * @Route("/user/profile/avatar", name="user_profile_avatar")
     * @Template()
     */
    public function profileAvatarAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var $user User */
        $user = $this->getUser();

        $form = $this->createFormBuilder($user)
            ->add('file', FileType::class, ['label' => 'user.profile.profileAvatar.file'])
            ->add('submit', SubmitType::class, ['label' => 'user.profile.profileAvatar.edit'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $file = $user->upload();

            $em->persist($user);
            $em->flush();

            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'user.profile.profileAvatar.confirm',
            ]);

            return $this->redirect($this->generateUrl('user_profile_edit'));
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/user/trombi/edit", name="user_trombi_edit")
     * @Template()
     */
    public function trombiEditAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var $user User */
        $user = $this->getUser();

        $form = $this->createFormBuilder($user)
            ->add('surnom', null, ['required' => false, 'label' => 'user.profile.trombiEdit.surname'])
            ->add('jadis', null, ['required' => false, 'attr' => ['class' => 'trombi-textarea', 'label' => 'user.profile.trombiEdit.jadis']])
            ->add('passions', null, ['required' => false, 'attr' => ['class' => 'trombi-textarea', 'label' => 'user.profile.trombiEdit.passions']])
            ->add('submit', SubmitType::class, ['label' => 'user.profile.trombiEdit.edit'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if (100 == $user->getTrombiCompletion()) {
                BadgesManager::userAddBadge($user, 'trombi_completed');
            } else {
                BadgesManager::userRemoveBadge($user, 'trombi_completed');
            }

            $em->persist($user);
            $em->flush();

            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'user.profile.trombiEdit.confirm',
            ]);

            return $this->redirect($this->generateUrl('user_profile'));
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/user/{login}", name="user_view")
     * @Template()
     *
     * @param mixed $login
     */
    public function viewAction($login, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_PROFIL');

        if ($login != $this->getUser()->getLogin()) {
            /** @var $em EntityManager */
            $em = $this->getDoctrine()->getManager();

            /** @var $user User */
            $user = $em->getRepository('EtuUserBundle:User')->findOneBy(['login' => $login]);

            if (!$user) {
                throw $this->createNotFoundException('Login "'.$login.'" not found');
            }
        } else {
            $user = $this->getUser();
        }

        $from = null;

        if (\in_array($request->get('from'), ['search', 'profile', 'trombi', 'admin'])) {
            $from = $request->get('from');
        }

        return [
            'user' => $user,
            'from' => $from,
        ];
    }

    /**
     * @Route("/images/profil/{avatar}", name="user_view_image_profil")
     *
     * @param $avatar
     *
     * @return Response
     */
    public function viewImageProfil($avatar)
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_PROFIL');
        $cleanAvatar = preg_replace('/[^a-zA-Z0-9._]/', '', $avatar);
        $cleanAvatar = str_replace('..', '', $cleanAvatar);
        $path = __DIR__.'/../../../../../web/uploads/photos/'.$cleanAvatar;
        if (!file_exists($path) || !mime_content_type($path)) {
            $path = __DIR__.'/../../../../../web/uploads/photos/default-avatar.png';
        }
        $file = file_get_contents($path);
        $headers = [
            'Content-Type' => mime_content_type($path),
            'Content-Disposition' => 'inline; filename="'.$cleanAvatar.'"', ];

        return new Response($file, 200, $headers);
    }

    /**
     * @Route("/user/{login}/organizations", name="user_organizations")
     * @Template()
     *
     * @param mixed $login
     */
    public function organizationsAction($login, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_ORGAS');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        /** @var $user User */
        $user = $em->getRepository('EtuUserBundle:User')->findOneBy(['login' => $login]);

        if (!$user) {
            throw $this->createNotFoundException('Login "'.$login.'" not found');
        }

        /** @var $memberships Member[] */
        $memberships = $em->createQueryBuilder()
            ->select('m, u, o')
            ->from('EtuUserBundle:Member', 'm')
            ->leftJoin('m.user', 'u')
            ->leftJoin('m.organization', 'o')
            ->where('u.login = :login')
            ->setParameter('login', $user->getLogin())
            ->getQuery()
            ->getResult();

        $from = null;

        if (\in_array($request->get('from'), ['search', 'profile', 'trombi', 'admin'])) {
            $from = $request->get('from');
        }

        return [
            'user' => $user,
            'memberships' => $memberships,
            'from' => $from,
        ];
    }

    /**
     * @Route("/user/{login}/schedule/{day}", defaults={"day" = "current"}, name="user_view_schedule")
     * @Template()
     *
     * @param mixed $login
     * @param mixed $day
     */
    public function scheduleAction($login, $day, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_SCHEDULE');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        if ($login != $this->getUser()->getLogin()) {
            /** @var $user User */
            $user = $em->getRepository('EtuUserBundle:User')->findOneBy(['login' => $login]);

            if (!$user) {
                throw $this->createNotFoundException('Login "'.$login.'" not found');
            }
        } else {
            $user = $this->getUser();
        }

        $from = null;

        if (\in_array($request->get('from'), ['search', 'profile', 'trombi', 'admin'])) {
            $from = $request->get('from');
        }

        /** @var $courses Course[] */
        $courses = $em->getRepository('EtuUserBundle:Course')->findByUser($user);

        // Builder to create the schedule
        $builder = new ScheduleBuilder();

        foreach ($courses as $course) {
            $builder->addCourse($course);
        }

        $days = [
            Course::DAY_MONDAY, Course::DAY_TUESDAY, Course::DAY_WENESDAY,
            Course::DAY_THURSDAY, Course::DAY_FRIDAY, Course::DAY_SATHURDAY,
        ];

        if (!\in_array($day, $days)) {
            if (0 == date('w')) { // Sunday
                $day = Course::DAY_MONDAY;
            } else {
                $day = $days[date('w') - 1];
            }
        }

        return [
            'courses' => $builder->build(),
            'currentDay' => $day,
            'user' => $user,
            'from' => $from,
        ];
    }

    /**
     * @Route("/user/{login}/badges", name="user_view_badges")
     * @Template()
     *
     * @param mixed $login
     */
    public function badgesAction($login, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_PROFIL');

        if ($login != $this->getUser()->getLogin()) {
            /** @var $em EntityManager */
            $em = $this->getDoctrine()->getManager();

            /** @var $user User */
            $user = $em->getRepository('EtuUserBundle:User')->findOneBy(['login' => $login]);

            if (!$user) {
                throw $this->createNotFoundException('Login "'.$login.'" not found');
            }
        } else {
            $user = $this->getUser();
        }

        $from = null;

        if (\in_array($request->get('from'), ['search', 'profile', 'trombi', 'admin'])) {
            $from = $request->get('from');
        }

        return [
            'user' => $user,
            'from' => $from,
        ];
    }
}
