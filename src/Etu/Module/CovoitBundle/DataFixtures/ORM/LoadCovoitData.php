<?php

namespace Etu\Module\BugsBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Etu\Module\CovoitBundle\Entity\Covoit;
use Faker\Factory;

class LoadCovoitData extends AbstractFixture implements OrderedFixtureInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getOrder()
	{
		return 5;
	}

	/**
	 * {@inheritDoc}
	 */
	public function load(ObjectManager $manager)
	{
        $faker = Factory::create('fr_FR');

		$covoit = new Covoit();
        $covoit->setAuthor($this->getReference('user_user'));
        $covoit->setStartCity($this->getReference('city_troyes'));
        $covoit->setEndCity($this->getReference('city_paris'));
        $covoit->setStartAdress($faker->text(150));
        $covoit->setEndAdress($faker->text(150));
        $covoit->setStartHour('16:00');
        $covoit->setEndHour('19:00');
        $covoit->setCapacity(rand(2, 5));
        $covoit->setDate($faker->dateTimeThisYear);
        $covoit->setPhoneNumber($faker->phoneNumber);
        $covoit->setPrice(rand(15, 35));
        $covoit->setNotes($faker->text(250));

		$manager->persist($covoit);
		$manager->flush();
	}
}