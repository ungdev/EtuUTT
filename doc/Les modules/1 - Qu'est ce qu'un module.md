Qu'est ce qu'un module ?
========================

Les modules sont la base du fonctionnement de EtuUTT. Un module représente
une portion du site internet. Par exemple, il existe actuellement quelques
modules comme le cumul d'emploi du temps, la gestion du trombinoscope, etc.

Le but d'un module est d'être indépendant du site : il peut être retiré très
facilement à tout moment, sans erreur. Il faut donc éviter le plus possible
les dépendances entre modules. Cependant, le système de modules de EtuUTT
propose une gestion des dépendances entre modules permettant d'éviter le
plus possible les erreurs à la désactivation d'un module.

> Connaissant Symfony, vous verrez rapidement qu'un module est un bundle
> qui utilisent des ressources en plus fournies par le CoreBundle.

