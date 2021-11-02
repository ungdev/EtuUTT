<?php

namespace Etu\Core\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Etu\Core\CoreBundle\Entity\Page;

class LoadPagesData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 2;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $pageToDelete = new Page();
        $pageToDelete->setSlug('page-to-delete');
        $pageToDelete->setTitle('Page to delete');
        $pageToDelete->setContent('Data-fixture for functionnal test (to delete during tests).');

        $manager->persist($pageToDelete);

        $datas = [
            'developpeurs' => 'Développeurs',
            'nous-aider' => 'Nous aider',
            'mentions-legales' => 'Mentions légales',
            'l-equipe' => 'L\'équipe',
        ];

        foreach ($datas as $slug => $title) {
            $page = new Page();
            $page->setSlug($slug);
            $page->setTitle($title);
            $page->setContent('Data-fixture for functionnal test.');

            $manager->persist($page);
        }

        $manager->flush();
    }
}
