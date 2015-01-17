<?php

namespace Etu\Core\CoreBundle\Home;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Entity\Subscription;
use Etu\Core\CoreBundle\Framework\Cache\Apc;
use Etu\Core\CoreBundle\Framework\Definition\Module;
use Etu\Core\CoreBundle\Framework\Twig\GlobalAccessorObject;
use Etu\Core\UserBundle\Entity\Course;
use Etu\Core\UserBundle\Entity\User;
use Etu\Module\ArgentiqueBundle\Entity\Photo;
use Etu\Module\ArgentiqueBundle\EtuModuleArgentiqueBundle;
use Etu\Module\EventsBundle\Entity\Event;
use Etu\Module\UVBundle\Entity\Review;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Stopwatch\Stopwatch;

class HomeBuilder
{
    /**
     * @var EntityManager
     */
    protected $manager;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var GlobalAccessorObject
     */
    protected $globalAccessorObject;

    /**
     * @var Stopwatch
     */
    protected $stopwatch;

    /**
     * @param EntityManager $manager
     * @param SecurityContext $context
     * @param GlobalAccessorObject $globalAccessorObject
     * @param Stopwatch $stopwatch
     */
    public function __construct(EntityManager $manager,
                                SecurityContext $context,
                                GlobalAccessorObject $globalAccessorObject,
                                Stopwatch $stopwatch = null)
    {
        $this->manager = $manager;
        $this->user = $context->getToken()->getUser();
        $this->globalAccessorObject = $globalAccessorObject;
        $this->stopwatch = $stopwatch;
    }

    /**
     * @return Course[]
     */
    public function getNextCourses()
    {
        if ($this->stopwatch) {
            $this->stopwatch->start('block_next_courses', 'home_blocks');
        }

        /** @var Course[] $result */
        $result = $this->manager
            ->getRepository('EtuUserBundle:Course')
            ->getUserNextCourses($this->user);

        if ($this->stopwatch) {
            $this->stopwatch->stop('block_next_courses');
        }

        return $result;
    }

    /**
     * @param Module[] $enabledModules
     * @return \Etu\Core\CoreBundle\Entity\Notification[]
     */
    public function getNotifications($enabledModules)
    {
        if ($this->stopwatch) {
            $this->stopwatch->start('block_notifications', 'home_blocks');
        }

        $query = $this->manager->createQueryBuilder()
            ->select('n')
            ->from('EtuCoreBundle:Notification', 'n')
            ->where('n.authorId != :userId')
            ->setParameter('userId', $this->user->getId())
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults(10);

        /** @var $subscriptions Subscription[] */
        $subscriptions = $this->globalAccessorObject->get('notifs')->get('subscriptions');

        $subscriptionsWhere = [];

        /** @var $notifications Notification[] */
        $notifications = [];

        if (! empty($subscriptions)) {
            if ($this->stopwatch) {
                $this->stopwatch->start('block_notifications_filters', 'home_blocks');
            }

            if (Apc::enabled() && Apc::has('etuutt_home_subscription_' . $this->user->getId())) {
                $subscriptionsWhere = Apc::fetch('etuutt_home_subscription_' . $this->user->getId());
            } else {
                foreach ($subscriptions as $key => $subscription) {
                    $subscriptionsWhere[] =   '(n.entityType = \'' . $subscription->getEntityType() . '\'
                                                AND n.entityId = ' . intval($subscription->getEntityId()) . ')';
                }

                $subscriptionsWhere = implode(' OR ', $subscriptionsWhere);

                if (Apc::enabled()) {
                    Apc::store('etuutt_home_subscription_' . $this->user->getId(), $subscriptionsWhere, 1200);
                }
            }

            if (! empty($subscriptionsWhere)) {
                $query = $query->andWhere($subscriptionsWhere);
            }

            /*
             * Modules
             */
            $modulesWhere = array('n.module = \'core\'', 'n.module = \'user\'');

            foreach ($enabledModules as $module) {
                $identifier = $module->getIdentifier();
                $modulesWhere[] = 'n.module = :module_'.$identifier;

                $query->setParameter('module_'.$identifier, $identifier);
            }

            if (! empty($modulesWhere)) {
                $query = $query->andWhere(implode(' OR ', $modulesWhere));
            }

            if ($this->stopwatch) {
                $this->stopwatch->stop('block_notifications_filters');

                $this->stopwatch->start('block_notifications_query', 'home_blocks');
            }

            $query = $query->getQuery();
            $query->useResultCache(true, 1200);

            $notifications = $query->getResult();

            if ($this->stopwatch) {
                $this->stopwatch->stop('block_notifications_query');
            }
        }

        if ($this->stopwatch) {
            $this->stopwatch->stop('block_notifications');
        }

