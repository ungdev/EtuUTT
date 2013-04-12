
Référence des commandes fournis par EtuCoreBundle et EtuUserBundle
==================================================================

Ce document référence toutes les commandes fournies par EtuCoreBundle et EtuUserBundle.

### Génération d'un module

**etu:generate:module**
*Etu\Core\CoreBundle\Command\GenerateModuleCommand*

Génère un squelette de module prèt à l'emploi.

### Ajouter une permission à un utilisateur

**etu:users:grant**
*Etu\Core\UserBundle\Command\GrantUserPermissionCommand*

Accorde une permission spécifique à un utilisateur.

### Création d'une association

**etu:orgas:create**
*Etu\Core\UserBundle\Command\CreateOrgaCommand*

Créer une organisation.

### Synchronisation avec le LDAP

**etu:users:sync**
*Etu\Core\UserBundle\Command\SyncProcessCommand*

Synchronise le LDAP et la base de données.

### Etat de synchrnosation avec le LDAP

**etu:users:sync-status**
*Etu\Core\UserBundle\Command\SyncStatusCommand*

Compare la base de données et le LDAP pour déterminer les différences.

### Création d'un utilisateur

**etu:users:create**
*Etu\Core\UserBundle\Command\CreateUserCommand*

Créer une utilisateur.
