
Envoyer des notifications depuis un module
==========================================

Le système de notifications d'EtuUTT est géré par le CoreBundle. Il utilise
les tags du "service container" de Symfony.

Pour ajouter une notification, il faut d'abord créer un Helper : une
classe qui sait comment afficher un type précis de notification.

Créons donc notre helper, en prenant l'exemple d'une notification d'évènement:

	<?php

	namespace Etu\Module\AcmeBundle\Notification\Helper;

	use Etu\Core\CoreBundle\Entity\Notification;
	use Etu\Core\CoreBundle\Notification\Helper\HelperInterface;

	/**
	 * An helper is a class that know how to display a given kind of notification
	 */
	class NewEventHelper implements HelperInterface
	{
		/**
		 * @var \Twig_Environment
		 */
		protected $twig;

		/**
		 * @param \Twig_Environment $twig
		 */
		public function __construct(\Twig_Environment $twig)
		{
			$this->twig = $twig;
		}

		/**
		 * @return string
		 */
		public function getName()
		{
			return 'new_event';
		}

		/**
		 * @param Notification $notification
		 * @return string
		 */
		public function render(Notification $notification)
		{
			return $this->twig->render('EtuModuleAcmeBundle:Demo:view.html.twig', array('notif' => $notification));
		}
	}

Cet helper va donc recevoir une notification de la part de la page de flux
afin de lui retourner une chaine de caractères à afficher. Pour cela, nous
utilisons ici Twig, mais notez que cela n'est pas nécessaire.

Cet helper créé, il nous faut l'enregistrer afin de pouvoir l'utiliser. Pour
cela, nous devons utiliser le système de tags de Symfony, au travers du fichier
Etu/Module/AcmeBundle/Resources/config/services.yml :

        etu.notifs.followed_helper:
            class: Etu\Module\AcmeBundle\Notification\Helper\NewEventHelper
            arguments: [@twig] # Ceci n'est nécessaire que si vous utilisez Twig
            tags:
                - { name: etu.notifs_helper } # C'est ici que se fait le lien

> **Note :** N'oubliez pas de vider votre cache si le helper n'est pas trouvé

Désormais, le flux connait votre helper et sait donc afficher des notifications
d'un type spécifique à votre module.

Il ne reste donc plus qu'à envoyer concrêtement votre notification aux étudiants
abonnés à votre entité :

