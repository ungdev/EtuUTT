<?php

namespace Etu\Module\DaymailBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Etu\Module\DaymailBundle\Entity\DaymailPart;

class LoadDaymailData extends AbstractFixture implements OrderedFixtureInterface
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
		$tomorrow = new \DateTime();
		$tomorrow->add(new \DateInterval('P1D'));

		$daymail = new DaymailPart($this->getReference('user_orga'), $tomorrow);
		$daymail->setTitle('Daymail test');
		$daymail->setBody('Daymail test');

		$manager->persist($daymail);
		$manager->flush();
	}
}