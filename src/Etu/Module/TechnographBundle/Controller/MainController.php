<?php

namespace Etu\Module\TechnographBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Etu\Core\CoreBundle\Form\EditorEmailType;
use Etu\Module\TechnographBundle\Entity\Article;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/technograph")
 */
class MainController extends Controller
{
  /**
   * @Route("", name="technograph_index")
   * @Template()
   */
  public function indexAction()
  {
      $this->denyAccessUnlessGranted('ROLE_TECHNOGRAPH_READ');
      /** @var $em EntityManager */
      $em = $this->getDoctrine()->getManager();

      $query = $em->createQueryBuilder()
          ->select('a.id, a.title, a.publishedAt')
          ->from('EtuModuleTechnographBundle:Article', 'a');
      if(!$this->isGranted('ROLE_TECHNOGRAPH_ADMIN')) {
        $query->andWhere('a.publishedAt is not NULL');
      }
      /** @var $articles Article[] */
      $articles = $query
          ->orderBy('a.createdAt', 'DESC')
          ->getQuery()
          ->getResult();
      return ['articles' => $articles];
  }
    /**
     * @Route("/articles/{id}", name="technograph_article_view")
     * @Template()
     * 
     * @param mixed $id
     */
    public function viewAction($id)
    {
        $this->denyAccessUnlessGranted('ROLE_TECHNOGRAPH_READ');


        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQueryBuilder()
            ->select('a, au')
            ->from('EtuModuleTechnographBundle:Article', 'a')
            ->leftJoin('a.author', 'au')
            ->where('a.id = :id')
            ->setParameter('id', $id);
        if(!$this->isGranted('ROLE_TECHNOGRAPH_ADMIN')) {
          $query->andWhere('a.publishedAt is not NULL');
        }
        $article = $query
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
        if(!$article){
            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'error',
                'message' => 'technograph.main.article.notFound',
            ]);
            return $this->redirect($this->generateUrl('technograph_index'));
        }
        return ['article' => $article];
    }

    /**
     * @Route(
     *      "/articles/{id}/edit",
     *      defaults={"id" = "0"},
     *      name="technograph_article_edit"
     * )
     * @Template()
     *
     * @param mixed $id
     */
    public function articleAction($id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_TECHNOGRAPH_ADMIN');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        /** @var $unpublishedArticles Article[] */
        $unpublishedArticles = $em->createQueryBuilder()
            ->select('a')
            ->from('EtuModuleTechnographBundle:Article', 'a')
            ->where('a.publishedAt is NULL')
            ->andWhere('a.id != :id')
            ->setParameter('id', $id)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->setMaxResults(10)
            ->getResult();

        $available = [];
        $future = [];
        if($id != '0') {
            $future['new'] = 'new';
        }
        /** @var $article Article */
        $article = $em->createQueryBuilder()
            ->select('a')
            ->from('EtuModuleTechnographBundle:Article', 'a')
            ->where('a.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
        $day = new \DateTime();

        foreach ($unpublishedArticles as $unpublishedArticle) {
            $available[$unpublishedArticle->getId()] = ['id' => $unpublishedArticle->getId(), 'name' => $unpublishedArticle->getTitle()];
        }

        $available = array_merge($future, $available);

        if (!$article) {
            $article = new Article($this->getUser());
        }

        $form = $this->createFormBuilder($article)
            ->add('title', TextType::class, ['required' => true, 'label' => 'technograph.main.article.labels.title', 'attr' => ['maxlength' => 100]])
            ->add('body', EditorEmailType::class, ['required' => true, 'label' => 'technograph.main.article.labels.body'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {


            if ($request->request->has('_publish')) {
                $article->setPublishedAt(new \DateTime());
                $this->get('session')->getFlashBag()->set('message', [
                    'type' => 'success',
                    'message' => 'technograph.main.article.confirmPublish',
                ]);
            }
            elseif ($request->request->has('_unpublish')) {
                $article->setPublishedAt(null);
                $this->get('session')->getFlashBag()->set('message', [
                    'type' => 'warning',
                    'message' => 'technograph.main.article.confirmUnpublish',
                ]);
            }
            else {
                if($article->getPublishedAt() != null) {
                    $this->get('session')->getFlashBag()->set('message', [
                        'type' => 'success',
                        'message' => 'technograph.main.article.confirm',
                    ]);
                } else {
                  $this->get('session')->getFlashBag()->set('message', [
                      'type' => 'warning',
                      'message' => 'technograph.main.article.confirmWarning',
                  ]);
                }
            }
            $em->persist($article);
            $em->flush();

            if ($request->request->has('_saveandsee')) {
                return $this->redirect($this->generateUrl('technograph_article_view', [
                    'id' => $article->getId(),
                ]));
            }

            return $this->redirect($this->generateUrl('technograph_article_edit', [
                    'id' => $article->getId(),
                ]));
        }
        return [
            'form' => $form->createView(),
            'article' => $article,
            'available' => $available,
            'currentDay' => $day,
            'day' => $day->format('d-m-Y'),
        ];
    }

    /**
     * @Route(
     *      "/articles/{id}/remove",
     *      name="technograph_article_remove"
     * )
     * @Template()
     *
     * @param mixed $id
     */
    public function removeAction($id)
    {
        $this->denyAccessUnlessGranted('ROLE_TECHNOGRAPH_ADMIN');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        /** @var $article Article */
        $article = $em->createQueryBuilder()
            ->select('a')
            ->from('EtuModuleTechnographBundle:Article', 'a')
            ->where('a.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();

        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        $em->remove($article);
        $em->flush();

        return $this->redirect($this->generateUrl('technograph_index'));
    }
}
