EtuUTT
======

EtuUTT est la nouvelle version (2013) du site étudiant de l'Université de Technologie de Troyes.

Originellement développé par [Titouan Galopin](https://github.com/tgalopin) au sein de l'association UTT Net Group (l'association étudiante d'informatique de l'UTT), le site étudiant propose des outils et des services à tous les étudiants pour faciliter leur intégration et leur vie de tous les jours. Il contient aussi des espaces d'échange et des raccourcis vers les différentes plateformes en ligne de l'école.

Cette refonte de 2013 a pour vocation d'améliorer les performances, le design et la qualité du code.
Ce projet utilise Symfony 3.1

Documentation
-------------

La documentation est disponible sur [https://github.com/ungdev/EtuUTT/wiki](https://github.com/ungdev/EtuUTT/wiki).
C'est un travail en cours, n'hésitez pas à nous contacter si jamais la ressource recherchée est manquante…
De la documentation est aussi disponible sur le site étudiant directement : https://etu.utt.fr/wiki/view/general/etuutt/developpeur/installer-une-version-locale-d-etuutt

Lancement du serveur en local
-------------

1. Il vous suffit d'avoir `docker` et `docker-compose` d'installés
2. Copiez le .env en .env.local et éditez ses caractéristiques. Pensez à éditer le UID et à mettre le votre afin de pouvoir monter correctement votre code source dans le container.
3. Lancez le tout : `docker-compose up -d`
4. Connectez-vous dans le container : `docker exec -it etuutt_etuutt_1 bash` puis initiez le site
```
./composer install
php bin/console doctrine:schema:update --force
php bin/console doctrine:fixtures:load -n
php bin/console etu:ue:import
php bin/console etu:badges:import
```

Pour créer les scopes API : `php bin/console etu:oauth:create-scope`. Les différents scopes : `public`, `private_user_organizations`, `private_user_schedule`, `private_user_account`

Pour lancer une synchro avec le LDAP UTT (nécessite d'être sur le réseau UTT), **ASSUREZ-VOUS QUE LES MAILS SONT DESACTIVES** (env `ETUUTT_MAILER_HOST` vide), `php bin/console etu:users:sync`

5. Rendez-vous sur http://127.0.0.1:8000 pour voir le site (avec l'id user/user ou admin/admin) et sur http://127.0.0.1:8080 pour voir adminer.
Cela permet d'éviter d'installer nginx et de devoir tout configurer

Traduction
----------
Tous les jours, les différentes chaînes pouvant être traduites sont importées sur [notre projet Transifex](https://www.transifex.com/ung/site-etudiant).

Vous pouvez alors en traduire, remonter des erreurs, demander de nouveaux langages… La création du compte est bien sûr gratuite ! Ouvrez juste une issue ou envoyez-nous un message quand vous voulez que l'on intègre vos modifications sur Github.

Déploiement continu
-------------------

Ce projet utilise l'intégration continue (TravisCI), et la branche `master` est directement à jour avec la production.
Ce dernier est déployé sur un cluster openshift. Chaque push déclenche une reconstruction de l'image et la mise à jour automatique de la version en prod (Rolling Update)

Branche `dev` (développement) :
[![Build Status](https://travis-ci.org/ungdev/EtuUTT.svg?branch=dev)](https://travis-ci.org/ungdev/EtuUTT)

Branche `master` (production) :
[![Build Status](https://travis-ci.org/ungdev/EtuUTT.svg?branch=master)](https://travis-ci.org/ungdev/EtuUTT)
