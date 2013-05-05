
Référence des fonctionnalités dans Twig
=======================================

Le EtuCoreBundle et le EtuUserBundle proposent beaucoup de fonctionnalités utilisables
depuis Twig.

Les templates
-------------

Il existe trois templates de bases :

	- `base.html.twig` qui est la base du layout
	- `page-1col.html.twig` qui est le layout pour une page à une seule colonne
	- `page-2cols.html.twig` qui est le layout pour une page à deux colonnes

Bien sûr, la plupart du temps, vous hériterez de `page-2cols.html.twig`. Cependant,
`page-1col.html.twig` pourra vous être utile afin de mettre en place de grands éléments,
qui pourrait avoir besoin de largeur.

De plus, il peut même vous arriver d'avoir besoin d'utiliser `base.html.twig` directement,
lorsque par exemple vous voulez mettre en place une organisation spécifique à la page
actuelle (comme la page de connexion, par exemple, qui utilise seulement la moitié de la
page : `src/Etu/UserBundle/Resources/views/Auth/connect.html.twig`.

Les fonctions
-------------

EtuUTT met en place de nombreuses fonctions pour faciliter la vie des déveoppeurs.
En voici une liste.


## Importantes

Fonctionnalités importantes (utiles pour le développement).

### is_subscriber(User $user, $entityType, $entityId)

Vérifie si l'utilisateur donné est abonné à l'entité donnée.

### string|ucfirst

Met la première lettre de la chaine donnée en majuscule.

### string|urlencode

Encode la chaine donnée pour les URL.

### string|limit(length)

Limite la taille de la chainne donnée à la limite donnée (affiche "..." si elle dépasse).

### word|camelize

Convertit un mot en camelCase ('camel_case' => 'CamelCase')

### word|uncamelize

Convertit un mot depuis camelCase ('CamelCase' => 'camel_case')

### string|seems_utf8

Vérifie si la chaine donnée semble être en UTF-8 ou non.

### string|unaccent

Enlève tous les accents des lettres accentuées de la chaine ('é' => 'e').

### string|slugify

Transforme la chaine donnée en un slug, une chaine de caractère sans caractères
spéciaux, en minuscule, et avec des tirets à la place des espaces.

### is_private($privacy)

Vérifie que la valeur donnée correspond à quelque chose de privé.

### is_public($privacy)

Vérifie que la valeur donnée correspond à quelque chose de public.


## Informatives

Fonctionnalités informatives (inutiles pour le développement mais intéressantes pour comprendre).

### render_notif(Notification $notification)

Affiche une notification selon son helper.

### render_orga_menu()

Affiche le menu des ogranisations.

### render_user_menu()

Affiche le menu des utilisateurs.

### render_sidebar()

Affiche la sidebar.


Les variables globales
----------------------

### etu

La variable `etu` correspond en quelque sorte à `app` pour EtuUTT. Elle donne accès
à de nombreuses fonctionnalités quant aux spécificités d'EtuUTT en plus de celles
de Symfony.

#### Vérifier l'existence d'un module (même désactivé)

	``` twig
	etu.hasModule($identifier)
	```

#### Vérifier l'état d'activation d'un module

	``` twig
	etu.moduleEnabled($identifier)
	```

#### Récupération des abonnements de l'utilisateur courant

	``` twig
	etu.notifs.subscriptions
	```

#### Récupération des nouvelles notifications de l'utilisateur courant

	``` twig
	etu.notifs.new
	```

#### Récupération du nombre de nouvelles notifications de l'utilisateur courant

	``` twig
	etu.notifs.new_count
	```


