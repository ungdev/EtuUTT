<?php

namespace Etu\Core\UserBundle\Api\Resource;

use Doctrine\ORM\EntityManager;
use Etu\Core\ApiBundle\Entity\OauthClient;
use Etu\Core\ApiBundle\Framework\Annotation\Scope;
use Etu\Core\ApiBundle\Framework\Controller\ApiController;
use Etu\Core\ApiBundle\Framework\Embed\EmbedBag;
use Etu\Core\UserBundle\Entity\Course;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/private/user")
 */
class PrivateUserController extends ApiController
{
    /**
     * Once you connected the user with EtuUTT, you will want to retrieve informations
     * about him / her.
     *
     * This endpoint allows you to retrieve such informations.
     *
     * It's more complete than `/api/public/user/account` as it includes private data
     * (it requires scope `private_user_account`).
     *
     * Be careful with the privacy of the data you access: please use the privacy
     * informations to limit access in your application.
     *
     * @ApiDoc(
     *   section = "User - Private data",
     *   authentication = true,
     *   authenticationRoles = {"private_user_account"},
     *   description = "Get the public and private informations about the current user"
     * )
     *
     * @Route("/account", name="api_private_user_account", options={"expose"=true})
     * @Method("GET")
     * @Scope("private_user_account")
     */
    public function accountAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('EtuUserBundle:User')->find($this->getAccessToken($request)->getUser());

        return $this->format([
            'data' => $this->get('etu.api.user.private_transformer')->transform($user),
        ], 200, [], $request);
    }

    /**
     * @ApiDoc(
     *   description = "Courses list of a given user (scope: private), in case of private schedule",
     *   section = "User - Private data",
     *   authentication = true,
     *   authenticationRoles = {"private_user_schedule"},
     * )
     *
     * @Route("/courses", name="api_private_user_courses", options={"expose"=true})
     * @Method("GET")
     * @Scope("private_user_schedule")
     */
    public function coursesAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var $courses Course[] */
        $courses = $em->createQueryBuilder()
                ->select('c.uv, c.day, c.start, c.end, c.week, c.type, c.room')
                ->from('EtuUserBundle:Course', 'c')
                ->where('c.deletedAt IS NULL')
                ->andWhere('c.user = :user')
                ->setParameter('user', $this->getAccessToken($request)->getUser())
                ->getQuery()
                ->getResult();

        return $this->format(['courses' => $courses], 200, [], $request);
    }

    /**
     * @ApiDoc(
     *   section = "User - Private data",
     *   authentication = true,
     *   authenticationRoles = {"private_user_schedule"},
     *   description = "Get the schedule of the current user"
     * )
     *
     * @Route("/schedule", name="api_private_user_schedule", options={"expose"=true})
     * @Method("GET")
     * @Scope("private_user_schedule")
     */
    public function scheduleAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $courses = $em->getRepository('EtuUserBundle:Course')->findBy(['user' => $this->getAccessToken($request)->getUser()]);

        // Order results
        $days = [];
        $hours = [];

        foreach ($courses as $course) {
            if (Course::DAY_MONDAY == $course->getDay()) {
                $days[] = 1;
            } elseif (Course::DAY_TUESDAY == $course->getDay()) {
                $days[] = 2;
            } elseif (Course::DAY_WENESDAY == $course->getDay()) {
                $days[] = 3;
            } elseif (Course::DAY_THURSDAY == $course->getDay()) {
                $days[] = 4;
            } elseif (Course::DAY_FRIDAY == $course->getDay()) {
                $days[] = 5;
            } elseif (Course::DAY_SATHURDAY == $course->getDay()) {
                $days[] = 6;
            } else {
                $days[] = 7;
            }

            $hours[] = (int) (explode(':', $course->getStart())[0]);
        }

        array_multisort(
            $days, SORT_ASC,
            $hours, SORT_ASC,
            $courses
        );

        return $this->format([
            'data' => $this->get('etu.api.course.transformer')->transform($courses),
        ], 200, [], $request);
    }

    /**
     * This endpoint list you the organizations the current user are in and the permission
     * he or she has in these organizations.
     *
     * @ApiDoc(
     *   section = "User - Private data",
     *   authentication = true,
     *   authenticationRoles = {"private_user_organizations"},
     *   description = "Get the list of organizations of the current user and its permissions in them"
     * )
     *
     * @Route("/organizations", name="api_private_user_organizations", options={"expose"=true})
     * @Method("GET")
     * @Scope("private_user_organizations")
     */
    public function organizationsAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('EtuUserBundle:User')->find($this->getAccessToken($request)->getUser());

        return $this->format([
            'data' => $this->get('etu.api.user_orgas_private.transformer')->transform($user->getMemberships()->toArray(), EmbedBag::createFromRequest($request)),
        ], 200, [], $request);
    }

    /**
     * Store the expo token in database.
     *
     * @ApiDoc(
     *   section = "User - Private data",
     *   description = "Set the expo token that will be used to send push notifications to the device (scope: public)",
     *   parameters = {
     *      {
     *          "name" = "token",
     *          "required" = true,
     *          "dataType" = "string",
     *          "description" = "Expo token"
     *      }
     *   }
     * )
     *
     * @Route("/push-token", name="oauth_push_token", options={"expose"=true})
     * @Method("POST")
     */
    public function setPushToken(Request $request)
    {
        /*
         * Initialize
         */
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $data = json_decode(
            $request->getContent(),
            true
        );
        if (!$data['token'] || mb_strlen(trim($data['token'])) < 3) {
            return $this->format(['error' => 'Le token n\'est pas valide. Contactez l\'auteur de l\'application.'], 400, [], $request);
        }

        /** @var OauthClient $client */
        $client = $this->getAccessToken($request)->getClient();
        if (!$client->getNative()) {
            return $this->format(['error' => 'l\'application n\'est pas une application native'], 401, [], $request);
        }
        $client->setPushToken($data['token']);
        $em->persist($client);
        $em->flush();

        return $this->format(['message' => 'ok', 'token' => $client->getPushToken()], 200, [], $request);
    }
}
