# Quotation module

Quotation est un module Prestashop permettant de générer des devis au format PDF 
pour un client avant de passer commande d'un ou plusieurs produit·s.

## Kit de démarrage
* Installer `Prestashop` sur vore machine
* Télécharger le projet
* Supprimer le dossier `.git` pour supprimer l'historique
* Déplacer le `.gitignore` à la racine du projet
* Déplacer le dossier `quotation` dans le dossier `modules` de votre arborescence Prestashop

## Installation
* Se déplacer dans le dossier `modules/quotation`
* Exécuter `composer install`
* Exécuter `npm install`
* Exécuter `npm install --save-dev webpack`
* Exécuter `npm run webpack`
* Exécuter `doctrine:schema:update --force`

## Administration
* Renommer le dossier 'admin' dans le fichier 'quotation/webpack.config.js' par le nom de votre dossier admin de la racine de Prestashop sur les lignes 10 et 12.

## Construit avec
* Prestashop 1.7.6.3
* PHP 7.2
* Symfony 3.4
* Twig

## Auteurs
* Lionel DELAMARE
* Yurniel LAHERA VILLA   
* Nolwenn SACHET  
* Toua VA

## Lien vidéo pour avoir un apperçu du fonctionnement du module
https://drive.google.com/file/d/1DJrneJWrAAaoL-FkcjMSJ_lYPU7bsr7U/view?usp=sharing
