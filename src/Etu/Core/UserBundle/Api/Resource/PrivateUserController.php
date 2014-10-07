<?php

namespace Etu\Core\UserBundle\Api\Resource;

use Doctrine\ORM\EntityManager;
use Etu\Core\ApiBundle\Framework\Annotation\Scope;
use Etu\Core\ApiBundle\Framework\Controller\ApiController;
use Etu\Core\ApiBundle\Framework\Embed\EmbedBag;
use Etu\Core\UserBundle\Entity\Course;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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
    public function accountAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('EtuUserBundle:User')->find($this->getAccessToken()->getUser());

        return $this->format([
            'data' => $this->get('etu.api.user.private_transformer')->transform($user)
        ]);
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
    public function scheduleAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $courses = $em->getRepository('EtuUserBundle:Course')->findBy([ 'user' => $this->getAccessToken()->getUser() ]);

        // Order results
        $days = [];
        $hours = [];

        foreach ($courses as $course) {
            if ($course->getDay() == Course::DAY_MONDAY) {
                $days[] = 1;
            } elseif ($course->getDay() == Course::DAY_TUESDAY) {
                $days[] = 2;
            } elseif ($course->getDay() == Course::DAY_WENESDAY) {
                $days[] = 3;
            } elseif ($course->getDay() == Course::DAY_THURSDAY) {
                $days[] = 4;
            } elseif ($course->getDay() == Course::DAY_FRIDAY) {
                $days[] = 5;
            } elseif ($course->getDay() == Course::DAY_SATHURDAY) {
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
            'data' => $this->get('etu.api.course.transformer')->transform($courses)
        ]);
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

        $user = $em->getRepository('EtuUserBundle:User')->find($this->getAccessToken()->getUser());

        return $this->format([
            'data' => $this->get('etu.api.user_orgas_private.transformer')->transform($user->getMemberships()->toArray(), EmbedBag::createFromRequest($request))
        ]);
    }
}
