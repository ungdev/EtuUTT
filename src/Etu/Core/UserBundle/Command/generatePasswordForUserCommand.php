<?php

namespace Etu\Core\UserBundle\Command;

use Etu\Core\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManager;

class generatePasswordForUserCommand extends ContainerAwareCommand
{
    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('etu:users:generate-password')
            ->setDescription('Set user password to let him connect as an external account')
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

        $output->writeln('
	Welcome to the EtuUTT users manager

This command will help you to set generate a random password for an user to let him connect as an external account and send him via email.
');

        $user = null;

        /** @var $mailer \Swift_Mailer */
        $mailer = $this->getContainer()->get('mailer');

        /** @var $twig wig_Environment */
        $twig = $this->getContainer()->get('twig');

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        while (!$user instanceof User) {
            $login = $dialog->ask($output, 'User login: ');

            $user = $em->getRepository('EtuUserBundle:User')->findOneBy(['login' => $login]);

            if (!$user) {
                $output->writeln("The given login can not be found. Please retry.\n");
            }
        }

        $output->writeln('User found: '.$user->getFirstName().' '.$user->getLastName());

        $email = $dialog->ask($output, 'External email ['.$user->getPersonnalMail().']: ', $user->getPersonnalMail());
        $user->setPersonnalMail($email);

        // Generate password
        $consonant = 'bcdfgjklmnpqrstvwxz';
        $vowel = 'aeiou';
        $countC = mb_strlen($consonant);
        $countV = mb_strlen($vowel);
        $result = '';

        for ($i = 0; $i < 5; ++$i) {
            $index = mt_rand(0, $countC - 1);
            $result .= mb_substr($consonant, $index, 1);

            $index = mt_rand(0, $countV - 1);
            $result .= mb_substr($vowel, $index, 1);
        }
        $password = $this->getContainer()->get('security.password_encoder')->encodePassword($user, $result);
        $user->setPassword($password);
        $em->persist($user);
        $em->flush();

        // Send email with password
        $subject = 'Identifiants de connexion à EtuUTT';
        $content = $twig->render('EtuUserBundle:Mail:password.html.twig', [
           'fullName' => $user->getFirstName().' '.$user->getLastName(),
           'login' => $user->getLogin(),
           'password' => $result,
        ]);

        $message = \Swift_Message::newInstance($subject)
           ->setFrom(['bde@utt.fr' => 'BDE UTT'])
           ->setTo([$email])
           ->setBody($content, 'text/html');
        $result = $mailer->send($message);

        if ($result > 0) {
            $output->writeln('The user '.$user->getLogin()." can now connect with a password that has been sent to him via email.\n");
        } else {
            $output->writeln("Fail to send email to user. Please try again.\n");
        }
    }
}
