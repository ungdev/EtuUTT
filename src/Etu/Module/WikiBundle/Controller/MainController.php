<?php

namespace Etu\Module\WikiBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Etu\Module\WikiBundle\Entity\WikiPage;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MainController extends Controller
{
    /**
     * @Route("/wiki/view/{category}/{slug}", requirements={"category" = "[a-z0-9-]+", "slug" = "[a-z0-9-/]+"}, name="wiki_view")
     * @Template()
     */
    public function viewAction($slug, $category)
    {
        // Find last version of a page
        $repo = $this->getDoctrine()->getRepository('EtuModuleWikiBundle:WikiPage');
        $page = $repo->findOneBy([
            'slug' => $slug,
            'category' => $category,
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

        return array(
            'page' => $page,
            'rights' => $this->get('etu.wiki.permissions_checker'),
            'parentSlug' => substr($slug, 0, strrpos($slug, '/')),
        );
    }

    /**
     * @Route("/wiki/edit/{category}/{slug}", requirements={"category" = "[a-z0-9-]+", "slug" = "[a-z0-9-/]+"}, name="wiki_edit")
     * @Route("/wiki/new/{category}/{slug}", defaults={"new"=true}, requirements={"category" = "[a-z0-9-]+", "slug" = "[a-z0-9-/]*"}, name="wiki_new")
     * @Template()
     */
    public function editAction($category, $slug, $new = false)
    {
        // Find last version of a page
        $repo = $this->getDoctrine()->getRepository('EtuModuleWikiBundle:WikiPage');
        $page = $repo->findOneBy([
            'slug' => $slug,
            'category' => $category,
        ], ['createdAt' => 'DESC']);

        // If not found
        $rights = $this->get('etu.wiki.permissions_checker');
        if ($new) {
            // Check create
            if (!$rights->canCreate($category)) {
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

        // Create form
        $form = $this->createFormBuilder($page)
            ->add('title', null, array('required' => true, 'label' => 'wiki.main.edit.title'));

        // Create pre-slug selection
        if ($new) {
            $em = $this->getDoctrine()->getManager();
            $result = $em->createQueryBuilder()
                ->select('p.title, p.slug, p.readRight, IDENTITY(p.organization) as organization_id')
                ->from('EtuModuleWikiBundle:WikiPage', 'p', 'p.slug')
                ->leftJoin('EtuModuleWikiBundle:WikiPage', 'p2', 'WITH',  'p.slug = p2.slug AND p.createdAt < p2.createdAt')
                ->where('p2.slug IS NULL')
                ->where('p.category = :category')->setParameter(':category', $category)
                ->orderBy('p.slug', 'ASC')
                ->getQuery()
                ->getResult();
            // Formate array, check rights and add ↳ at the beggining of the title if necessary
            $rights = $this->get('etu.wiki.permissions_checker');
            $pagelist = [];
            foreach ($result as $key => $value) {
                if ($rights->has($value['readRight'], $value['organization_id'])) {
                    $pagelist[$key] = $value['title'];
                    if (strpos($key, '/') !== false) {
                        $pagelist[$key] = '↳'.$pagelist[$key];
                    }
                }
            }

            // Add "none" item
            $form = $form->add('preslug', 'choice', array(
                'choices' => $pagelist,
                'choice_attr' => function ($val) {
                    $level = substr_count($val, '/');

                    return ['class' => 'choice_level_'.$level];
                },
                'data' => $slug,
                'placeholder' => '-',
                'required' => false,
                'label' => 'wiki.main.edit.parentPage',
                'mapped' => false,
            ));
        }

        // Create editor field
        $form = $form->add('content', null, array('required' => true, 'label' => 'wiki.main.edit.content', 'attr' => ['class' => 'redactor']));

        // Create rights fields
        $choices = [];
        $organization_id = ($rights->getOrganization($category)) ? $rights->getOrganization($category)->getId() : null;
        foreach (WikiPage::RIGHT as $right) {
            if ($rights->has($right, $organization_id)) {
                $choices[$right] = 'wiki.main.right.'.$right;
            }
        }
        $form = $form->add('readRight', 'choice', array('choices' => $choices, 'required' => true, 'label' => 'wiki.main.edit.readRight'));
        unset($choices[WikiPage::RIGHT['ALL']]);
        if (count($choices) > 1) {
            $form = $form->add('editRight', 'choice', array('choices' => $choices, 'required' => true, 'label' => 'wiki.main.edit.editRight'));
        }

        // Create submit
        $form = $form->add('submit', SubmitType::class, array('label' => 'wiki.main.edit.submit'))
            ->getForm();

        // Submit form
        if ($this->getRequest()->isMethod('POST') && $form->handleRequest($this->getRequest())->isValid()) {
            // Force insert
            $page->setId(null);

            // Create slug if its a new page
            if ($new) {
                $page->setCategory($category);
                if (empty($form->get('preslug')->getData())) {
                    $page->setSlug(StringManipulationExtension::slugify($page->getTitle()));
                } else {
                    $page->setSlug($form->get('preslug')->getData().'/'.StringManipulationExtension::slugify($page->getTitle()));
                }

                // Check if slug is not already used
                $pageTest = $repo->findOneBy([
                    'slug' => $page->getSlug(),
                    'category' => $page->getCategory(),
                ]);

                $try = 1;
                $baseSlug = $page->getSlug();
                while ($pageTest) {
                    ++$try;
                    $page->setSlug($baseSlug.'-'.$try);
                    // Check if slug is not already used
                    $pageTest = $repo->findOneBy([
                        'slug' => $page->getSlug(),
                        'category' => $page->getCategory(),
                    ]);
                }
            }

            // Set modification author and date
            $page->setAuthor($this->getUser());
            $page->setCreatedAt(new \DateTime());
            $page->setValidated(false);

            // Update organization according to category
            $page->setOrganization($rights->getOrganization($category));

            // Save to db
            $em = $this->getDoctrine()->getManager();
            $em->persist($page);
            $em->flush();

            // Emit flash message
            $this->get('session')->getFlashBag()->set('message', array(
                'type' => 'success',
                'message' => 'wiki.main.edit.success',
            ));

            // Redirect
            return $this->redirect($this->generateUrl('wiki_view', array(
                'category' => $page->getCategory(),
                'slug' => $page->getSlug(),
                )
            ), 301);
        }

        return array(
            'page' => $page,
            'form' => $form->createView(),
            'rights' => $this->get('etu.wiki.permissions_checker'),
        );
    }

    /**
     * @Route("/wiki/list/{category}", requirements={"category" = "[a-z0-9-]+"}, name="wiki_list")
     * @Template()
     */
    public function listAction($category)
    {
        $em = $this->getDoctrine()->getManager();
        $result = $em->createQueryBuilder()
            ->select('p')
            ->from('EtuModuleWikiBundle:WikiPage', 'p')
            ->leftJoin('EtuModuleWikiBundle:WikiPage', 'p2', 'WITH',  'p.slug = p2.slug AND p.createdAt < p2.createdAt')
            ->where('p2.slug IS NULL')
            ->where('p.category = :category')->setParameter(':category', $category)
            ->orderBy('p.slug', 'ASC')
            ->getQuery()
            ->getResult();
        // Formate array, check rights and add ↳ at the beggining of the title if necessary
        $rights = $this->get('etu.wiki.permissions_checker');
        $pagelist = [];
        foreach ($result as $value) {
            $association_id = ($value->getOrganization()) ? $value->getOrganization()->getId() : null;
            if ($rights->has($value->getReadRight(), $association_id)) {
                $pagelist[$value->getSlug()] = [
                    'title' => $value->getTitle(),
                    'category' => $value->getCategory(),
                    'level' => substr_count($value->getSlug(), '/'),
                ];
            }
        }

        return array(
            'pagelist' => $pagelist,
            'category' => $category,
            'rights' => $this->get('etu.wiki.permissions_checker'),
        );
    }
}
