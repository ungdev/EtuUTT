<?php

namespace Etu\Module\DaymailBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Etu\Module\DaymailBundle\Entity\DaymailPart;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\Translator;

class SendCommand extends ContainerAwareCommand
{
	/**
	 * Configure the command
	 */
	protected function configure()
	{
		$this
			->setName('etu:daymail:send')
			->setDescription('Send the daymail to all the users using daymail@utt.fr')
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
		/*
		 * Init the components
		 */
		/** @var $container Container */
		$container = $this->getContainer();

		/** @var $mailer \Swift_Mailer */
		$mailer = $container->get('mailer');

		/** @var $twig \Twig_Environment */
		$twig = $container->get('twig');

		/** @var $doctrine Registry */
		$doctrine = $container->get('doctrine');

		/** @var $translator Translator */
		$translator = $container->get('translator');
		$translator->setLocale('fr');

		/*
		 * Find daymails
		 */
		/** @var $em EntityManager */
		$em = $doctrine->getManager();

		/** @var $daymailParts DaymailPart[] */
		$daymailParts = $em->createQueryBuilder()
			->select('d, o')
			->from('EtuModuleDaymailBundle:DaymailPart', 'd')
			->leftJoin('d.orga', 'o')
			->where('d.day = :day')
			->setParameter('day', date('d-m-Y'))
			->getQuery()
			->getResult();

		if ($daymailParts) {
			/*
			 * Create the message
			 */
			$subject = 'Daymail du ';
			$subject .= $translator->trans('daymail.memberships.daymail.days.'.strtolower(date('D'))).' ';
			$subject .= date('d').' ';
			$subject .= $translator->trans('daymail.memberships.daymail.months.'.strtolower(date('M'))).' ';

			$content = $twig->render('EtuModuleDaymailBundle:Mail:daymail.html.twig', array(
				'daymail' => $daymailParts[0],
				'parts' => $daymailParts
			));

			$message = \Swift_Message::newInstance($subject)
				->setFrom(array('bde@utt.fr' => 'BDE UTT'))
				->setTo(array('titouan@ademis.com'))
				->setBody($content, 'text/html');

			$result = $mailer->send($message);

			if ($result > 0) {
				$output->writeln("Daymail for ".date('d-m-Y')." sent (".count($daymailParts)
					." part".(count($daymailParts) > 1 ? 's' : '').").\n");
			} else {
				$output->writeln("An error occured.\n");

				$alert = \Swift_Message::newInstance('Erreur lors de l\'envoi du '.$subject)
					->setFrom(array('bde@utt.fr' => 'BDE UTT'))
					->setTo(array('ung@utt.fr'))
					->setBody('Le '.$subject.' n\'a pas pu être envoyé, une erreur a interrompu son envoi.');

				$alertResult = $mailer->send($alert);

				if ($alertResult == 0) {
					$output->writeln("The alert message can not be sent.\n");
				}
			}
		} else {
			$output->writeln("There is no part in the current daymail.\n");
		}
	}
}
