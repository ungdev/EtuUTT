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
use Symfony\Component\Form\FormError;
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
        if ($organization && 'general' != $organization) {
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
        if (is_null($page)) {
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
        $result = $em->createQueryBuilder()
            ->select('p')
            ->from('EtuModuleWikiBundle:WikiPage', 'p')
            ->leftJoin('EtuModuleWikiBundle:WikiPage', 'p2', 'WITH', 'p.slug = p2.slug AND p.createdAt < p2.createdAt')
            ->where('p2.slug IS NULL')
            ->andWhere('p.content != \'\'');
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
        if ($organization && 'general' != $organization) {
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
            if (!empty($slug) && is_null($page)) {
                return $this->createAccessDeniedResponse();
            }
            $page = new WikiPage();
            $page->setReadRight(WikiPage::RIGHT['STUDENT']);
            $page->setEditRight(WikiPage::RIGHT['STUDENT']);
        } else {
            if (is_null($page)) {
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
                ->where('p2.slug IS NULL')
                ->andWhere('p.content != \'\'');
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
            foreach ($result as $key => $value) {
                if ($rights->has($value['readRight'], $organization)) {
                    $pagelist[$key] = $value['title'];
                    if (false !== mb_strpos($key, '/')) {
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
        if (\count($choices) > 1) {
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
        if ($organization && 'general' != $organization) {
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
            ->select(['p as page, CONCAT(p.slug, \'/\') as orderValue'])
            ->from('EtuModuleWikiBundle:WikiPage', 'p')
            ->leftJoin('EtuModuleWikiBundle:WikiPage', 'p2', 'WITH', 'p.slug = p2.slug AND p.createdAt < p2.createdAt')
            ->where('p2.slug IS NULL')
            ->andWhere('p.content != \'\'');
        if ($organization) {
            $result = $result->andWhere('p.organization = :organization')->setParameter(':organization', $organization);
        } else {
            $result = $result->andWhere('p.organization is NULL');
        }
        $result = $result->orderBy('orderValue', 'ASC')
            ->getQuery()
            ->getResult();

        // Formate array, check rights and add ↳ at the beggining of the title if necessary
        $rights = $this->get('etu.wiki.permissions_checker');
        $pagelist = [];
        foreach ($result as $value) {
            $value = $value['page'];
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
     * @param mixed|null $organization
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
            ->andWhere('p.content != \'\'');
        if ($organization) {
            $result = $result->andWhere('p.organization = :organization')->setParameter(':organization', $organization);
        } else {
            $result = $result->andWhere('p.organization is NULL');
        }
        $result = $result
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
                    'value' => $this->generateUrl('wiki_view', ['organization' => ($organization ? $organization->getLogin() : 'general'), 'slug' => $value->getSlug()], true),
                ];
            }
        }

        return new JsonResponse($pagelist);
    }

    /**
     * Called by rich text editor to show a list of available links.
     *
     * @Route("/wiki", name="wiki_list")
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

    /**
     * @Route("/wiki/delete/{organization}/{slug}", requirements={"slug" = "[a-z0-9-/]+"}, name="wiki_delete")
     * @Template()
     *
     * @param mixed $organization
     * @param mixed $slug
     * @param mixed $confirm
     */
    public function deleteAction($organization, $slug, Request $request)
    {
        // Find organization
        $em = $this->getDoctrine()->getManager();
        if ($organization && 'general' != $organization) {
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
        if (is_null($page)) {
            throw $this->createNotFoundException('Wiki page not found');
        } elseif (!$rights->canDelete($page)) {
            return $this->createAccessDeniedResponse();
        }

        // Check for children pages
        $em = $this->getDoctrine()->getManager();
        $result = $em->createQueryBuilder()
            ->select('p')
            ->from('EtuModuleWikiBundle:WikiPage', 'p')
            ->leftJoin('EtuModuleWikiBundle:WikiPage', 'p2', 'WITH', 'p.slug = p2.slug AND p.createdAt < p2.createdAt')
            ->where('p2.slug IS NULL')
            ->andWhere('p.content != \'\'')
            ->andWhere('p.slug like CONCAT( :slug, \'/%\')')->setParameter(':slug', $page->getSlug());
        if ($organization) {
            $result = $result->andwhere('p.organization = :organization')->setParameter(':organization', $organization);
        } else {
            $result = $result->andwhere('p.organization is NULL');
        }
        $childrenPages = $result->orderBy('p.slug', 'ASC')
            ->getQuery()
            ->getResult();

        // Check if the page is homepage
        if ($rights->getHomeSlug($organization) === $page->getSlug()) {
            // Emit flash message
            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'danger',
                'message' => 'wiki.main.delete.isHomepage',
            ]);

            // Redirect
            return $this->redirect($this->generateUrl('wiki_view', [
                'slug' => $page->getSlug(),
                'organization' => ($organization ? $organization->getLogin() : 'general'),
                ]
            ), 301);

            return;
        }

        // Submit yes
        if ('yes' === $request->query->get('confirm') && !$childrenPages) {
            // Force insertion
            $page = clone $page;

            // Set modification author and date
            $page->setAuthor($this->getUser());
            $page->setCreatedAt(new \DateTime());
            $page->setValidated(false);
            $page->delete();

            // Save to db
            $em = $this->getDoctrine()->getManager();
            $em->persist($page);
            $em->flush();

            // Emit flash message
            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'wiki.main.delete.success',
            ]);

            // Redirect
            return $this->redirect($this->generateUrl('wiki_index', [
                'organization' => ($organization ? $organization->getLogin() : 'general'),
                ]
            ), 301);
        }

        return [
            'page' => $page,
            'rights' => $this->get('etu.wiki.permissions_checker'),
            'organization' => $organization,
            'childrenPages' => $childrenPages,
        ];
    }

    /**
     * @Route("/wiki/move/{organization}/{slug}", requirements={"slug" = "[a-z0-9-/]+"}, name="wiki_move")
     * @Template()
     *
     * @param mixed $slug
     * @param mixed $organization
     */
    public function moveAction($organization, $slug, Request $request)
    {
        // Find organization
        $em = $this->getDoctrine()->getManager();
        if ($organization && 'general' != $organization) {
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
        if (is_null($page)) {
            throw $this->createNotFoundException('Wiki page not found');
        } elseif (!$rights->canMove($page)) {
            return $this->createAccessDeniedResponse();
        }

        // Genereate preSlug select field
        $em = $this->getDoctrine()->getManager();
        $result = $em->createQueryBuilder()
            ->select('p.title, p.slug, p.readRight, IDENTITY(p.organization) as organization_id')
            ->from('EtuModuleWikiBundle:WikiPage', 'p', 'p.slug')
            ->leftJoin('EtuModuleWikiBundle:WikiPage', 'p2', 'WITH', 'p.slug = p2.slug AND p.createdAt < p2.createdAt')
            ->where('p2.slug IS NULL')
            ->andWhere('p.content != \'\'');
        if ($organization) {
            $result = $result->andWhere('p.organization = :organization')->setParameter(':organization', $organization);
        } else {
            $result = $result->andWhere('p.organization is NULL');
        }
        $result = $result->orderBy('p.slug', 'ASC')
            ->getQuery()
            ->getResult();

        // Create form
        $form = $this->createFormBuilder();
        $pagelist = [];
        foreach ($result as $key => $value) {
            if ($rights->has($value['readRight'], $organization)
                && 0 !== mb_strpos($key, $page->getSlug())) {
                $pagelist[$key] = $key.'/';
            }
        }
        $form = $form->add('preslug', ChoiceType::class, [
            'choices' => array_flip($pagelist),
            'choice_attr' => function ($val) {
                $level = mb_substr_count($val, '/');

                return ['class' => 'choice_level_'.$level];
            },
            'data' => mb_substr($slug, 0, mb_strrpos($slug, '/')),
            'placeholder' => '-',
            'required' => false,
            'label' => 'wiki.main.move.preslug',
            'mapped' => false,
        ]);
        $form = $form->add('titleSlug', TextType::class, [
            'data' => mb_substr($slug, mb_strrpos($slug, '/')),
            'required' => true,
            'label' => 'wiki.main.move.titleSlug',
            'mapped' => false,
        ]);
        $form = $form->add('submit', SubmitType::class, ['label' => 'wiki.main.move.submit'])
            ->getForm();

        // On form submit
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $oldSlug = $page->getSlug();
            // Create slug
            if (empty($form->get('preslug')->getData())) {
                $newSlug = StringManipulationExtension::slugify($form->get('titleSlug')->getData());
            } else {
                $newSlug = $form->get('preslug')->getData().'/'.StringManipulationExtension::slugify($form->get('titleSlug')->getData());
            }

            // Check if slug is not already used
            $pageTest = $repo->findOneBy([
                'slug' => $newSlug,
                'organization' => $organization,
            ]);
            if ($pageTest) {
                $form->get('titleSlug')->addError(new FormError($this->get('translator')->trans('wiki.main.move.alreadyUsed')));
            } else {
                // Move all modification of this page and all childrens
                $result = $em->createQueryBuilder()
                    ->update('EtuModuleWikiBundle:WikiPage', 'p')
                    ->set('p.slug', 'CONCAT(:newslug, SUBSTRING(p.slug, LENGTH(:oldslug)+1))')
                    ->where('p.slug like CONCAT(:oldslug, \'%\')')
                    ->setParameter(':newslug', $newSlug)
                    ->setParameter(':oldslug', $oldSlug)
                    ->getQuery()
                    ->execute();

                // Emit flash message
                $this->get('session')->getFlashBag()->set('message', [
                    'type' => 'success',
                    'message' => 'wiki.main.move.success',
                ]);

                // Redirect
                return $this->redirect($this->generateUrl('wiki_view', [
                    'organization' => ($organization ? $organization->getLogin() : 'general'),
                    'slug' => $newSlug,
                    ]
                ), 301);
            }
        }

        return [
            'page' => $page,
            'form' => $form->createView(),
            'rights' => $this->get('etu.wiki.permissions_checker'),
            'organization' => $organization,
        ];
    }
}
