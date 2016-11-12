<?php

namespace Etu\Module\TrombiBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class MainController extends Controller
{
    /**
     * @Route("/trombi/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="trombi_index")
     * @Template()
     */
    public function indexAction($page, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_TROMBI');

        $user = new User();
        $search = false;
        $users = [];

        $form = $this->createFormBuilder($user)
            ->setMethod('get')
            ->add('fullName', null, ['required' => false])
            ->add('studentId', null, ['required' => false])
            ->add('phoneNumber', null, ['required' => false])
            ->add('uvs', null, ['required' => false])
            ->add('branch', ChoiceType::class, ['choices' => User::$branches, 'required' => false])
            ->add('niveau', ChoiceType::class, ['choices' => User::$levels, 'required' => false])
            ->add('personnalMail', null, ['required' => false])
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
                !$user->getBranch() && !$user->getNiveau() && !$user->getPersonnalMail()) {
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

                $where = substr($where, 0, -5).')';

                $users->andWhere($where);
            }

            if ($user->getStudentId()) {
                $users->andWhere('u.studentId = :id')
                    ->setParameter('id', $user->getStudentId());
            }

            if ($user->getPhoneNumber()) {
                $phone = $user->getPhoneNumber();
                $parts = [];

                if (strpos($phone, '.') !== false) {
                    $parts = explode('.', $phone);
                } elseif (strpos($phone, '-') !== false) {
                    $parts = explode('-', $phone);
                } elseif (strpos($phone, ' ') !== false) {
                    $parts = explode(' ', $phone);
                } else {
                    $parts = str_split($phone, 2);
                }

                $users->andWhere('u.phoneNumber LIKE :phone')
                    ->setParameter('phone', implode('%', $parts));
            }

            if ($user->getUvs()) {
                $uvs = array_map('trim', explode(',', $user->getUvs()));

                foreach ($uvs as $key => $uv) {
                    $users->andWhere('u.uvs LIKE :uv'.$key)
                        ->setParameter('uv'.$key, '%'.$uv.'%');
                }
            }

            if ($user->getBranch()) {
                $users->andWhere('u.branch = :branch')
                    ->setParameter('branch', $user->getBranch());
            }

            if ($user->getNiveau()) {
                $users->andWhere('u.niveau = :niveau')
                    ->setParameter('niveau', $user->getNiveau());
            }

            if ($user->getPersonnalMail()) {
                $users->andWhere('u.personnalMail = :personnalMail')
                    ->setParameter('personnalMail', $user->getPersonnalMail());
            }

            $query = $users->getQuery();
            $query->useResultCache(true, 3600 * 24);

            $users = $this->get('knp_paginator')->paginate($query, $page, 10);
        }

        return [
            'form' => $form->createView(),
            'search' => $search,
            'pagination' => $users,
        ];
    }
}
