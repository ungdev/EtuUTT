Le CoreBundle
========================

Le système de EtuUTT, basé sur le concept de bundle, s'appuie principalement sur
le bundle noyau Etu\Core\CoreBundle. Ce bundle fournit aux modules les
éléments nécessaires à leur fonctionnement (gestion des menus, gestion des
notifications, création des modules en eux-mêmes, gestion des dépendances, etc.)


Gestion des modules
-------------------

Le principe de fonctionnement du CoreBundle est de se baser sur le système de
bundles déjà très découplés de Symfony2 pour y ajouter le concept de modules.
Un module n'est donc rien d'autre qu'un bundle spécial, c'est-à-dire auquel
on a ajouté des fonctionnalités.

Un module, quelqu'il soit, doit posséder une classe de base étendant
`Etu\Core\CoreBundle\Framework\Definition\Module`. Cette classe fournit des
méthodes abstraites à surcharger :

``` php
<?php
abstract public function getIdentifier(); // Module identifier (to be required by other modules)
abstract public function getTitle(); // Module title (describe shortly its aim)
abstract public function getDescription(); // Module description
abstract public function getRequirements(); // Define the modules requirements (the required modules)
```

Grâce à ces quatres méthodes de base, le système de modules réussit à fournir à
l'espace d'administration suffisament d'informations pour éviter les erreurs lors
des activations/désactivations de modules.

Le CoreBundle gère les modules au moyen du ModulesManager
(`Etu\Core\CoreBundle\Framework\Module\ModulesManager`). Cette classe créer une liste
des modules disponibles (activés ou non). Elle permet aussi d'activer ou de désactiver
des modules en utilisant le fichier de configuration `app/config/modules.yml`.

> **Note:** Un problème persiste dans cette notion de module-bundle : les routes restent
> importées à la main, via routing.yml. Pour éviter de devoir modifier routing.yml à
> chaque activation ou désactivation de module, le CoreBundle dispose d'un RoutingLoader
> spécialisé, cpable de charger les routes des modules activés seulement (en utilisant
> la méthode `getRouting()` de la classe `Etu\Core\CoreBundle\Framework\Definition\Module`).


Gestion des menus
-----------------

Etant donné que les modules doivent être très découplés, il a fallu mettre en place une
architecture souple de gestion des menus (Sidebar et UserMenu). Le CoreBundle dispose
de ce système et le rend accessible à tous les modules.

De cette manière, tous les modules peuvent accéder, modifier, supprimer et ajouter des
éléments dans la sidebar et/ou dans le menu utilisateur. Pour cela, il suffit d'utiliser
le conteneur de services :

``` php
<?php
$container->get('etu.menu.sidebar_builder') // ...;
$container->get('etu.menu.user_builder') // ...;
```

> **Note:** Le tutoriel de modification des menus est disponible dans la partie
> "Les modules"

Le CoreBundle créé donc par la même occasion deux fonctions Twig (`render_sidebar()`
et `render_user_menu()`) afin d'afficher dans les templates Twig la sidebar et le
menu utilisateur de manière dynamique.


Interface en ligne de commande
------------------------------

Le CoreBundle propose une interface en ligne de commande pour créer l'arborescence
d'un module automatique, via la commande `php app/console etu:generate:module`.