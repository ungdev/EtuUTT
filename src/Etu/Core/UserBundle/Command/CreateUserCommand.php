<?php

namespace Etu\Core\UserBundle\Command;

use Etu\Core\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManager;

class CreateUserCommand extends ContainerAwareCommand
{
	/**
	 * Configure the command
	 */
	protected function configure()
	{
		$this
			->setName('etu:users:create')
            ->addOption('login', 'lo', InputOption::VALUE_REQUIRED, 'Login')
            ->addOption('firstName', 'fn', InputOption::VALUE_REQUIRED, 'First name')
            ->addOption('lastName', 'ln', InputOption::VALUE_REQUIRED, 'Last name')
            ->addOption('password', 'pa', InputOption::VALUE_REQUIRED, 'Password')
            ->addOption('email', 'em', InputOption::VALUE_REQUIRED, 'Public e-mail')
			->setDescription('Create a user')
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

        if ($input->hasOption('login')) {
            $login = $input->getOption('login');
        } else {
            $login = $dialog->ask($output, 'Login: ');
        }

        if ($input->hasOption('firstName')) {
            $firstName = $input->getOption('firstName');
        } else {
            $firstName = $dialog->ask($output, 'First name: ');
        }

        if ($input->hasOption('lastName')) {
            $lastName = $input->getOption('lastName');
        } else {
            $lastName = $dialog->ask($output, 'Last name: ');
        }

        if ($input->hasOption('password')) {
            $password = $input->getOption('password');
        } else {
            $password = $dialog->ask($output, 'Password: ');
        }

        if ($input->hasOption('email')) {
            $email = $input->getOption('email');
        } else {
            $email = $dialog->ask($output, 'Public e-mail: ');
        }

		$user = new User();
		$user->setKeepActive(true);
        $user->setLogin($login);
		$user->setFirstName($firstName);
		$user->setLastName($lastName);
		$user->setFullName($user->getFirstName().' '.$user->getLastName());
		$user->setPassword($this->getContainer()->get('etu.user.crypting')->encrypt($password));
		$user->setMail($email);

		$em->persist($user);
		$em->flush();

		$output->writeln("\nDone.\n");
	}
}