        return $notifications;
    }

    /**
     * @return Review[]
     */
    public function getUvReviews()
    {
        if ($this->stopwatch) {
            $this->stopwatch->start('block_uvs_reviews', 'home_blocks');
        }

        $query = $this->manager
            ->getRepository('EtuModuleUVBundle:Review')
            ->createQbReviewOf($this->user->getUvsList())
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults(5)
            ->getQuery();

        $query->useResultCache(true, 1200);

        $result = $query->getResult();

        if ($this->stopwatch) {
            $this->stopwatch->stop('block_uvs_reviews');
        }

        return $result;
    }

    /**
     * @return Event[]
     */
    public function getEvents()
    {
        if ($this->stopwatch) {
            $this->stopwatch->start('block_events', 'home_blocks');
        }

        $query = $this->manager->createQueryBuilder()
            ->select('e, o')
            ->from('EtuModuleEventsBundle:Event', 'e')
            ->leftJoin('e.orga', 'o')
            ->where('e.begin >= :begin')
            ->setParameter('begin', new \DateTime())
            ->orderBy('e.begin', 'ASC')
            ->addOrderBy('e.end', 'ASC')
            ->setMaxResults(3)
            ->getQuery();

        $query->useResultCache(true, 1200);

        $result = $query->getResult();

        if ($this->stopwatch) {
            $this->stopwatch->stop('block_events');
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getPhotos()
    {
        if ($this->stopwatch) {
            $this->stopwatch->start('block_photos', 'home_blocks');
        }

        /** @var string $root */
        $root = EtuModuleArgentiqueBundle::getPhotosRoot();

        if (! file_exists($root)) {
            return [];
        }

        /*
         * Select most recent collection, find most recent set in it and get 5 random images from this set
         */

        // Collection
        $collections = glob($root . '/*', GLOB_ONLYDIR);
        $collectionsRegistry = [];

        foreach ($collections as $collection) {
            $collectionsRegistry[$collection] = filemtime($collection);
        }

        arsort($collectionsRegistry);
        $collectionsRegistry = array_flip($collectionsRegistry);

        $collection = reset($collectionsRegistry);


        // Set
        $sets = glob($collection . '/*', GLOB_ONLYDIR);
        $setsRegistry = [];

        foreach ($sets as $set) {
            $setsRegistry[$set] = filemtime($set);
        }

        arsort($setsRegistry);
        $setsRegistry = array_flip($setsRegistry);

        $set = reset($setsRegistry);


        // Random
        /** @var \SplFileInfo[] $iterator */
        $iterator = new \DirectoryIterator($set);

        $i = 0;

        /** @var array $photos */
        $photos = [];

        foreach ($iterator as $file) {
            if ($file->getExtension() == 'jpg' || $file->getExtension() == 'jpeg') {
                $photos[] = [
                    'extension' => $file->getExtension(),
                    'pathname' => str_replace($root . '/', '', $file->getPathname()),
                    'basename' => $file->getBasename(),
                    'filename' => $file->getBasename('.' . $file->getExtension()),
                ];

                $i++;

                if ($i == 10) {
                    break;
                }
            }
        }

        if ($this->stopwatch) {
            $this->stopwatch->stop('block_photos');
        }

        return $photos;
    }

    /**
     * @return User[]
     */
    public function getBirthdays()
    {
        if ($this->stopwatch) {
            $this->stopwatch->start('block_birthdays', 'home_blocks');
        }

        $query = $this->manager->createQueryBuilder()
            ->select('u, m, o')
            ->from('EtuUserBundle:User', 'u')
            ->leftJoin('u.memberships', 'm')
            ->leftJoin('m.organization', 'o')
            ->where('DAY(u.birthday) = DAY(CURRENT_TIMESTAMP())')
            ->andWhere('MONTH(u.birthday) = MONTH(CURRENT_TIMESTAMP())')
            ->andWhere('u.birthday IS NOT NULL')
            ->andWhere('u.birthdayPrivacy = :privacy')
            ->setParameter('privacy', User::PRIVACY_PUBLIC)
            ->andWhere('u.id != :me')
            ->setParameter('me', $this->user->getId())
            ->getQuery();

        $query->useResultCache(true, 3600);

        /** @var User[] $users */
        $users = $query->getResult();

        // Find more interesting birthdays : same promotion (SRT4), same branch (SRT), others
        $usersWeights = [];

        foreach ($users as $key => $user) {
            $usersWeights[$key] = 0;

            if ($user->getBranch() == $this->user->getBranch()) {
                $usersWeights[$key]++;
            }

            if ($user->getNiveau() == $this->user->getNiveau()) {
                $usersWeights[$key]++;
            }
        }

        array_multisort(
            $usersWeights, SORT_DESC, SORT_NUMERIC,
            $users
        );

        $result = array_slice($users, 0, 3);

        if ($this->stopwatch) {
            $this->stopwatch->stop('block_birthdays');
        }

        return $result;
    }
}
