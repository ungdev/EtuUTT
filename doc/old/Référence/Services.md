
Référence des services fournis par EtuCoreBundle et EtuUserBundle
=================================================================

Ce document référence tous les services fournis par EtuCoreBundle et EtuUserBundle.
Il vous permet de vous donner une idée des possibilités offertes par ces deux bundles
afin d'éviter la dupplication de code.

Chaque service est décrit plus en détail dans la documentation API.

### Modules manager

**etu.core.modules_manager**
*Etu\Core\CoreBundle\Framework\Module\ModulesManager*

Gestionnare des modules. Récupère la liste des modules (activés et désactivés),
leurs caractéristiques et permet d'activer/désactiver des modules.

### Routing loader

**etu.core.routing_loader**
*Etu\Core\CoreBundle\Framework\Routing\ModulesRoutingLoader*

Le Routing loader est l'élément qui charge les routes des modules automatiquement selon le fait qu'ils
soient activés ou non.

### Sidebar builder

**etu.menu.sidebar_builder**
*Etu\Core\CoreBundle\Menu\Sidebar\SidebarBuilder*

Builder pour la sidebar. Permet de modifier depuis le contrôleur ou les modules la sidebar (menu de droite,
présente seulement sur les pages à deux colones).

### Sidebar renderer

**etu.menu.sidebar_renderer**
*Etu\Core\CoreBundle\Menu\Sidebar\SidebarRenderer*

Objet générant le HTML de la sidebar.

### Sidebar Twig extension

**etu.menu.sidebar_twig_extension**
*Etu\Core\CoreBundle\Twig\Extension\SidebarRendererExtension*

Donne un accès Twig au "Sidebar renderer".

### User menu Builder

**etu.menu.user_builder**
*Etu\Core\CoreBundle\Menu\UserMenu\UserMenuBuilder*

Builder pour le menu utilisateur. Permet de modifier depuis le contrôleur ou les modules
le menu utilisateur.

### User menu renderer

**etu.menu.user_renderer**
*Etu\Core\CoreBundle\Menu\UserMenu\UserMenuRenderer*

Objet générant le HTML du menu utilisateur.

### User menu Twig extension

**etu.menu.user_twig_extension**
*Etu\Core\CoreBundle\Twig\Extension\UserMenuRendererExtension*

Donne un accès Twig au "User menu renderer".

### Modules boot listener

**etu.modules_boot_listener**
*Etu\Core\CoreBundle\Framework\Listener\ModulesBootListener*

Listener écoutant l'évènement kernel.request pour charger les modules nécessaires (utilisant
mustBoot()) et pour donner un accès aux modules depuis Twig.

### Notifications helpers manager

**etu.notifs.helper_manager**
*Etu\Core\CoreBundle\Notification\Helper\HelperManager*

Gestionnaire des helpers pour notifications (enregistrement en utilisant le CompilerPass,
récupération des bons helpers).

### Listener de nouvelles notifications

**etu.notifs.listener**
*Etu\Core\CoreBundle\Notification\Listener\NewNotifsListener*

Récupération automatique des nouvelles notifications (requête SQL) et stockage de la valeur
dans une globale Twig pour affichage.

### Emetteur de notifications

**etu.notifs.sender**
*Etu\Core\CoreBundle\Notification\NotificationSender*

Objet pour envoyer des notifications depuis les modules.

### Gestion des abonnements

**etu.notifs.subscriber**
*Etu\Core\CoreBundle\Notification\SubscriptionsManager*

Objet pour gérer les abonnements des utilisateurs (ajouter et supprimer des modifier des abonnements).

### Extension Twig de gestion des abonnements

**etu.notifs.subscriber.twig**
*Etu\Core\CoreBundle\Twig\Extension\SubscriptionsManagerExtension*

Fournit des méthodes d'accès aux abonnements utilisateur depuis Twig.

### Notifications helper extension

**etu.twig.notif_helper**
*Etu\Core\CoreBundle\Twig\Extension\NotificationHelperExtension*

Fournit la fonction Twig `render_notif` pour afficher une notification spécifique en utilisant
son helper.

### String manipulation

**etu.twig.string_manipulation**
*Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension*

Fournit des filtres de chaines de caractères pour Twig et PHP.

### Authentication listener

**etu.user.authentication_listener**
*Etu\Core\UserBundle\Security\Listener\KernelListener*

Listener de kernel.request pour récupérer les informations utilisateur de la base de données.

### LDAP manager

**etu.user.ldap**
*Etu\Core\UserBundle\Ldap\LdapManager*

Gestion du LDAP de l'UTT.

### Synchronizer

**etu.user.sync**
*Etu\Core\UserBundle\Sync\Synchronizer*

Objet capable de synchroniser la base de données et le LDAP fournissant un Iterator.

### Privacy extension

**etu.user.twig.privacy_extension**
*Etu\Core\UserBundle\Twig\Extension\PrivacyExtension*

Extension Twig pour la gestion de la confidentialité
