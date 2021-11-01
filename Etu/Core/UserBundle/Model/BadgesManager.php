<?php

namespace Etu\Core\UserBundle\Model;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Etu\Core\UserBundle\Entity\Badge;
use Etu\Core\UserBundle\Entity\User;
use Etu\Core\UserBundle\Entity\UserBadge;

class BadgesManager
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var EntityManager
     */
    protected static $doctrine;

    /**
     * @var Badge[]
     */
    protected static $badges = [];

    /**
     * @var UserBadge[]
     */
    protected static $usersBadges;

    /**
     * @var bool
     */
    protected static $initialized = false;

    /**
     * @var bool
     */
    protected static $initializedUsers = false;

    public function __construct(Registry $doctrine)
    {
        $this->em = $doctrine->getManager();
    }

    /**
     * Freeze badges from the database.
     */
    public function onKernelRequest()
    {
        self::$doctrine = $this->em;
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return Badge[][]
     */
    public static function findBadgesList()
    {
        if (!self::$initialized) {
            self::initialize();
        }

        $list = [];

        foreach (self::$badges as $badge) {
            $list[$badge->getSerie()][$badge->getLevel()] = $badge;
        }

        return $list;
    }

    /**
     * @param $serie
     * @param $level
     *
     * @throws \InvalidArgumentException
     *
     * @return Badge
     */
    public static function findBySerie($serie, $level = 1)
    {
        if (!self::$initialized) {
            self::initialize();
        }

        if (!isset(self::$badges[$serie.$level])) {
            throw new \InvalidArgumentException('Invalid badge reference');
        }

        return self::$badges[$serie.$level];
    }

    /**
     * @param $id
     *
     * @throws \InvalidArgumentException
     *
     * @return Badge
     */
    public static function findById($id)
    {
        if (!self::$initialized) {
            self::initialize();
        }

        foreach (self::$badges as $badge) {
            if ($badge->getId() == $id) {
                return $badge;
            }
        }
        throw new \InvalidArgumentException('Invalid badge reference');
    }

    /**
     * @param $serie
     * @param $level
     *
     * @return bool|UserBadge
     */
    public static function getUserBadge(User $user, $serie, $level)
    {
        if (!self::$initializedUsers) {
            self::initializeUsersBadges();
        }

        if (!isset(self::$usersBadges[$user->getId().$serie.$level])) {
            return false;
        }

        return self::$usersBadges[$user->getId().$serie.$level];
    }

    /**
     * @param     $serie
     * @param int $level
     *
     * @return bool
     */
    public static function userHasBadge(User $user, $serie, $level = 1)
    {
        if (!self::$initializedUsers) {
            self::initializeUsersBadges();
        }

        return isset(self::$usersBadges[$user->getId().$serie.$level]);
    }

    /**
     * @param string $badgeSerie
     * @param int    $badgeLevel
     *
     * @return User
     */
    public static function userAddBadge(User &$user, $badgeSerie, $badgeLevel = 1)
    {
        if (!self::$initialized) {
            self::initialize();
        }

        if (!self::$initializedUsers) {
            self::initializeUsersBadges();
        }

        $badge = self::findBySerie($badgeSerie, $badgeLevel);

        if (!self::userHasBadge($user, $badgeSerie, $badgeLevel)) {
            $user->addBadge(new UserBadge($badge, $user));
            self::userPersistBadges($user);
        }

        return $user;
    }

    /**
     * @param string $badgeSerie
     * @param int    $badgeLevel
     *
     * @return User
     */
    public static function userRemoveBadge(User &$user, $badgeSerie, $badgeLevel = 1)
    {
        if (!self::$initialized) {
            self::initialize();
        }

        if (!self::$initializedUsers) {
            self::initializeUsersBadges();
        }

        if (self::userHasBadge($user, $badgeSerie, $badgeLevel)) {
            self::$doctrine->remove(self::getUserBadge($user, $badgeSerie, $badgeLevel));
            self::$doctrine->flush();
        }

        return $user;
    }

    /**
     * @return User
     */
    private static function userPersistBadges(User $user)
    {
        foreach ($user->getBadges() as $userBadge) {
            self::$doctrine->persist($userBadge);
            self::$usersBadges[$userBadge->getUser()->getId().$userBadge->getBadge()->getSerie().$userBadge->getBadge()->getLevel()] = $userBadge;
        }

        self::$doctrine->flush();
    }

    /**
     * Initialize the badges list.
     */
    protected static function initialize()
    {
        /** @var Badge[] $badges */
        $badges = self::$doctrine->getRepository('EtuUserBundle:Badge')->findBy([], [
            'serie' => 'ASC',
        ]);

        foreach ($badges as $badge) {
            self::$badges[$badge->getSerie().$badge->getLevel()] = $badge;
        }

        self::$initialized = true;
    }

    /**
     * Initialize the badges list.
     */
    protected static function initializeUsersBadges()
    {
        /** @var UserBadge[] $usersBadges */
        $usersBadges = self::$doctrine->getRepository('EtuUserBundle:UserBadge')
            ->createQueryBuilder('ub')
            ->select('ub, u, b')
            ->join('ub.badge', 'b')
            ->join('ub.user', 'u')
            ->getQuery()
            ->getResult();

        foreach ($usersBadges as $userBadge) {
            if ($userBadge->getUser()) {
                self::$usersBadges[$userBadge->getUser()->getId().
                    $userBadge->getBadge()->getSerie().$userBadge->getBadge()->getLevel()] = $userBadge;
            }
        }

        self::$initializedUsers = true;
    }
}
