<?php

namespace Etu\Module\WikiBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\UserBundle\Entity\Member;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class MembershipsController extends Controller
{
    /**
     * @Route(
     *      "/user/membership/{login}/wiki",
     *      name="memberships_orga_wiki"
     * )
     * @Template()
     *
     * @param mixed $login
     */
    public function wikiAction($login, Request $request)
    {
        $rights = $this->get('etu.wiki.permissions_checker');
        $this->denyAccessUnlessGranted('ROLE_DAYMAIL_EDIT');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        /** @var $memberships Member[] */
        $memberships = $em->createQueryBuilder()
            ->select('m, o')
            ->from('EtuUserBundle:Member', 'm')
            ->leftJoin('m.organization', 'o')
            ->andWhere('m.user = :user')
            ->setParameter('user', $this->getUser()->getId())
            ->orderBy('m.role', 'DESC')
            ->addOrderBy('o.name', 'ASC')
            ->getQuery()
            ->getResult();

        $membership = null;

        foreach ($memberships as $m) {
            if ($m->getOrganization()->getLogin() == $login) {
                $membership = $m;
                break;
            }
        }

        if (!$membership) {
            throw $this->createNotFoundException('Membership or organization not found for login '.$login);
        }
        $organization = $membership->getOrganization();

        // Get list of pages for form
        $em = $this->getDoctrine()->getManager();
        $result = $em->createQueryBuilder()
            ->select('p.title, p.slug, p.readRight, IDENTITY(p.organization) as organization_id')
            ->from('EtuModuleWikiBundle:WikiPage', 'p', 'p.slug')
            ->leftJoin('EtuModuleWikiBundle:WikiPage', 'p2', 'WITH', 'p.slug = p2.slug AND p.createdAt < p2.createdAt')
            ->where('p2.slug IS NULL')
            ->where('p.organization = :organization')->setParameter(':organization', $organization)
            ->orderBy('p.slug', 'ASC')
            ->getQuery()
            ->getResult();

        // Formate array, check rights and add ↳ at the beggining of the title if necessary
        $rights = $this->get('etu.wiki.permissions_checker');
        $pagelist = [];
        foreach ($result as $key => $value) {
            if ($rights->has($value['readRight'], $organization)) {
                $pagelist[$key] = $value['title'];
            }
        }

        // Create form
        $form = $this->createFormBuilder($organization)
            ->add('wikiHomepage', ChoiceType::class, [
                'choices' => array_flip($pagelist),
                'placeholder' => 'wiki.membership.wiki.placeholder',
                'required' => false,
                'label' => 'wiki.membership.wiki.homepage',
            ])
            ->add('submit', SubmitType::class, ['label' => 'wiki.membership.wiki.submit'])
            ->getForm();

        // Submit form
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$rights->canSetHome($organization)) {
                return $this->createAccessDeniedResponse();
            }

            $em->persist($organization);
            $em->flush();
        }

        return [
            'memberships' => $memberships,
            'membership' => $membership,
            'form' => $form->createView(),
            'organization' => $membership->getOrganization(),
            'rights' => $rights,
        ];
    }
}
