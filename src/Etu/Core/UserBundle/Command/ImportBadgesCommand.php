<?php

namespace Etu\Core\UserBundle\Command;

use Etu\Core\UserBundle\Entity\Badge;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportBadgesCommand extends ContainerAwareCommand
{
	/**
	 * Configure the command
	 */
	protected function configure()
	{
		$this
			->setName('etu:badges:import')
			->setDescription('Import badges')
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
        $em = $this->getContainer()->get('doctrine')->getManager();

        foreach ($this->badges as $badgeData) {
            $em->persist(new Badge(
                $badgeData['serie'],
                $badgeData['name'],
                $badgeData['description'],
                $badgeData['picture'],
                $badgeData['level'],
                $badgeData['countLevels']
            ));

            $em->flush();
        }
	}

    private $badges = array(
        array('serie' => 'profile_completed','name' => 'Fiché','picture' => 'prism_00','description' => 'A complété son profil entièrement','level' => '1','countLevels' => '1','deletedAt' => NULL),
        array('serie' => 'trombi_completed','name' => 'Data lover','picture' => 'data_lover_00','description' => 'A complété son trombi entièrement','level' => '1','countLevels' => '1','deletedAt' => NULL),
        array('serie' => 'orga_member','name' => 'Associatif','picture' => 'membre_asso_00','description' => 'Membre d\'une association de l\'UTT','level' => '1','countLevels' => '4','deletedAt' => NULL),
        array('serie' => 'orga_member','name' => 'Remue-méninges','picture' => 'membre_bureau_00','description' => 'Membre du bureau d\'une association de l\'UTT','level' => '2','countLevels' => '4','deletedAt' => NULL),
        array('serie' => 'orga_member','name' => 'Rameur en chef','picture' => 'president_00','description' => 'Président(e) d\'une association de l\'UTT','level' => '3','countLevels' => '4','deletedAt' => NULL),
        array('serie' => 'orga_member','name' => 'Ohé, ohé, Capitaine','picture' => 'president_bde_00','description' => 'Président(e) du BDE de l\'UTT','level' => '4','countLevels' => '4','deletedAt' => NULL),
        array('serie' => 'tc01','name' => 'The wrong hole','picture' => 'wrong_hole_00','description' => 'Est en TC01','level' => '1','countLevels' => '1','deletedAt' => NULL),
        array('serie' => 'tc06','name' => 'T.T. est mon ami','picture' => 'donkey_hat_00','description' => 'Est en TC06','level' => '1','countLevels' => '1','deletedAt' => NULL),
        array('serie' => 'tc_survivor','name' => 'Le survivant','picture' => 'survivant_00','description' => 'A terminé son TC','level' => '1','countLevels' => '1','deletedAt' => NULL),
        array('serie' => 'starter','name' => 'Starter','picture' => 'challenge_starter_00','description' => 'A obtenu les 3 badges "What\'s up", "Heure révélatrice" et "Data lover"','level' => '1','countLevels' => '1','deletedAt' => NULL),
        array('serie' => 'uvs_reviews','name' => 'Fait ou fiction','picture' => 'love_annales_01','description' => 'A posté 1 annale sur le site','level' => '1','countLevels' => '4','deletedAt' => NULL),
        array('serie' => 'uvs_reviews','name' => 'Récidive','picture' => 'love_annales_02','description' => 'A posté 2 annales sur le site','level' => '2','countLevels' => '4','deletedAt' => NULL),
        array('serie' => 'uvs_reviews','name' => 'Vous en voulez encore ?','picture' => 'love_annales_03','description' => 'A posté 4 annales sur le site','level' => '3','countLevels' => '4','deletedAt' => NULL),
        array('serie' => 'uvs_reviews','name' => 'I <3 Annale','picture' => 'love_annales_04','description' => 'A posté 10 annales sur le site','level' => '4','countLevels' => '4','deletedAt' => NULL),
        array('serie' => 'subscriber','name' => 'What\'s up ?','picture' => 'abonnement_01','description' => 'A ajouté 10 élément à ses abonnements','level' => '1','countLevels' => '3','deletedAt' => NULL),
        array('serie' => 'subscriber','name' => 'Je me renseigne','picture' => 'abonnement_02','description' => 'A ajouté 20 éléments à ses abonnements','level' => '2','countLevels' => '3','deletedAt' => NULL),
        array('serie' => 'subscriber','name' => 'Sacre de l\'abondance','picture' => 'abonnement_03','description' => 'A ajouté 30 éléments à ses abonnements','level' => '3','countLevels' => '3','deletedAt' => NULL),
        array('serie' => 'challenge','name' => 'Attrapez les tous !','picture' => 'challenge_badges_01','description' => 'A obtenu 10 badges','level' => '1','countLevels' => '5','deletedAt' => NULL),
        array('serie' => 'challenge','name' => 'Attrapez les tous !','picture' => 'challenge_badges_02','description' => 'A obtenu 20 badges','level' => '2','countLevels' => '5','deletedAt' => NULL),
        array('serie' => 'challenge','name' => 'Attrapez les tous !','picture' => 'challenge_badges_03','description' => 'A obtenu 30 badges','level' => '3','countLevels' => '5','deletedAt' => NULL),
        array('serie' => 'challenge','name' => 'Attrapez les tous !','picture' => 'challenge_badges_04','description' => 'A obtenu 40 badges','level' => '4','countLevels' => '5','deletedAt' => NULL),
        array('serie' => 'challenge','name' => 'Attrapez les tous !','picture' => 'challenge_badges_05','description' => 'A obtenu 50 badges','level' => '5','countLevels' => '5','deletedAt' => NULL),
        array('serie' => 'monkey','name' => 'Heure révélatrice','picture' => 'monkey_01','description' => 'A posté 1 message dans les forums','level' => '1','countLevels' => '5','deletedAt' => NULL),
        array('serie' => 'monkey','name' => 'Des ampoules aux doigts','picture' => 'monkey_02','description' => 'A posté 20 messages dans les forums','level' => '2','countLevels' => '5','deletedAt' => NULL),
        array('serie' => 'monkey','name' => 'Chercheur compulsif','picture' => 'monkey_03','description' => 'A posté 50 messages dans les forums','level' => '3','countLevels' => '5','deletedAt' => NULL),
        array('serie' => 'monkey','name' => 'Conscience collective','picture' => 'monkey_04','description' => 'A posté 100 messages dans les forums','level' => '4','countLevels' => '5','deletedAt' => NULL),
        array('serie' => 'monkey','name' => 'Typing Monkey','picture' => 'monkey_05','description' => 'A posté 500 messages dans les forums','level' => '5','countLevels' => '5','deletedAt' => NULL),
        array('serie' => 'mysterion','name' => 'Qui suis-je ?','picture' => 'mysterion_01','description' => 'A initié 1 discussion sur les forums','level' => '1','countLevels' => '4','deletedAt' => NULL),
        array('serie' => 'mysterion','name' => 'D\'où viens-je ?','picture' => 'mysterion_02','description' => 'A initié 10 discussions sur les forums','level' => '2','countLevels' => '4','deletedAt' => NULL),
        array('serie' => 'mysterion','name' => 'Où vais-je ?','picture' => 'mysterion_03','description' => 'A initié 20 discussions sur les forums','level' => '3','countLevels' => '4','deletedAt' => NULL),
        array('serie' => 'mysterion','name' => 'Patrick, l\'homme qui posait des questions','picture' => 'mysterion_04','description' => 'A initié 40 discussions sur les forums','level' => '4','countLevels' => '4','deletedAt' => NULL),
        array('serie' => 'hipster','name' => 'Hipster','picture' => 'hipster_00','description' => 'A été parmi les premiers à visiter EtuUTT','level' => '1','countLevels' => '1','deletedAt' => NULL),
		array('serie' => 'duck_hunter','name' => 'Duck Hunter','picture' => 'duck_hunt_00','description' => 'A trouvé le canard caché','level' => '1','countLevels' => '1','deletedAt' => NULL),
		array('serie' => 'cereales','name' => 'Team Céréales','picture' => 'cereales_00','description' => 'A mangé des céréales','level' => '1','countLevels' => '1','deletedAt' => NULL),
		array('serie' => 'aigri','name' => 'Aigri !','picture' => 'aigri_00','description' => 'A déjà fait partie du bureau d\'une association.','level' => '1','countLevels' => '1','deletedAt' => NULL)
    );
}
