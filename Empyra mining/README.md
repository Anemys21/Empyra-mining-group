# Empyra Mining Group

## Description
Site web de présentation pour Empyra Mining Group, une entreprise spécialisée dans l'exploitation minière.

## Prérequis
- XAMPP (Apache, MySQL, PHP)
- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Navigateur web moderne (Chrome, Firefox, Edge, etc.)

## Installation avec XAMPP

1. **Installation de XAMPP**
   - Télécharger XAMPP depuis [le site officiel](https://www.apachefriends.org/index.html)
   - Exécuter l'installateur et suivre les instructions
   - Installer les composants suivants :
     - Apache
     - MySQL
     - PHP
     - phpMyAdmin

2. **Configuration du projet**
   - Placer le dossier du projet dans `C:\xampp\htdocs\Empyra mining`
   - Démarrer les services Apache et MySQL depuis le panneau de contrôle XAMPP
   - Ouvrir phpMyAdmin à l'adresse : `http://localhost/phpmyadmin`

3. **Configuration de la base de données**
   - Dans phpMyAdmin, créer une nouvelle base de données nommée `empyra_mining`
   - Importer le fichier SQL (s'il est fourni) ou exécuter les requêtes suivantes :
   ```sql
   CREATE DATABASE IF NOT EXISTS empyra_mining;
   USE empyra_mining;
   
   CREATE TABLE IF NOT EXISTS fichiers (
       id INT AUTO_INCREMENT PRIMARY KEY,
       nom_fichier VARCHAR(255) NOT NULL,
       type_fichier VARCHAR(50) NOT NULL,
       taille_fichier INT NOT NULL,
       description TEXT,
       chemin_fichier VARCHAR(500) NOT NULL,
       date_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );
   ```
   - Vérifier les paramètres de connexion dans `config.php` :
   ```php
   $servername = "localhost";
   $username = "root";
   $password = "";
   $dbname = "empyra_mining";
   ```

4. **Configuration PHP**
   - Vérifier que les extensions suivantes sont activées dans `php.ini` :
     - extension=mysqli
     - extension=pdo_mysql
     - extension=gd (pour le traitement d'images)
   - Configurer les permissions :
     - Le dossier `uploads/` doit avoir les permissions en écriture
     - Pour Windows : Clic droit sur le dossier > Propriétés > Sécurité > Modifier les autorisations

## Utilisation

1. **Démarrage**
   - Lancer XAMPP Control Panel
   - Démarrer les modules Apache et MySQL
   - Ouvrir un navigateur et accéder à : `http://localhost/Empyra mining/acceuil.html`

2. **Pages principales**
   - Accueil : `acceuil.html`
   - Services : `services.html`
   - Projets : `projects.html`
   - À propos : `about.html`
   - Téléchargements : `telechargement.php`
   - Contact : `Contact.html`
   - Actualités : `actualites.php`

3. **Administration**
   - Gestion des documents : `admin_upload.php`
   - Gestion des actualités : `admin_upload_Actu.php`

3. **Fonctionnalités**
   - Formulaire de contact avec envoi d'email
   - Téléchargement de documents
   - Interface d'administration pour gérer les fichiers à télécharger

## Développement

### Structure des dossiers
```
Empyra mining/
├── uploads/             # Fichiers téléversés
│   ├── actualites/      # Images des actualités
│   └── ...
├── images/              # Images du site
├── project-about-images/ # Images de la page À propos
├── *.php               # Fichiers PHP
├── *.html              # Pages HTML
└── *.css               # Feuilles de style
```

### Personnalisation
- Modifier les fichiers CSS dans le dossier racine
- Les couleurs principales sont définies dans les variables CSS au début des fichiers
- Les polices peuvent être modifiées dans les fichiers CSS

## Sécurité
- Ne pas modifier les permissions des fichiers inutilement
- Toujours vérifier les fichiers téléversés
- Garder les identifiants de base de données sécurisés

## Dépannage

1. **Erreurs de connexion à la base de données**
   - Vérifier que MySQL est démarré dans XAMPP
   - Vérifier les identifiants dans `config.php`
   - S'assurer que la base de données existe

2. **Problèmes de téléchargement**
   - Vérifier les permissions du dossier `uploads/`
   - Vérifier la taille maximale des fichiers dans `php.ini`
   - Vérifier les logs d'erreur d'Apache

3. **Pages non trouvées**
   - Vérifier que le module `mod_rewrite` est activé dans `httpd.conf`
   - Vérifier que le fichier `.htaccess` est présent

## Support
Pour toute question ou problème, veuillez contacter :
- Email : contact@empyramining.com
- Téléphone : +237 677 22 39 28

---
*Dernière mise à jour : 06/09/2025*
