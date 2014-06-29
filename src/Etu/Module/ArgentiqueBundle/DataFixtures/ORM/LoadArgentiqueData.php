<?php

namespace Etu\Module\ArgentiqueBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Etu\Module\ArgentiqueBundle\Entity\Photo;
use Etu\Module\ArgentiqueBundle\Entity\PhotoSet;

class LoadArgentiqueData extends AbstractFixture implements OrderedFixtureInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getOrder()
	{
		return 6;
	}

	/**
	 * {@inheritDoc}
	 */
	public function load(ObjectManager $manager)
	{
		$set = new PhotoSet('42');
        $set->setTitle('TestSet');
        $set->setIcon('test_set');

        $photo = new Photo('43');
        $photo->setPhotoSet($set);
        $photo->setFile('test_photo');
        $photo->setIcon('test_photo_icon');
        $photo->setTitle('TestPhoto');
        $photo->setReady(true);

        $set->addPhoto($photo);

        $manager->persist($photo);
        $manager->persist($set);
		$manager->flush();
	}
}