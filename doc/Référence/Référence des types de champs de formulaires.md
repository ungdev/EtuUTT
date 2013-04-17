
Référence des types de champs de formulaire
===========================================

EtuCoreBundle et EtuUserBundle fournissent des types de champs de formulaire
pour faciliter la mise en place d'outils orienté utilisateurs (autocomplétion
et WYSIWYG).

### Autocomplétion utilisateur

**user**
*Etu\Core\UserBundle\Form\UserAutocompleteType*

Met en place un champ de texte avec autocomplétion sur le nom de l'utilisateur.

### RedactorJs

**etu:users:grant**
*Etu\Core\UserBundle\Command\GrantUserPermissionCommand*

RedactorJs est un éditeur de contenu WYSIWYG. Ce type de champ met en place Redactor
au dessus d'un textarea.

En accompagnement de ce type de champs, utilitsez
`Etu\Core\CoreBundle\Util\RedactorJsEscaper::escape($string)`
pour sécuriser la donnée utilisateur.
