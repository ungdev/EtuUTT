ArgentiqueBundle
================

Ce module met à disposition les photos placés dans le dossier ArgentiqueBundle/Resources/photos. Des photos de haute qualité peuvent être utilisé puisqu'elles sont converties pour le web à la volée.

### Regénération du cache
Lors d'un ajout massif de photo, l'utilisation de la commande `php bin/console etu:argentique:warmup` permet de pré-calculer toutes les photos et donc éviter une charge intense au serveur.

### Point d'entrée spécial photo argentique
Le but de cette galerie est de proposer les photos argentiques uniquement aux étudiants. En conséquence, pour chaque image visionné, il faut vérifier les droits. Malheureusement, cela implique en temps normal de lancer toute la stack Symfony pour chaque photo. Or le site etu télécharge toutes les photos d'un album en parallèle, en conséquence, la base de donnée SQL n'apprécie généralement pas de se retrouver avec des centaines de connexions causé par un seul utilisateur.

Pour contourner ce problème, le `SessionUpdater` de ce module s'occupe d'ajouter un cookie `external_jwt` contenant un jwt avec la variable `ROLE_ARGENTIQUE_READ` à 1 ou 0 pour savoir si l'utilisateur possède ce role ou non. Le cookie est conservé 10 minutes et est mis à jour à chaque requète http du site etu (sauf les images argentique). On utilise un JWT parce que cela nous permet de signer les données et donc de vérifier qu'il n'y a pas eu d'altération par l'utilisateur. Il faut donc configurer la clé `argentique_jwt_key` du fichier de conf pour que cela fonctionne. Dans l'idéal, on aurait pus utiliser les sessions, sauf que manifestement, lorsqu'on modifie les session dans symfony, il n'est pas vraiment possible de les récupérer en dehors de symfony.

Une fois ce JWT dans les cookie, le fichier php `ArgentiqueEntrypoint.php` qui se trouve dans le dossier `/web` principal, s'occupe d'afficher les images l'utilisateur est autorisé sans lancer la stack symfony et donc sans lancer de connexion sql. Pour que cela fonctionne, il faut par contre ajouter les lignes suivantes à la configuration d'Ngnix :

```
location /argentique/photo/ {
    try_files $uri /ArgentiqueEntrypoint.php$is_args$args;
}
```
