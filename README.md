DESCRIPTION
===========
Depuis la création du forum des Musiques Incongrues en 2006 nous constituons une base de données regroupant tous les liens postés par nos contributeurs. Ce corpus regroupe plus de 36 000 liens à ce jour.

Ce script permet de générer des compilations constituées de morceaux sélectionnés aléatoirement au sein de cette base de données.

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
./compilation <nombre de compilations à générer>
```