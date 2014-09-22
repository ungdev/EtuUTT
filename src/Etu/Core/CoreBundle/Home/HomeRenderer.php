<?php

namespace Etu\Core\CoreBundle\Home;

use Etu\Core\CoreBundle\Framework\Module\ModulesManager;
use Symfony\Component\Form\FormFactory;

class HomeRenderer
{
    /**
     * @var HomeBuilder
     */
    protected $builder;

    /**
     * @var ModulesManager
     */
    protected $modulesManager;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var array
     */
    protected $blocks;

    /**
     * @param HomeBuilder $builder
     * @param ModulesManager $modulesManager
     * @param FormFactory $formFactory
     */
    public function __construct(HomeBuilder $builder, ModulesManager $modulesManager, FormFactory $formFactory)
    {
        $this->builder = $builder;
        $this->modulesManager = $modulesManager;
        $this->formFactory = $formFactory;
    }

    public function createCoursesBlock()
    {
        $this->blocks[] = [
            'template' => 'EtuCoreBundle:Main/index_blocks:courses.html.twig',
            'context' => [
                'nextCourses' => $this->builder->getNextCourses(),
            ]
        ];
    }

    public function createTrombiBlock()
    {
        $trombiFrom = $this->formFactory->createBuilder()
            ->add('fullName', 'text', array('required' => false))
            ->add('studentId', 'hidden', array('required' => false))
            ->add('phoneNumber', 'hidden', array('required' => false))
            ->add('uvs', 'hidden', array('required' => false))
            ->add('branch', 'hidden', array('required' => false))
            ->add('niveau', 'hidden', array('required' => false))
            ->add('personnalMail', 'hidden', array('required' => false))
            ->getForm();

        $this->blocks[] = [
            'template' => 'EtuCoreBundle:Main/index_blocks:trombi.html.twig',
            'context' => [
                'trombiForm' => $trombiFrom->createView(),
            ]
        ];
    }

    public function createEventsBlock()
    {
        if ($this->modulesManager->getModuleByIdentifier('events')->isEnabled()) {
            $events = $this->builder->getEvents();

            if (count($events) > 0) {
                $this->blocks[] = [
                    'template' => 'EtuCoreBundle:Main/index_blocks:events.html.twig',
                    'context' => [
                        'events' => $events,
                    ]
                ];
            }
        }
    }

    public function createReviewsBlock()
    {
        if ($this->modulesManager->getModuleByIdentifier('uv')->isEnabled()) {
            $reviews = $this->builder->getUvReviews();

            if (count($reviews) > 0) {
                $this->blocks[] = [
                    'template' => 'EtuCoreBundle:Main/index_blocks:reviews.html.twig',
                    'context' => [
                        'reviews' => $reviews,
                    ]
                ];
            }
        }
    }

    /**
     * @return array
     */
    public function renderBlocks()
    {
        $this->createCoursesBlock();
        $this->createTrombiBlock();
        $this->createEventsBlock();
        $this->createReviewsBlock();

        return $this->blocks;
    }
}