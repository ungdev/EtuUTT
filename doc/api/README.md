
Documentation API
=================

Qu'est ce que c'est ?
---------------------

Ceci est la documentation API de EtuUTT (ie une version du code accessible en HTML, pour comprendre les rouages sans
avoir à chercher dans le code).

Elle documente tout le contenu du dossier `src/Etu`.

Pour y accéder, copiez le contenu de ce dossier dans un dossier sur votre ordinateur et ouvrez le fichier
`build/index.html` avec votre navigateur.

Cette documentation utilise [Sami](http://fabien.potencier.org/article/63/sami-yet-another-php-api-documentation-generator).

Comment regéréner la documentation API ?
----------------------------------------

Excétuez, dans le dossier `doc/API` :

`php build.php update config.php`