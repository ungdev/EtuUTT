<?php

namespace Etu\Module\BugsBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Etu\Module\BugsBundle\Entity\Comment;
use Etu\Module\BugsBundle\Entity\Issue;

class LoadBugsData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 3;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $bug = new Issue();
        $bug->setUser($this->getReference('user_user'));
        $bug->setTitle('Issue title');
        $bug->setBody('Issue body');
        $bug->setCriticality(Issue::CRITICALITY_SECURITY);
        $bug->setUpdatedAt(new \DateTime());

        $manager->persist($bug);

        $comment = new Comment();
        $comment->setUser($this->getReference('user_admin'));
        $comment->setIssue($bug);
        $comment->setBody('Comment body');

        $manager->persist($comment);

        $manager->flush();
    }
}
