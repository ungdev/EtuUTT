<?php

namespace Etu\Module\WikiBundle\Controller;

use Etu\Core\CoreBundle\Form\EditorType;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Etu\Module\WikiBundle\Entity\WikiPage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class MainController extends Controller
{
    /**
     * @Route("/wiki/view/{organization}/{slug}", requirements={"slug" = "[a-z0-9-/]+"}, name="wiki_view")
     * @Template()
     *
     * @param mixed $slug
     * @param mixed $category
     * @param mixed $organization
     */
    public function viewAction($slug, $organization, Request $request)
    {
        // Find organization
        $em = $this->getDoctrine()->getManager();
        if ($organization && $organization != 'general') {
            $organization = $em->getRepository('EtuUserBundle:Organization')
                ->findOneBy(['login' => $request->get('organization')]);
            if (!$organization) {
                throw $this->createNotFoundException('Organization not found');
            }
        } else {
            $organization = null;
        }

        // Find last version of a page
        $repo = $this->getDoctrine()->getRepository('EtuModuleWikiBundle:WikiPage');
        $page = $repo->findOneBy([
            'slug' => $slug,
            'organization' => $organization,
        ], ['createdAt' => 'DESC']);

        // If not found
        if (count($page) != 1) {
            throw $this->createNotFoundException('Wiki page not found');
        }

        // Check rights
        $rights = $this->get('etu.wiki.permissions_checker');
        if (!$rights->canRead($page)) {
            return $this->createAccessDeniedResponse();
        }

        // Create page tree
        $slug = $page->getSlug();
        $originalLevel = mb_substr_count($page->getSlug(), '/');
        if ($rights->getHomeSlug($organization) == $page->getSlug()) {
            $slug = '';
            $originalLevel = -1;
        }
        $result = $em->createQueryBuilder()
            ->select('p')
            ->from('EtuModuleWikiBundle:WikiPage', 'p')
            ->leftJoin('EtuModuleWikiBundle:WikiPage', 'p2', 'WITH', 'p.slug = p2.slug AND p.createdAt < p2.createdAt')
            ->where('p2.slug IS NULL');
        if ($slug) {
            $result = $result->andWhere('p.slug LIKE CONCAT(:slug, \'/%\')')->setParameter(':slug', $slug);
        }
        if ($organization) {
            $result = $result->andWhere('p.organization = :organization')->setParameter(':organization', $organization);
        } else {
            $result = $result->andWhere('p.organization is NULL');
        }
        $result = $result->orderBy('p.slug', 'ASC')
            ->getQuery()
            ->getResult();

        // Formate array, check rights and add ↳ at the beggining of the title if necessary
        $rights = $this->get('etu.wiki.permissions_checker');
        $pagelist = [];
        foreach ($result as $value) {
            if ($rights->has($value->getReadRight(), $value->getOrganization())) {
                $pagelist[$value->getSlug()] = [
                    'title' => $value->getTitle(),
                    'organization' => $value->getOrganization(),
                    'level' => mb_substr_count($value->getSlug(), '/') - $originalLevel - 1,
                ];
            }
        }

        return [
            'page' => $page,
            'pagelist' => $pagelist,
            'rights' => $this->get('etu.wiki.permissions_checker'),
            'parentSlug' => mb_substr($slug, 0, mb_strrpos($slug, '/')),
            'organization' => $organization,
        ];
    }

    /**
     * @Route("/wiki/edit/{organization}/{slug}", defaults={"new"=false}, requirements={"slug" = "[a-z0-9-/]+"}, name="wiki_edit")
     * @Route("/wiki/new/{organization}/{slug}", defaults={"new"=true}, requirements={"slug" = "[a-z0-9-/]*"}, name="wiki_new")
     * @Template()
     *
     * @param mixed $category
     * @param mixed $slug
     * @param mixed $new
     * @param mixed $organization
     */
    public function editAction($organization, $slug, $new, Request $request)
    {
        // Find organization
        $em = $this->getDoctrine()->getManager();
        if ($organization && $organization != 'general') {
            $organization = $em->getRepository('EtuUserBundle:Organization')
                ->findOneBy(['login' => $request->get('organization')]);
            if (!$organization) {
                throw $this->createNotFoundException('Organization not found');
            }
        } else {
            $organization = null;
        }

        // Find last version of a page
        $repo = $this->getDoctrine()->getRepository('EtuModuleWikiBundle:WikiPage');
        $page = $repo->findOneBy([
            'slug' => $slug,
            'organization' => $organization,
        ], ['createdAt' => 'DESC']);

        // If not found
        $rights = $this->get('etu.wiki.permissions_checker');
        if ($new) {
            // Check create
            if (!$rights->canCreate($organization)) {
                return $this->createAccessDeniedResponse();
            }
            // Check if subslug exist
            if (!empty($slug) && count($page) != 1) {
                return $this->createAccessDeniedResponse();
            }
            $page = new WikiPage();
            $page->setReadRight(WikiPage::RIGHT['STUDENT']);
            $page->setEditRight(WikiPage::RIGHT['STUDENT']);
        } else {
            if (count($page) != 1) {
                throw $this->createNotFoundException('Wiki page not found');
            } elseif (!$rights->canEdit($page)) {
                return $this->createAccessDeniedResponse();
            }
        }

        // Force insertion
        $page = clone $page;

        // Create form
        $form = $this->createFormBuilder($page)
            ->add('title', TextType::class, ['required' => true, 'label' => 'wiki.main.edit.title']);

        // Create pre-slug selection
        if ($new) {
            $em = $this->getDoctrine()->getManager();
            $result = $em->createQueryBuilder()
                ->select('p.title, p.slug, p.readRight, IDENTITY(p.organization) as organization_id')
                ->from('EtuModuleWikiBundle:WikiPage', 'p', 'p.slug')
                ->leftJoin('EtuModuleWikiBundle:WikiPage', 'p2', 'WITH', 'p.slug = p2.slug AND p.createdAt < p2.createdAt')
                ->where('p2.slug IS NULL');
            if ($organization) {
                $result = $result->where('p.organization = :organization')->setParameter(':organization', $organization);
            } else {
                $result = $result->where('p.organization is NULL');
            }
            $result = $result->orderBy('p.slug', 'ASC')
                ->getQuery()
                ->getResult();
            // Formate array, check rights and add ↳ at the beggining of the title if necessary
            $rights = $this->get('etu.wiki.permissions_checker');
            $pagelist = [];
            foreach ($result as $key => $value) {
                if ($rights->has($value['readRight'], $organization)) {
                    $pagelist[$key] = $value['title'];
                    if (mb_strpos($key, '/') !== false) {
                        $pagelist[$key] = '↳'.$pagelist[$key];
                    }
                }
            }

            // Add "none" item
            $form = $form->add('preslug', ChoiceType::class, [
                'choices' => array_flip($pagelist),
                'choice_attr' => function ($val) {
                    $level = mb_substr_count($val, '/');

                    return ['class' => 'choice_level_'.$level];
                },
                'data' => $slug,
                'placeholder' => '-',
                'required' => false,
                'label' => 'wiki.main.edit.parentPage',
                'mapped' => false,
            ]);
        }

        // Create editor field
        $form = $form->add('content', EditorType::class, ['required' => true, 'label' => 'wiki.main.edit.content', 'organization' => ($organization ? $organization->getLogin() : null)]);

        // Create rights fields
        $choices = [];
        foreach (WikiPage::RIGHT as $right) {
            if ($rights->has($right, $organization)) {
                $choices['wiki.main.right.'.$right] = $right;
            }
        }
        $form = $form->add('readRight', ChoiceType::class, ['choices' => $choices, 'required' => true, 'label' => 'wiki.main.edit.readRight']);
        unset($choices['wiki.main.right.'.WikiPage::RIGHT['ALL']]);
        if (count($choices) > 1) {
            $form = $form->add('editRight', ChoiceType::class, ['choices' => $choices, 'required' => true, 'label' => 'wiki.main.edit.editRight']);
        }

        // Create submit
        $form = $form->add('submit', SubmitType::class, ['label' => 'wiki.main.edit.submit'])
            ->getForm();

        // Submit form
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Create slug if its a new page
            if ($new) {
                if (empty($form->get('preslug')->getData())) {
                    $page->setSlug(StringManipulationExtension::slugify($page->getTitle()));
                } else {
                    $page->setSlug($form->get('preslug')->getData().'/'.StringManipulationExtension::slugify($page->getTitle()));
                }

                // Check if slug is not already used
                $pageTest = $repo->findOneBy([
                    'slug' => $page->getSlug(),
                    'organization' => $organization,
                ]);

                $try = 1;
                $baseSlug = $page->getSlug();
                while ($pageTest) {
                    ++$try;
                    $page->setSlug($baseSlug.'-'.$try);
                    // Check if slug is not already used
                    $pageTest = $repo->findOneBy([
                        'slug' => $page->getSlug(),
                        'organization' => $organization,
                    ]);
                }
            }

            // Set modification author and date
            $page->setAuthor($this->getUser());
            $page->setOrganization($organization);
            $page->setCreatedAt(new \DateTime());
            $page->setValidated(false);

            // Save to db
            $em = $this->getDoctrine()->getManager();
            $em->persist($page);
            $em->flush();

            // Emit flash message
            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'wiki.main.edit.success',
            ]);

            // Redirect
            return $this->redirect($this->generateUrl('wiki_view', [
                'organization' => ($organization ? $organization->getLogin() : 'general'),
                'slug' => $page->getSlug(),
                ]
            ), 301);
        }

        return [
            'page' => $page,
            'form' => $form->createView(),
            'rights' => $this->get('etu.wiki.permissions_checker'),
            'organization' => $organization,
        ];
    }

    /**
     * @Route("/wiki/index/{organization}", name="wiki_index")
     * @Template()
     *
     * @param mixed $category
     * @param mixed $organization
     */
    public function indexAction($organization, Request $request)
    {
        // Find organization
        $em = $this->getDoctrine()->getManager();
        if ($organization && $organization != 'general') {
            $organization = $em->getRepository('EtuUserBundle:Organization')
                ->findOneBy(['login' => $request->get('organization')]);
            if (!$organization) {
                throw $this->createNotFoundException('Organization not found');
            }
        } else {
            $organization = null;
        }

        // Find page tree
        $em = $this->getDoctrine()->getManager();
        $result = $em->createQueryBuilder()
            ->select('p')
            ->from('EtuModuleWikiBundle:WikiPage', 'p')
            ->leftJoin('EtuModuleWikiBundle:WikiPage', 'p2', 'WITH', 'p.slug = p2.slug AND p.createdAt < p2.createdAt')
            ->where('p2.slug IS NULL');
        if ($organization) {
            $result = $result->where('p.organization = :organization')->setParameter(':organization', $organization);
        } else {
            $result = $result->where('p.organization is NULL');
        }
        $result = $result->orderBy('p.slug', 'ASC')
            ->getQuery()
            ->getResult();

        // Formate array, check rights and add ↳ at the beggining of the title if necessary
        $rights = $this->get('etu.wiki.permissions_checker');
        $pagelist = [];
        foreach ($result as $value) {
            if ($rights->has($value->getReadRight(), $value->getOrganization())) {
                $pagelist[$value->getSlug()] = [
                    'title' => $value->getTitle(),
                    'organization' => $value->getOrganization(),
                    'level' => mb_substr_count($value->getSlug(), '/'),
                ];
            }
        }

        return [
            'pagelist' => $pagelist,
            'organization' => $organization,
            'rights' => $this->get('etu.wiki.permissions_checker'),
        ];
    }

    /**
     * Give wiki link list for editor.
     *
     * @Route("/wiki/linklist/{organization}", name="wiki_linklist", options={"expose"=true})
     * @Template()
     *
     * @param null|mixed $organization
     */
    public function editorAction(Request $request, $organization = null)
    {
        // Find organization
        $em = $this->getDoctrine()->getManager();
        if ($organization) {
            $organization = $em->getRepository('EtuUserBundle:Organization')
                ->findOneBy(['login' => $request->get('organization')]);
            if (!$organization) {
                throw $this->createNotFoundException('Organization not found');
            }
        }

        // Find list
        $result = $em->createQueryBuilder()
            ->select('p')
            ->from('EtuModuleWikiBundle:WikiPage', 'p')
            ->leftJoin('EtuModuleWikiBundle:WikiPage', 'p2', 'WITH', 'p.slug = p2.slug AND p.createdAt < p2.createdAt')
            ->where('p2.slug IS NULL')
            ->where('p.organization = :organization')->setParameter(':organization', $organization)
            ->orderBy('p.slug', 'ASC')
            ->getQuery()
            ->getResult();

        // Formate array, check rights and add ↳ at the beggining of the title if necessary
        $rights = $this->get('etu.wiki.permissions_checker');
        $pagelist = [];
        foreach ($result as $value) {
            if ($rights->has($value->getReadRight(), $value->getOrganization())) {
                $pagelist[$value->getSlug()] = [
                    'title' => (mb_substr_count($value->getSlug(), '/') ? str_repeat(' ', mb_substr_count($value->getSlug(), '/')).'↳' : '').$value->getTitle(),
                    'value' => $this->generateUrl('wiki_view', ['organization' => $organization, 'slug' => $value->getSlug()], true),
                ];
            }
        }

        return new JsonResponse($pagelist);
    }

    /**
     * @Route("/wiki/list", name="wiki_list")
     * @Template()
     */
    public function wikilistAction(Request $request)
    {
        $right = WikiPage::RIGHT['ALL'];
        if ($this->isGranted('ROLE_STUDENT')) {
            $right = WikiPage::RIGHT['STUDENT'];
        }

        $em = $this->getDoctrine()->getManager();
        $organizations = $em->createQueryBuilder()
                ->select('o')
                ->from('EtuUserBundle:Organization', 'o')
                ->innerJoin('EtuModuleWikiBundle:WikiPage', 'p', 'WITH', 'o = p.organization')
                ->where('p.readRight >= :right')->setParameter(':right', $right)
                ->orderBy('o.name')
                ->getQuery()
                ->getResult();

        return [
            'organizations' => $organizations,
            'rights' => $this->get('etu.wiki.permissions_checker'),
        ];
    }
}
