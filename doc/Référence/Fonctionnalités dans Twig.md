
Référence des fonctionnalités dans Twig
=======================================

Le EtuCoreBundle et le EtuUserBundle proposent beaucoup de fonctionnalités
à utiliser dans Twig quant à EtuUTT.

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

Les macros
----------

Les macros sont des morceaux de HTML réutilisables partout dans Twig. EtuUTT en définit
pour le moment un seul : celui pour s'abonner à un élément.

Pour l'utiliser, vous devez faire appel à lui comme à une méthode de l'objet `etu_subscribe`.
Par exemple, dans `src/Etu/Module/BugsBundle/Resources/views/Bugs/view.html.twig` :

	{{ etu_subscribe.block('issue', bug.id) }}

Les fonctions
-------------

EtuUTT met en place de nombreuses fonctions pour faciliter la vie des déveoppeurs.
En voici une liste.


## Importantes

Fonctionnalités importantes (utiles pour le développement).

### is_subscriber(User $user, $entityType, $entityId)

Vérifie si l'utilisateur donné est abonné à l'entité donnée.

### ucfirst($string)

Met la première lettre de la chaine donnée en majuscule.

### urlencode($string)

Encode la chaine donnée pour les URL.

### limit($string, $length)

Limite la taille de la chainne donnée à la limite donnée (affiche "..." si elle dépasse).

### camelize($word)

Convertit un mot en camelCase ('camel_case' => 'CamelCase')

### uncamelize($word)

Convertit un mot depuis camelCase ('CamelCase' => 'camel_case')

### seems_utf8($string)

Vérifie si la chaine donnée semble être en UTF-8 ou non.

### unaccent($string)

Enlève tous les accents des lettres accentuées de la chaine ('é' => 'e').

### slugify($string)

Transforme la chaine donnée en un slug, une chaine de caractère sans caractères
spéciaux, en minuscule, et avec des tirets à la place des espaces.

### is_private($privacy)

Vérifie que la valeur donnée correspond à quelque chose de privé.

### is_public($privacy)

Vérifie que la valeur donnée correspond à quelque chose de public.


## Informatives

Fonctionnalités informatives (inutiles pour le développement mais importantes pour comprendre).

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

### etu_new_notifs

Contient les nouvelles notifications.

### etu_count_new_notifs

Contient le nombre de nouvelles notifications.

### etu

Contient une instance de `Etu\Core\CoreBundle\Framework\Twig\GlobalAccessorObject` qui
permet de :

	- vérifier l'existence d'un module (même désactivé) :
			etu.hasModule($identifier)
	- vérifier l'état d'activation d'un module :
			etu.moduleEnabled($identifier)

