Tests de charge EtuUTT
======================

Ce dossier contient les tests de charge EtuUTT. Ces tests sont effectués avec Locust, un outil moderne de tests de charge
en Python.

Pour lancer les tests de charge, executez la commande :

    locust -f tests/load.py -H http://etu.utt.fr

Une fois lancée, connecté en VPN au SIA, allez sur http://172.16.1.120:8089 et lancez les tests avec les caractéristiques
voulues.