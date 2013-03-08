
Le menu utilisateur (au clic dans le header) est accessible à la modification depuis l'exterieur par le service
`etu.menu.user_builder`. Il est affiché grâce à la fonction Twig `render_user_menu()`.

Il est constitué, par défaut, des éléments suivants :

	base.user.menu.flux				Mon flux
	base.user.menu.account			Mon compte
	base.user.menu.buckutt			Mon compte BuckUTT
	base.user.menu.dailymail		Mon dailymail
	base.user.menu.emails			Mes e-mails
	base.user.menu.table			Mon emploi du temps
	base.user.menu.logout			Déconnexion
	separator-1
	base.user.menu.help				Aide