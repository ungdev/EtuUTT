<?php

namespace Etu\Core\UserBundle\Api\Resource;

use Doctrine\ORM\EntityManager;
use Etu\Core\ApiBundle\Framework\Annotation\Scope;
use Etu\Core\ApiBundle\Framework\Controller\ApiController;
use Etu\Core\UserBundle\Entity\Course;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/private/user")
 */
class PrivateUserController extends ApiController
{
    /**
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
}
