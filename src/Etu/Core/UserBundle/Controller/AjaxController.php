<?php

namespace Etu\Core\UserBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\ApiBundle\Framework\Controller\ApiController;
use Etu\Core\UserBundle\Entity\Member;
use Etu\Core\UserBundle\Entity\Organization;
use Etu\Core\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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

    /**
     * @Route("/orga/{login}/remove-phone", name="orga_remove_phone", options={ "expose" = true })
     */
    public function orgaRemovePhoneAction($login)
    {
        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        // Check user type to limit access
        if ($this->getUserLayer()->isUser()) {
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

            if (! $membership) {
                return $this->format([
                        'error' => 'Membership not found'
                    ], 403);
            }

            if (! $membership->hasPermission('edit_desc')) {
                return $this->format([
                        'error' => 'Membership does not have required access'
                    ], 403);
            }
        } else {
            if ($login != $this->getUser()->getLogin()) {
                return $this->format([
                        'error' => 'You can not edit the description of this organization'
                    ], 403);
            }
        }

        /** @var Organization $orga */
        $orga = $em->getRepository('EtuUserBundle:Organization')->findOneBy([ 'login' => $login ]);

        if (! $orga) {
            return $this->format([
                    'error' => 'Orga not found'
                ], 404);
        }

        $orga->setContactPhone(null);

        $em->persist($orga);
        $em->flush();

        return $this->format([ 'phone' => null ]);
    }

    /**
     * @Route("/orga/{login}/remove-website", name="orga_remove_website", options={ "expose" = true })
     */
    public function orgaRemoveWebsiteAction($login)
    {
        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        // Check user type to limit access
        if ($this->getUserLayer()->isUser()) {
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

            if (! $membership) {
                return $this->format([
                        'error' => 'Membership not found'
                    ], 403);
            }

            if (! $membership->hasPermission('edit_desc')) {
                return $this->format([
                        'error' => 'Membership does not have required access'
                    ], 403);
            }
        } else {
            if ($login != $this->getUser()->getLogin()) {
                return $this->format([
                        'error' => 'You can not edit the description of this organization'
                    ], 403);
            }
        }

        /** @var Organization $orga */
        $orga = $em->getRepository('EtuUserBundle:Organization')->findOneBy([ 'login' => $login ]);

        if (! $orga) {
            return $this->format([
                    'error' => 'Orga not found'
                ], 404);
        }

        $orga->setWebsite(null);

        $em->persist($orga);
        $em->flush();

        return $this->format([ 'website' => null ]);
    }
}
