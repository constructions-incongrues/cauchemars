DESCRIPTION
===========
Depuis la création du [forum des Musiques Incongrues](http://www.musiques-incongrues.net) en 2006 nous constituons une [base de données](http://data.musiques-incongrues.net) regroupant tous les liens postés par nos contributeurs. Ce corpus regroupe plus de 45 000 liens à ce jour.

Ce script permet de générer des compilations constituées de morceaux sélectionnés aléatoirement au sein de cette base de données.

Chaque compilation est constituée de fichiers .wav dont la durée cumulée est inférieure à 74 minutes. 
Le script génère aussi un fichier décrivant la compilation : présentation, playlist.

Les données sont recueillies grâce à l'API mise à disposition par http://data.musiques-incongrues.net.

PRÉ-REQUIS
==========
* mpg123

INSTALLATION
============

```bash
git clone git@github.com:contructions-incongrues/cauchemars.git
cd cauchemars
php composer.phar install
mkdir -p var/compilations
```

USAGE
=====
```bash
Usage:
 ./cauchemars generate-compilation [--count[="..."]] [--prefix[="..."]]

Options:
 --count   Number of compilations to generate (default: 1)
 --prefix  Compilations prefix (default: 'ananas')


```