# 🏦 Bank Management

## Description
**Bank Management** est une application de gestion bancaire permettant :
- **Aux utilisateurs** : De créer des comptes bancaires, effectuer des transactions (dépôts, retraits, transferts) et consulter leurs historiques de transactions.
- **Aux administrateurs** : De superviser les comptes, les transactions, et de suivre l'activité via des outils analytiques.

Ce projet a été réalisé dans le cadre de notre formation à l'EFREI (2ᵉ année du Bachelor Développement Web & Application) pour la matière *Hackathon*.

---


## Fonctionnalités

### Design
- **Responsive** : Adapté aux mobiles et tablettes.
- **Visuel moderne** : Utilisation de Bootstrap pour un design épuré et professionnel.
- **Animation** : Animations fluides pour une expérience utilisateur agréable.

### Côté Utilisateur
- Création de comptes bancaires.
- Dépôt de fonds.
- Authentification sécurisée
- Retrait de fonds.
- Transferts entre comptes.
- Consultation des transactions pour chaque compte.
- Suivi de budget visuel et intuitif

### Côté Administrateur
- **Gestion des utilisateurs** :
    - Ajout, modification et suppression d'utilisateurs.
- **Tableau de bord admin** :
    - Vue globale des comptes, transactions et indicateurs clés.
    - Outils d'analyse graphique.
- **Gestion des transactions** :
    - Consultation et annulation des transactions en cas d'erreur.
- **Supervision analytique** :
    - Suivi des activités via des graphiques interactifs.

---

## Navigation dans le site

### Côté utilisateur
- **Page d'accueil (Login)** : Connexion avec email et mot de passe.
- **Tableau de bord utilisateur** :
    - Aperçu des comptes bancaires et solde total.
    - Boutons rapides pour effectuer un dépôt, un retrait ou un transfert.
- **Page des transactions** :
    - Liste des transactions avec détails (montant, date, type).
- **Page de création de compte** : Formulaire pour créer un nouveau compte bancaire.
- **Page Bénéficiaire** : Formulaire pour ajouter un bénéficiaire pour les transferts.
- **Déconnexion** : Redirection vers la page de connexion.

### Côté administrateur
- **Page de connexion admin** : Accès sécurisé avec identifiants spécifiques.
- **Tableau de bord admin** :
    - Indicateurs principaux : nombre total d'utilisateurs, comptes bancaires, et transactions.
    - Navigation par icônes pour explorer les détails.
- **Gestion des utilisateurs** :
    - Ajouter un utilisateur via un formulaire.
    - Modifier ou supprimer des utilisateurs existants.
- **Gestion des transactions** :
    - Vue complète des transactions.
    - Option d'annulation pour chaque transaction.
- **Déconnexion** : Redirection vers la page d'accueil admin.

---

## Sécurité

- **Authentification sécurisée** : Utilisation de l'authentification par mot de passe haché.
- **Validation des entrées** : Protection contre les injections SQL et XSS.
- **Gestion des sessions** : Utilisation de sessions sécurisées pour maintenir l'état de connexion.
- **Contrôles d'accès** : Vérification des autorisations pour chaque action (utilisateur vs administrateur).
- **Protection CSRF** : Utilisation de jetons CSRF pour sécuriser les formulaires.

## Qualité du code et bonnes pratiques

- **Architecture MVC** : Séparation claire entre le modèle, la vue et le contrôleur.
- **Utilisation de Composer** : Gestion des dépendances PHP.
- **ORM Doctrine** : Gestion des entités et des relations avec la base de données.
- **Architecture modulaire** : Chaque fonctionnalité est encapsulée dans son propre module.
- **Séparation des préoccupations** : Les fichiers sont organisés par fonctionnalité et type (contrôleurs, modèles, vues).

---

## Technologies utilisées

### Backend
- **Langage** : PHP 8.1
- **Framework** : Symfony
- **Twig** pour les templates HTML

### Base de données
- **PostgresSQL**

### Frontend
- **HTML** pour la structure
- **CSS** pour le style
- **JavaScript**
- **Bootstrap** pour le design responsive

---

## Installation

### Prérequis
- **PHP 8.1** ou version ultérieure.
- **Composer**.
- **PostgresSQL**.

### Étapes d'installation
1. **Clonez le projet** :
   ```bash
   git clone https://github.com/Elioott/Bank-Management
   ```

2. **Naviguez dans le dossier du projet** :
   ```bash
   cd Bank-Management
   ```

3. **Installez les dépendances PHP avec Composer** :
   ```bash
   composer install
   ```

4. **Configurez le fichier `.env`** :
   Remplacez `DATABASE_URL` par vos informations PostgreSQL :
   ```dotenv
   DATABASE_URL="postgresql://<username>:<password>@127.0.0.1:5432/<nom_de_la_base>?serverVersion=16&charset=utf8"
   ```

5. **Créez la base de données** :
   ```bash
   php bin/console doctrine:database:create
   ```

6. **Appliquez les migrations** :
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

7. **Lancez le serveur Symfony** :
   ```bash
   symfony server:start
   ```

8. **Accédez à l'application** : [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## Structure du projet

### Architecture Modulaire
- `Account` : Gestion des comptes bancaires.
- `Transactions` : Gestion des transactions (dépôts, retraits, transferts).
- `Admin` : Gestion des fonctionnalités administratives.
- `Auth` : Authentification et sécurité.
- `Home` : Page d'accueil et tableau de bord utilisateur.
- `Beneficiary` : Gestion des bénéficiaires pour les transferts.

### Dossier `src`
- `templates/` : Contient les fichiers Twig pour le frontend.
- `public/CSS` : Contient les fichiers CSS pour le design.
- `public/JS` : Contient les fichiers JavaScript.
- `public/videos` : Contient les vidéos utilisées pour les arrière-plans.

---

## Améliorations futures
- Re factorisation du code pour une meilleure lisibilité.
- Ajout de fonctionnalités de sécurité avancées (authentification à deux facteurs).
