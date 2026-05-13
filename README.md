# Système Intelligent de Gestion des Permanences et Heures Supplémentaires

## Présentation

Cette application web permet la gestion des :

* Permanences
* Heures supplémentaires
* Contrôle administratif
* Validation des listes
* Génération de rapports
* Extraction automatique des données PDF avec IA

Le projet a été développé avec :

* PHP Native
* MySQL
* JavaScript
* Chart.js
* Gemini API
* HTML / CSS

---

# Fonctionnalités principales

## 1. Importation PDF

L’utilisateur peut importer :

* Une liste de permanence
* Une liste d’heures supplémentaires

Formats acceptés :

* PDF

---

## 2. Extraction intelligente avec Gemini AI

Le système utilise Gemini API pour :

* Lire les fichiers PDF
* Extraire automatiquement les informations
* Transformer les données en JSON
* Enregistrer les données dans MySQL

Informations extraites :

* Nom complet
* Numéro de travail
* CIN
* Cadre
* Dates
* Nombre de jours
* Nombre d’heures
* Travaux réalisés

---

## 3. Gestion des permanences

Le système permet :

* Ajouter des permanences
* Modifier les données
* Supprimer les erreurs
* Recherche avancée
* Validation administrative

---

## 4. Gestion des heures supplémentaires

Fonctionnalités :

* Import PDF
* Modification manuelle
* Gestion des travaux réalisés
* Validation
* Contrôle

---

## 5. Dashboard

Le tableau de bord affiche :

* Nombre de permanences
* Nombre d’heures supplémentaires
* Nombre de listes refusées
* Nombre d’observations
* Dernières listes
* Dernières observations

---

## 6. Rapports

Le système permet :

* Export Excel
* Export PDF
* Recherche par :

  * année
  * trimestre
  * service
  * numéro de travail

---

## 7. Validation administrative

Chaque liste peut être :

* Acceptée
* Refusée
* Commentée

Statuts disponibles :

* En attente
* Validée
* Refusée

---

## 8. Statistiques

La page statistiques contient :

* Graphiques permanences / HS
* Statuts des listes
* Répartition par service
* Observations par niveau

Bibliothèque utilisée :

* Chart.js

---

# Structure du projet

```text
gestion_permanance/
│
├── ajax/
├── config/
├── controllers/
├── exports/
├── models/
├── uploads/
├── views/
├── public/css/
├── autoload.php
├── index.php
└── README.md
```

---

# Installation

## 1. Cloner le projet

```bash
git clone REPOSITORY_LINK
```

---

## 2. Copier dans WAMP

```text
C:\wamp64\www\gestion_permanance
```

---

## 3. Créer la base de données

Créer une base MySQL :

```text
gestion_permanance
```

---

## 4. Importer le fichier SQL

Dans phpMyAdmin :

* Import
* Choisir database.sql
* Exécuter

---

## 5. Configurer la connexion

Fichier :

```text
config/database.php
```

Exemple :

```php
private $host = "localhost";
private $dbname = "gestion_permanance";
private $username = "root";
private $password = "";
```

---

## 6. Configurer Gemini API

Fichier :

```text
config/gemini.php
```

Exemple :

```php
define('GEMINI_API_KEY', 'YOUR_API_KEY');
```

---

# Technologies utilisées

* PHP Native
* MySQL
* JavaScript
* HTML5
* CSS3
* Chart.js
* Gemini AI
* Git & GitHub

---

# Auteur

Projet réalisé par :

Youssef

Dans le cadre d’un stage de développement web.

---

# Version

Version actuelle :

```text
v1.0
```
