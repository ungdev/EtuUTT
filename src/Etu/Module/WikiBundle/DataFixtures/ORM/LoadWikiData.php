<?php

namespace Etu\Module\WikiBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Etu\Core\UserBundle\Entity\User;
use Etu\Module\WikiBundle\Entity\Category;
use Etu\Module\WikiBundle\Entity\Page;
use Etu\Module\WikiBundle\Entity\PageRevision;

class LoadWikiData extends AbstractFixture implements OrderedFixtureInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getOrder()
	{
		return 4;
	}

	/**
	 * {@inheritDoc}
	 */
	public function load(ObjectManager $manager)
	{
		// Categories
		$category = new Category();
		$category->setOrga($this->getReference('user_orga'));
		$category->setTitle('Category');
		$category->setDepth(0);

		$subCategory = new Category();
		$subCategory->setOrga($this->getReference('user_orga'));
		$subCategory->setTitle('Sub-category');
		$subCategory->setDepth(1);
		$subCategory->setParent($category);

		$manager->persist($category);
		$manager->persist($subCategory);

		$manager->flush();


		// Home
		$home = new Page();
		$home->setTitle('Accueil')
			->setIsHome(true)
			->setLevelToView(Page::LEVEL_CONNECTED)
			->setLevelToEdit(Page::LEVEL_ADMIN)
			->setLevelToEditPermissions(Page::LEVEL_ADMIN);

		$homeRevision = new PageRevision();
		$homeRevision->setUser($this->getReference('user_user'))
			->setComment('Revision comment')
			->setBody('Revision body');

		$home->setRevision($homeRevision);

		$manager->persist($home);
		$manager->persist($homeRevision);

		$manager->flush();

		$homeRevisionOld = new PageRevision();
		$homeRevisionOld->setUser($this->getReference('user_user'))
			->setComment('Revision comment')
			->setBody('Revision body')
			->setDate(\DateTime::createFromFormat('U', time() - 3600))
			->setPageId($home->getId());

		$homeRevision->setPageId($home->getId());

		$manager->persist($homeRevision);
		$manager->persist($homeRevisionOld);

		$manager->flush();

		// Orga home
		$page = new Page();
		$page->setTitle('Page')
			->setOrga($this->getReference('user_orga'))
			->setCategory($subCategory)
			->setIsHome(true);

		$pageRevision = new PageRevision();
		$pageRevision->setUser($this->getReference('user_user'))
			->setComment('Revision comment')
			->setBody('Revision body');

		$page->setRevision($pageRevision);

		$manager->persist($page);
		$manager->persist($pageRevision);

		$manager->flush();

		$pageRevisionOld = new PageRevision();
		$pageRevisionOld->setUser($this->getReference('user_user'))
			->setComment('Revision comment')
			->setBody('Revision body')
			->setDate(\DateTime::createFromFormat('U', time() - 3600))
			->setPageId($page->getId());

		$pageRevision->setPageId($home->getId());

		$manager->persist($pageRevision);
		$manager->persist($pageRevisionOld);

		$manager->flush();
	}
}