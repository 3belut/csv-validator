CSV Validator
=============

Une application Web permettant la validation des données d'un fichier CSV d'import en masse.

Déploiement
-----------

Ce projet utilise la version 3.4 du framework Symfony, et nécessite donc **PHP 5.5.9** ou supérieur. Les extensions suivantes sont utilisées et doivent être activées :

* cURL
* SOAP

Le script peut mettre jusqu'à 150 secondes pour s'exécuter selon la machine, la taille du fichier et le nombre de tests à réaliser. Il convient donc d'augmenter le paramètre **max_execution_time** dans le fichier de configuration **php.ini**.
