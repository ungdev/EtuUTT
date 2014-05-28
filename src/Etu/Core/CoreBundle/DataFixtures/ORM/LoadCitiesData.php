<?php

namespace Etu\Core\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Etu\Core\CoreBundle\Entity\City;
use Etu\Core\CoreBundle\Entity\Page;

class LoadCitiesData extends AbstractFixture implements OrderedFixtureInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getOrder()
	{
		return 2;
	}

	/**
	 * {@inheritDoc}
	 */
	public function load(ObjectManager $manager)
	{
        $troyes = new City();
        $troyes->setName('Troyes')
            ->setSlug('troyes')
            ->setPostalCodes('10000')
            ->setPopulation(60280)
            ->setLatitude(48.3)
            ->setLongitude(4.08333);

        $paris = new City();
        $paris->setName('Paris')
            ->setSlug('paris')
            ->setPostalCodes('75001|75002|75003|75004|75005|75006|75007|75008|75009|75010|75011|75012|75013|75014|75015|75016|75017|75018|75019|75020|75116')
            ->setPopulation(2243833)
            ->setLatitude(48.86)
            ->setLongitude(2.34445);

        $manager->persist($troyes);
        $manager->persist($paris);
        $manager->flush();

        $this->addReference('city_troyes', $troyes);
        $this->addReference('city_paris', $paris);
	}
}