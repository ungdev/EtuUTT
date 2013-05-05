<?php

namespace Etu\Core\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Etu\Core\CoreBundle\Entity\Page;

class LoadPagesData implements FixtureInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function load(ObjectManager $manager)
	{
		$pageToDelete = new Page();
		$pageToDelete->setSlug('page-to-delete');
		$pageToDelete->setTitle('Page to delete');
		$pageToDelete->setContent('Data-fixture for functionnal test (to delete during tests).');

		$manager->persist($pageToDelete);

		$datas = array(
			'developpeurs' => 'Développeurs',
			'nous-aider' => 'Nous aider',
			'mentions-legales' => 'Mentions légales',
			'l-equipe' => 'L\'équipe',
		);

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