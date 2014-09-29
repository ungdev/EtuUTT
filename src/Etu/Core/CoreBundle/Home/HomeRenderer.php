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
        return [
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

        return [
            'template' => 'EtuCoreBundle:Main/index_blocks:trombi.html.twig',
            'context' => [
                'trombiForm' => $trombiFrom->createView(),
            ]
        ];
    }

    public function createNotificationsBlock()
    {
        return [
            'template' => 'EtuCoreBundle:Main/index_blocks:notifications.html.twig',
            'context' => [
                'notifications' => $this->builder->getNotifications($this->modulesManager->getEnabledModules()),
            ]
        ];
    }

    public function createEventsBlock()
    {
        if ($this->modulesManager->getModuleByIdentifier('events')->isEnabled()) {
            $events = $this->builder->getEvents();

            if (count($events) > 0) {
                return [
                    'template' => 'EtuCoreBundle:Main/index_blocks:events.html.twig',
                    'context' => [
                        'events' => $events,
                    ]
                ];
            }
        }

        return false;
    }

    public function createReviewsBlock()
    {
        if ($this->modulesManager->getModuleByIdentifier('uv')->isEnabled()) {
            $reviews = $this->builder->getUvReviews();

            if (count($reviews) > 0) {
                return [
                    'template' => 'EtuCoreBundle:Main/index_blocks:reviews.html.twig',
                    'context' => [
                        'reviews' => $reviews,
                    ]
                ];
            }
        }

        return false;
    }

    public function createPhotosBlock()
    {
        if ($this->modulesManager->getModuleByIdentifier('argentique')->isEnabled()) {
            $photos = $this->builder->getPhotos();

            if (count($photos) > 0) {
                return [
                    'template' => 'EtuCoreBundle:Main/index_blocks:photos.html.twig',
                    'context' => [
                        'photos' => $photos,
                    ]
                ];
            }
        }

        return false;
    }

    public function createBirthdaysBlock()
    {
        $birthdays = $this->builder->getBirthdays();

        if (count($birthdays) > 0) {
            return [
                'template' => 'EtuCoreBundle:Main/index_blocks:birthdays.html.twig',
                'context' => [
                    'birthdays' => $birthdays,
                ]
            ];
        }

        return false;
    }

    /**
     * @return array
     */
    public function renderBlocks()
    {
        $columns = [];

        $columns[0][] = $this->createCoursesBlock();
        $columns[0][] = $this->createTrombiBlock();

        if ($eventsBlock = $this->createEventsBlock()) {
            $columns[0][] = $eventsBlock;
        }

        if ($photosBlock = $this->createPhotosBlock()) {
            $columns[0][] = $photosBlock;
        }

        if ($birthdays = $this->createBirthdaysBlock()) {
            $columns[0][] = $birthdays;
        }

        if ($reviewsBlock = $this->createReviewsBlock()) {
            $columns[0][] = $reviewsBlock;
        }

        $columns[1][] = $this->createNotificationsBlock();

        return $columns;
    }
}