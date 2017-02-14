<?php

namespace Etu\Core\CoreBundle\Home;

use Etu\Core\CoreBundle\Framework\Module\ModulesManager;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
     * @param HomeBuilder    $builder
     * @param ModulesManager $modulesManager
     * @param FormFactory    $formFactory
     */
    public function __construct(HomeBuilder $builder,
                                ModulesManager $modulesManager,
                                FormFactory $formFactory)
    {
        $this->builder = $builder;
        $this->modulesManager = $modulesManager;
        $this->formFactory = $formFactory;
    }

    public function createCoursesBlock()
    {
        $block = [
            'template' => 'EtuCoreBundle:Main/index_blocks:courses.html.twig',
            'context' => [
                'nextCourses' => $this->builder->getNextCourses(),
                'UVs' => $this->builder->getUVs(),
            ],
            'role' => 'ROLE_CORE_SCHEDULE_OWN',
        ];

        return $block;
    }

    public function createTrombiBlock()
    {
        $trombiFrom = $this->formFactory->createBuilder()
            ->add('fullName', TextType::class, ['required' => false])
            ->add('studentId', HiddenType::class, ['required' => false])
            ->add('phoneNumber', HiddenType::class, ['required' => false])
            ->add('uvs', HiddenType::class, ['required' => false])
            ->add('branch', HiddenType::class, ['required' => false])
            ->add('niveau', HiddenType::class, ['required' => false])
            ->add('personnalMail', HiddenType::class, ['required' => false])
            ->getForm();

        $block = [
            'template' => 'EtuCoreBundle:Main/index_blocks:trombi.html.twig',
            'context' => [
                'trombiForm' => $trombiFrom->createView(),
            ],
            'role' => 'ROLE_TROMBI',
        ];

        return $block;
    }

    public function createNotificationsBlock()
    {
        $block = [
            'template' => 'EtuCoreBundle:Main/index_blocks:notifications.html.twig',
            'context' => [
                'notifications' => $this->builder->getNotifications($this->modulesManager->getEnabledModules()),
            ],
            'role' => 'ROLE_CORE_SUBSCRIBE',
        ];

        return $block;
    }

    public function createEventsBlock()
    {
        if ($this->modulesManager->getModuleByIdentifier('events')->isEnabled()) {
            $events = $this->builder->getEvents();

            if (count($events) > 0) {
                $block = [
                    'template' => 'EtuCoreBundle:Main/index_blocks:events.html.twig',
                    'context' => [
                        'events' => $events,
                    ],
                ];
            }
        }

        return (isset($block)) ? $block : false;
    }

    public function createReviewsBlock()
    {
        if ($this->modulesManager->getModuleByIdentifier('uv')->isEnabled()) {
            $reviews = $this->builder->getUvReviews();

            if (count($reviews) > 0) {
                $block = [
                    'template' => 'EtuCoreBundle:Main/index_blocks:reviews.html.twig',
                    'context' => [
                        'reviews' => $reviews,
                    ],
                    'role' => 'ROLE_UV_REVIEW',
                ];
            }
        }

        return (isset($block)) ? $block : false;
    }

    public function createPhotosBlock()
    {
        if ($this->modulesManager->getModuleByIdentifier('argentique')->isEnabled()) {
            $photos = $this->builder->getPhotos();

            if (isset($photos['list']) && count($photos['list']) > 0) {
                $block = [
                    'template' => 'EtuCoreBundle:Main/index_blocks:photos.html.twig',
                    'context' => [
                        'photos' => $photos['list'],
                        'collection' => $photos['collection'],
                        'set' => $photos['set'],
                    ],
                    'role' => 'ROLE_ARGENTIQUE_READ',
                ];
            }
        }

        return (isset($block)) ? $block : false;
    }

    public function createBirthdaysBlock()
    {
        $birthdays = $this->builder->getBirthdays();

        if (count($birthdays) > 0) {
            $block = [
                'template' => 'EtuCoreBundle:Main/index_blocks:birthdays.html.twig',
                'context' => [
                    'birthdays' => $birthdays,
                ],
                'role' => 'ROLE_CORE_PROFIL',
            ];
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

        $columns[1][] = $this->createNotificationsBlock();

        return $columns;
    }
}
