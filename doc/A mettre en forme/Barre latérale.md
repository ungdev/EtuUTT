
La barre latérale est accessible à la modification depuis l'exterieur par le service
`etu.menu.sidebar_builder`. Elle est affichée grâce à la fonction Twig `render_sidebar()`.

Elle est constituée, par défaut, des éléments suivants :

	base.sidebar.services.title						Services
		base.sidebar.services.items.uvs					Les UV
		base.sidebar.services.items.trombi				Le trombinoscope
		base.sidebar.services.items.table				Cumul d'emplois du temps
		base.sidebar.services.items.wiki				Wiki des associations
	base.sidebar.etu.title							EtuUTT
		base.sidebar.etu.items.team						L'équipe
		base.sidebar.etu.items.suggest					Suggestions
		base.sidebar.etu.items.bugs						Signaler un bug