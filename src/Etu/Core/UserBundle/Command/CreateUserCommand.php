<?php

namespace Etu\Core\UserBundle\Command;

use Doctrine\ORM\EntityManager;
use Etu\Core\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class CreateUserCommand extends ContainerAwareCommand
{
    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('etu:users:create')
            ->addOption('firstName', 'fn', InputOption::VALUE_REQUIRED, 'First name')
            ->addOption('lastName', 'ln', InputOption::VALUE_REQUIRED, 'Last name')
            ->addOption('password', 'pa', InputOption::VALUE_REQUIRED, 'Password')
            ->addOption('email', 'em', InputOption::VALUE_REQUIRED, 'Public e-mail')
            ->addOption('branch', 'br', InputOption::VALUE_REQUIRED, 'Branch')
            ->addOption('isStudent', 'st', InputOption::VALUE_REQUIRED, 'Is it a student (y/n)')
            ->addOption('isStaffUTT', 'sf', InputOption::VALUE_REQUIRED, 'Is it a staff of UTT (y/n)')
            ->setDescription('Create a user');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        $firstName = ($input->getOption('firstName') !== null) ? $input->getOption('firstName') : $helper->ask($input, $output, new Question('First name: '));
        $lastName = ($input->getOption('lastName') !== null) ? $input->getOption('lastName') : $helper->ask($input, $output, new Question('Last name: '));
        $password = ($input->getOption('password') !== null) ? $input->getOption('password') : $helper->ask($input, $output, new Question('Password: '));
        $email = ($input->getOption('email') !== null) ? $input->getOption('email') : $helper->ask($input, $output, new Question('Public e-mail: '));
        $branch = ($input->getOption('branch') !== null) ? $input->getOption('branch') : $helper->ask($input, $output, new Question('Branch: '));
        $isStudent = ($input->getOption('isStudent') !== null) ? $input->getOption('isStudent') === 'y' : $helper->ask($input, $output, new ConfirmationQuestion('Is it a student (Y/n)', true));
        $isStaffUTT = ($input->getOption('isStaffUTT') !== null) ? $input->getOption('isStaffUTT') === 'y' : $helper->ask($input, $output, new ConfirmationQuestion('Is it a staff of UTT (y/N)', false));

        $user = new User();
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setFullName($user->getFirstName().' '.$user->getLastName());
        $user->setPassword($this->getContainer()->get('security.password_encoder')->encodePassword($user, $password));
        $user->setMail($email);
        $user->setBranch($branch);
        $user->setIsStudent($isStudent);
        $user->setIsStaffUTT($isStaffUTT);
        $user->setIsInLDAP(false);

        // Set external user login and studentId
        $user->setLogin($user->getMail());
        $lowestId = $em->createQueryBuilder()
            ->select('MIN(u.studentId)')
            ->from('EtuUserBundle:User', 'u')
            ->getQuery()
            ->getSingleScalarResult();
        $user->setStudentId($lowestId - 1);

        $em->persist($user);
        $em->flush();

        $output->writeln("\nDone.\n");
    }
}
