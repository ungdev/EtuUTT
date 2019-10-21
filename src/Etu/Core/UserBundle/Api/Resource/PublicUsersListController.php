<?php

namespace Etu\Core\UserBundle\Api\Resource;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Etu\Core\ApiBundle\Framework\Controller\ApiController;
use Etu\Core\ApiBundle\Framework\Embed\EmbedBag;
use Etu\Core\UserBundle\Entity\Course;
use Etu\Core\UserBundle\Entity\User;
use Knp\Component\Pager\Pagination\SlidingPagination;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class PublicUsersListController extends ApiController
{
    /**
     * @ApiDoc(
     *   section = "User - Public data",
     *   description = "List of the users (scope: public)",
     *   filters = {
     *      { "name"="firstname",       "dataType"="string"     },
     *      { "name"="lastname",        "dataType"="string"     },
     *      { "name"="name",            "dataType"="string", "description"="Search in firstname, lastname or fullname"     },
     *      { "name"="branch",          "dataType"="string"     },
     *      { "name"="level",           "dataType"="string"     },
     *      { "name"="speciality",      "dataType"="string"     },
     *      { "name"="is_student",      "dataType"="boolean"    },
     *      { "name"="bde_member",      "dataType"="boolean"    },
     *      { "name"="student_id",      "dataType"="integer"    },
     *      { "name"="multifield",      "dataType"="string", "description"="Search between firstname, lastname, fullname, login, student_id, nickname, and emails"     }
     *   },
     *   parameters={
     *      { "name"="embed", "dataType"="string", "description"="Embed foreign entities in the users data (available: badges)", "required"=false }
     *   }
     * )
     *
     * @Route("/public/users", name="api_public_users_list", options={"expose"=true})
     * @Method("GET")
     */
    public function listAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $page = (int) $request->query->get('page', 1);

        /** @var $query QueryBuilder */
        $query = $em->createQueryBuilder()
            ->select('u')
            ->from('EtuUserBundle:User', 'u')
            ->orderBy('u.lastName');

        $query = $this->get('etu.api.user.mapper')->map($query, $request->query);

        /** @var SlidingPagination $pagination */
        $pagination = $this->get('knp_paginator')->paginate($query, $page, 30);

        $previous = false;
        $next = false;

        if ($page > 1) {
            $previous = $this->generateUrl('api_public_users_list', ['page' => $page - 1]);
        }

        if ($page < $pagination->getPaginationData()['pageCount']) {
            $next = $this->generateUrl('api_public_users_list', ['page' => $page + 1]);
        }

        $embedBag = EmbedBag::createFromRequest($request);

        return $this->format([
            'pagination' => [
                'currentPage' => $pagination->getCurrentPageNumber(),
                'totalPages' => $pagination->getPaginationData()['pageCount'],
                'totalItems' => $pagination->getTotalItemCount(),
                'perPage' => $pagination->getItemNumberPerPage(),
                'previous' => $previous,
                'next' => $next,
            ],
            'embed' => $embedBag->getMap(['badges']),
            'data' => $this->get('etu.api.user.transformer')->transform($pagination->getItems(), $embedBag),
        ], 200, [], $request);
    }

    /**
     * @ApiDoc(
     *   description = "View a single user (scope: public)",
     *   section = "User - Public data",
     *   parameters={
     *      { "name"="login", "dataType"="string", "required"=true, "description"="User login" }
     *   }
     * )
     *
     * @Route("/public/users/{login}", name="api_public_users_view")
     * @Method("GET")
     */
    public function viewAction(User $user, Request $request)
    {
        return $this->format([
            'embed' => ['badges' => true],
            'data' => $this->get('etu.api.user.transformer')->transform($user, new EmbedBag(['badges'])),
        ], 200, [], $request);
    }

    /**
     * @ApiDoc(
     *   description = "Badges list of a given user (scope: public)",
     *   section = "User - Public data",
     *   parameters={
     *      { "name"="login", "dataType"="string", "required"=true, "description"="User login" }
     *   }
     * )
     *
     * @Route("/public/users/{login}/badges", name="api_public_users_badges")
     * @Method("GET")
     */
    public function badgesAction(User $user, Request $request)
    {
        $badges = [];

        foreach ($user->getBadges() as $userBadge) {
            $badges[] = $userBadge->getBadge();
        }

        return $this->format([
            'data' => $this->get('etu.api.badge.transformer')->transform($badges),
        ], 200, [], $request);
    }

    /**
     * @ApiDoc(
     *   description = "Courses list of a given user (scope: public)",
     *   section = "User - Public data",
     *   parameters={
     *      { "name"="login", "dataType"="string", "required"=true, "description"="User login" }
     *   }
     * )
     *
     * @Route("/public/users/{login}/courses", name="api_public_users_courses")
     * @Method("GET")
     */
    public function coursesAction(User $user, Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var $courses Course[] */
        $courses = $em->createQueryBuilder()
            ->select('c.uv, c.day, c.start, c.end, c.week, c.type, c.room')
            ->from('EtuUserBundle:Course', 'c')
            ->where('c.deletedAt IS NULL')
            ->andWhere('c.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        return $this->format(['courses' => $courses], 200, [], $request);
    }

    /**
     * @ApiDoc(
     *   section = "User - Public data",
     *   description = "Use /api/public/users instead. List of the users (scope: public)",
     *   deprecated = true,
     *   filters={
     *      { "name"="firstname",       "dataType"="string"     },
     *      { "name"="lastname",        "dataType"="string"     },
     *      { "name"="branch",          "dataType"="string"     },
     *      { "name"="level",           "dataType"="string"     },
     *      { "name"="speciality",      "dataType"="string"     },
     *      { "name"="is_student",      "dataType"="boolean"    },
     *      { "name"="bde_member",      "dataType"="boolean"    }
     *   }
     * )
     *
     * @Route("/users", name="api_users_list", options={"expose"=true})
     * @Method("GET")
     */
    public function listDeprecatedAction(Request $request)
    {
        return $this->listAction($request);
    }

    /**
     * @ApiDoc(
     *   section = "User - Public data",
     *   description = "Use /api/public/users/{login} instead. View a single user (scope: public)",
     *   deprecated = true,
     *   parameters={
     *      { "name"="login", "dataType"="string", "required"=true, "description"="User login" }
     *   }
     * )
     *
     * @Route("/users/{login}", name="api_users_view")
     * @Method("GET")
     */
    public function viewDeprecatedAction(User $user)
    {
        return $this->viewAction($user);
    }
}
