<?php

namespace Etu\Core\CoreBundle\Home;

use Etu\Core\CoreBundle\Framework\Module\ModulesManager;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Stopwatch\Stopwatch;

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
     * Stopwatch instance.
     *
     * If provided, the profiler will be ran on the HomerRenderer to analyse blocks time rendering.
     *
     * @var Stopwatch
     */
    public $stopwatch;

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
        if ($this->stopwatch) {
            $this->stopwatch->start('courses_block', 'home_blocks');
        }

        $block = [
            'template' => 'EtuCoreBundle:Main/index_blocks:courses.html.twig',
            'context' => [
                'nextCourses' => $this->builder->getNextCourses(),
            ]
        ];

        if ($this->stopwatch) {
            $this->stopwatch->stop('courses_block');
        }

        return $block;
    }

    public function createTrombiBlock()
    {
        if ($this->stopwatch) {
            $this->stopwatch->start('trombi_block', 'home_blocks');
        }

        $trombiFrom = $this->formFactory->createBuilder()
            ->add('fullName', 'text', array('required' => false))
            ->add('studentId', 'hidden', array('required' => false))
            ->add('phoneNumber', 'hidden', array('required' => false))
            ->add('uvs', 'hidden', array('required' => false))
            ->add('branch', 'hidden', array('required' => false))
            ->add('niveau', 'hidden', array('required' => false))
            ->add('personnalMail', 'hidden', array('required' => false))
            ->getForm();

        $block = [
            'template' => 'EtuCoreBundle:Main/index_blocks:trombi.html.twig',
            'context' => [
                'trombiForm' => $trombiFrom->createView(),
            ]
        ];

        if ($this->stopwatch) {
            $this->stopwatch->stop('trombi_block');
        }

        return $block;
    }

    public function createNotificationsBlock()
    {
        if ($this->stopwatch) {
            $this->stopwatch->start('notifs_block', 'home_blocks');
        }

        $block = [
            'template' => 'EtuCoreBundle:Main/index_blocks:notifications.html.twig',
            'context' => [
                'notifications' => $this->builder->getNotifications($this->modulesManager->getEnabledModules()),
            ]
        ];

        if ($this->stopwatch) {
            $this->stopwatch->stop('notifs_block');
        }

        return $block;
    }

    public function createEventsBlock()
    {
        if ($this->modulesManager->getModuleByIdentifier('events')->isEnabled()) {
            if ($this->stopwatch) {
                $this->stopwatch->start('events_block', 'home_blocks');
            }

            $events = $this->builder->getEvents();

            if (count($events) > 0) {
                $block = [
                    'template' => 'EtuCoreBundle:Main/index_blocks:events.html.twig',
                    'context' => [
                        'events' => $events,
                    ]
                ];
            }

            if ($this->stopwatch) {
                $this->stopwatch->stop('events_block');
            }
        }

        return (isset($block)) ? $block : false;
    }

    public function createReviewsBlock()
    {
        if ($this->modulesManager->getModuleByIdentifier('uv')->isEnabled()) {
            if ($this->stopwatch) {
                $this->stopwatch->start('uvs_block', 'home_blocks');
            }

            $reviews = $this->builder->getUvReviews();

            if (count($reviews) > 0) {
                $block = [
                    'template' => 'EtuCoreBundle:Main/index_blocks:reviews.html.twig',
                    'context' => [
                        'reviews' => $reviews,
                    ]
                ];
            }

            if ($this->stopwatch) {
                $this->stopwatch->stop('uvs_block');
            }
        }

        return (isset($block)) ? $block : false;
    }

    public function createPhotosBlock()
    {
        if ($this->modulesManager->getModuleByIdentifier('argentique')->isEnabled()) {
            if ($this->stopwatch) {
                $this->stopwatch->start('photos_block', 'home_blocks');
            }

            $photos = $this->builder->getPhotos();

            if (count($photos) > 0) {
                $block = [
                    'template' => 'EtuCoreBundle:Main/index_blocks:photos.html.twig',
                    'context' => [
                        'photos' => $photos,
                    ]
                ];
            }

            if ($this->stopwatch) {
                $this->stopwatch->stop('photos_block');
            }
        }

        return (isset($block)) ? $block : false;
    }

    public function createBirthdaysBlock()
    {
        if ($this->stopwatch) {
            $this->stopwatch->start('birthdays_block', 'home_blocks');
        }

        $birthdays = $this->builder->getBirthdays();

        if (count($birthdays) > 0) {
            $block = [
                'template' => 'EtuCoreBundle:Main/index_blocks:birthdays.html.twig',
                'context' => [
                    'birthdays' => $birthdays,
                ]
            ];
        }

        if ($this->stopwatch) {
            $this->stopwatch->stop('birthdays_block');
        }

        return (isset($block)) ? $block : false;
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