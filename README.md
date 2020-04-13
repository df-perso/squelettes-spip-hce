# Squelettes SPIP pour le site Handi Cap Evasion
[https://www.hce.asso.fr](https://www.hce.asso.fr)

## Principes

Le site web est basé sur SPIP 3.2 et les squelettes sont construits avec BootStrap 4. L'objectif est d'avoir une navigation fluide aussi bien sur un grand écran que sur un téléphone ou une tablette. Le redesign permet aussi de donner plus de places aux images et aux vidéos.

La quasi totalité du contenu du site est dynamique, soit administrable directement dans SPIP, soit dans iTop.
Les pages sont découpées en "sections" que l'on peut combiner différement selon les rubriques pour varier un peu la navigation dans le site.
En plus du contenu issu de SPIP deux sections purement statiques (càd codées directement en HTML) existent:
  - La section "engagement" qui affiche un bandeau (Adhérez / Inscrivez-vous / Faites un don)
  - La section "historique de l'association" qui affiche un résumé des grandes étapes de l'association sous la forme d'une "timeline".
   
**La saisie des informations relatives aux séjour s'effectue désormais dans un seul endroit: iTop**. De nouveaux champs ont été rajoutés sur l'objet "**Séjour**" pour permettre la génération automatique du tableau des séjours:
  * **Type** : le type de séjour (Itinérant, En étoile...). Ce champ est un liste figée, car chaque valeur doit avoir son pictogramme correspondant sur le site web.
  * **Tarif** : le code du tarif pour ce séjour (A, B, C...). Ce champ est un texte libre car la codification des tarifs varie quelque peu d'une année sur l'autre.

### Modèles pour l'affichage des séjours

Les informations dynamiques de la page séjour (tableau des séjours, date de mise à jour des places restantes...) sont affichées via l'utilisation de *modèles* inclus dans des articles épinglés en tête de rubrique. Le contenu des ces articles reste éditable, comme n'importe quel autre article, mais certaines parties deviennent dynamiques.

Ces informations concernant les séjours sont directements extraites d'iTop et **basculent automatiquement d'une saison sur l'autre** quand la date de publication de la nouvelle saison est échue (traditionellement le jour de l'AG).

Les modèles disponibles sont les suivants:

  * `<tableau_des_sejours|>` : affiche le tableau des séjour (liens vers les fiches techniques, places restantes)
  * `<tableau_des_sejours|>` : affiche la légende des types de séjours (sous forme d'un tableau aussi)
  * `<ouverture_des_inscriptions|>` : affiche une "alerte" (encart bleu) indiquant les dates d'ouverture des inscriptions pour les passagers joëlette et les accompagnateurs actifs. Si ces deux dates sont déjà dépassées, n'affiche rien du tout.
  * `<tableau_des_sejours_mise_a_jour|>` : affiche la date de dernière mise à jour du tablau des séjours.
  * `<carte_des_sejours|>` : affiche (sous forme d'IFRAME) la carte des séjours.
  
#### Configuration dans iTop

La publication de la liste des séjours est configurée dans iTop par de nouveaux champs ajoutés sur l'objet "**Saison**":
  * **Publication des séjours** : date (et heure précise) à laquelle la liste des séjours de la saison doit apparaître sur le site web
  * **Ouverture des inscriptions (P.J.)** : Date à laquelle les passagers joëlette pourront commencer à s'inscire aux séjours (cf `<ouverture_des_inscriptions|>` ci-dessus).
  * **Ouverture des inscriptions (A.A.)** : Date à laquelle les accompagnateurs actifs pourront commencer à s'inscire aux séjours (cf `<ouverture_des_inscriptions|>` ci-dessus).
  * **Carte des séjours (URL)**: URL permettant d'afficher la carte interactive des séjours (cf `<carte_des_sejours|>` ci-dessus)

#### Mise à jour des places disponibles

La mise à jour des places disponibles s'effectue désormais directement dans iTop, via le **menu "Places Disponibles"**. 

## Configuration

Le code des squelettes est public (ici même sur Git) mais certains paramètres doivent rester confidentiels ou être aisément configurables. Ces paramètres ont été regroupés dans le fichier `config/mes_options.php` (à créer s'il n'existe pas dans votre instance de SPIP).

Voici une exemple de fichier `config/mes_options.php`:

```php
<?php
// sécurité
if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

define('HCE_MOT_CLE_SEJOURS', 'Séjours2019'); // Pour afficher les compte-rendus des séjours de l'année
define('YOUTUBE_API_KEY', 'xxxxxxxxxxxxxxx'); // Pour afficher la liste des vidéos YouTube
define('ITOP_DB_HOST', 'serveur');            // Connexion à iTop
define('ITOP_DB_USER', 'utilisateur');        // Connexion à iTop
define('ITOP_DB_PWD', 'mot-de-passe');        // Connexion à iTop
define('ITOP_DB_NAME', 'nom-de-la-base');     // Connexion à iTop
define('ITOP_DB_TABLES_PREFIX', 'itop_');     // Connexion à iTop
```

**Note**: la liste des compte-rendus des séjours est pilotée par l'attribution d'un mot clé "SéjoursXXXX" (à créer chaque saison) aux articles correspondants. Les compte-rendus affichés sur la page d'accueil, sont ceux associés au mot clé `HCE_MOT_CLE_SEJOURS` défini dans le fichier `config/mes_options.php` (cf ci-dessus).
Le titre de la section est calculé en extrayant l'année (XXXX) du label du mot clé, il faut dont toujours nommer ce mot-clé de la même façon ('Séjours2020', 'Séjours2021', etc...).

### Rubriques et sous-rubriques
Quand une rubrique *racine* contient des sous-rubriques, elle est affichée sous la forme d'un menu déroulant dans la barre de menu principale. Le titre de la rubrique lui même n'est donc plus cliquable. Dans ce cas, une entrée de sous-menu est automatiquement rajoutée. Le label pour cette entrée est défini sous forme d'un tableau directement dans le squelette `squelettes/includes/menu.html`.

```
#SET{rubrique_labels, #ARRAY{2,"Présentation de Handi Cap Evasion", 7, "Tous les reportages", 23, "Les nouveaux projets"}}
```

## Composants utilisés

Les composants suivant sont utilisés dans ces squelettes:

### CSS et Javascript
  * JQuery
  * Bootstrap 4
  * Font Awesome 5 (free)
  * Slick Carousel
  * Scroll Reveal
  * Lity lightbox
  * Police "Fira Sans"

### Plugins SPIP
  * Champs Extras
  * UploadHtml5
  * En exergue
  