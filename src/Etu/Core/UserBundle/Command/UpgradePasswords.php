<?php

namespace Etu\Core\UserBundle\Command;

use Etu\Core\UserBundle\Entity\User;
use Etu\Core\UserBundle\Command\Util\ProgressBar;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManager;

class KeepUserActiveCommand extends ContainerAwareCommand
{
	/**
	 * Configure the command
	 */
	protected function configure()
	{
		$this
			->setName('etu:users:upgrade-passwords')
			->setDescription('Upgrade all passwords of the db from custom encryption to bcrypt hash')
		;
	}

	/**
	 * @param \Symfony\Component\Console\Input\InputInterface $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 * @return void
	 * @throws \RuntimeException
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$dialog = $this->getHelperSet()->get('dialog');


		/** @var EntityManager $em */
		$em = $this->getContainer()->get('doctrine')->getManager();

		$users = $em->getRepository('EtuUserBundle:User')
			->createQueryBuilder('u')
			->where('u.password is not null')
			->Where('u.password<>\'\'')
			->getQuery()
			->execute();

			$output->writeln('Upgrading old database password format.');

			$bar = new ProgressBar('%fraction% [%bar%] %percent%', '=>', ' ', 80, count($users));
			$bar->update(0);
			$i = 1;
			$updated = 0;
			foreach ($users as $user) {

				if(strpos($user->getPassword(), '$2y$') !== 0) {
					$plain = $this->getContainer()->get('etu.user.crypting')->decrypt($user->getPassword());
					$hash = $this->getContainer()->get('security.password_encoder')->encodePassword($user, $plain);
					$user->setPassword($hash);
					$em->persist($user);
					$updated++;
				}
				$bar->update($i);
				$i++;
			}
			$bar->update(count($users));
			$em->flush();

		$output->writeln("\n\nOn ".count($users)." external users, ".$updated." had an old password format which has been upgraded.\n");
	}
}
