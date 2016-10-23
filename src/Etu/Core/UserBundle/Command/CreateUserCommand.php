<?php

namespace Etu\Core\UserBundle\Command;

use Etu\Core\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManager;

class CreateUserCommand extends ContainerAwareCommand
{
    /**
     * Configure the command.
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
            ->addOption('branch', 'br', InputOption::VALUE_REQUIRED, 'Branch')
            ->addOption('isStudent', 'st', InputOption::VALUE_REQUIRED, 'Is it a student (y/n)', 'y')
            ->setDescription('Create a user')
        ;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelperSet()->get('dialog');

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        $login = ($input->getOption('login') !== null) ? $input->getOption('login') : $dialog->ask($output, 'Login: ');
        $firstName = ($input->getOption('firstName') !== null) ? $input->getOption('firstName') : $dialog->ask($output, 'First name: ');
        $lastName = ($input->getOption('lastName') !== null) ? $input->getOption('lastName') : $dialog->ask($output, 'Last name: ');
        $password = ($input->getOption('password') !== null) ? $input->getOption('password') : $dialog->ask($output, 'Password: ');
        $email = ($input->getOption('email') !== null) ? $input->getOption('email') : $dialog->ask($output, 'Public e-mail: ');
        $branch = ($input->getOption('branch') !== null) ? $input->getOption('branch') : $dialog->ask($output, 'Branch: ');
        $isStudent = ($input->getOption('isStudent') === 'y' || $input->getOption('isStudent') === 'Y');

        $user = new User();
        $user->setLogin($login);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setFullName($user->getFirstName().' '.$user->getLastName());
        $user->setPassword($this->getContainer()->get('security.password_encoder')->encodePassword($user, $password));
        $user->setMail($email);
        $user->setBranch($branch);
        $user->setIsStudent($isStudent);

        $em->persist($user);
        $em->flush();

        $output->writeln("\nDone.\n");
    }
}
