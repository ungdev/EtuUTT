<?php

namespace Etu\Core\UserBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\ApiBundle\Framework\Controller\ApiController;
use Etu\Core\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class AjaxController extends ApiController
{
    /**
     * @Route("/user/ajax/search", name="user_ajax_search", options={ "expose" = true })
     */
    public function searchAction(Request $request)
    {
        if (! $this->getUserLayer()->isConnected()) {
            return $this->format([
                'error' => 'Your must be connected to access this page'
            ], 403);
        }

        $term = $request->query->get('term');

        if (mb_strlen($term) < 3) {
            return $this->format([
                'error' => 'Term provided is too short'
            ], 400);
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder();

        $qb ->select('u')
            ->from('EtuUserBundle:User', 'u');

        $keywords = explode(' ', $term);

        foreach ($keywords as $i => $keyword) {
            $qb->andWhere(implode(' OR ', [
                'u.firstName LIKE :k_' . $i,
                'u.lastName LIKE :k_' . $i,
                'u.login LIKE :k_' . $i,
                'u.studentId LIKE :k_' . $i,
            ]));

            $qb->setParameter('k_' . $i, '%' . $keyword . '%');
        }

        /** @var User[] $users */
        $users = $qb->getQuery()->getResult();

        return $this->format([
            'users' => $this->get('etu.api.user.transformer')->transform($users)
        ]);
    }
}
