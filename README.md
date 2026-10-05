# 🤖 Prompt Manager BCP — Backend

Backend de la plateforme **Prompt Manager BCP**, développée avec **Laravel 13** dans un contexte bancaire professionnel.

L'application permet de centraliser, personnaliser et exécuter des prompts destinés aux intelligences artificielles génératives. Elle intègre également un module d'**évaluation technique des offres fournisseurs** à partir d'un cahier des charges et d'une offre fournisseur au format PDF.

---

## 📌 Présentation

**Prompt Manager BCP** est une application destinée à faciliter l'utilisation des solutions d'intelligence artificielle dans un environnement professionnel.

Le backend fournit une API REST consommée par une interface frontend développée avec **Vue.js 3**.

L'application permet notamment de :

* créer et gérer des prompts ;
* importer des documents Word contenant des variables `{{ }}` ;
* détecter automatiquement les variables d'un prompt ;
* exécuter des prompts avec des valeurs personnalisées ;
* utiliser des modèles d'intelligence artificielle tels que **Gemini** et **Groq** ;
* exporter les résultats dans différents formats ;
* envoyer les résultats par email ;
* réaliser une **évaluation technique d'une offre fournisseur** à partir de documents PDF.

---

# ✨ Fonctionnalités principales

## 📝 Gestion des prompts

Le backend permet de :

* créer un prompt manuellement ;
* modifier un prompt ;
* supprimer un prompt ;
* consulter les prompts existants ;
* organiser les prompts par catégories ;
* détecter les variables présentes dans un prompt.

Les variables utilisent la syntaxe :

```text
{{ nom_variable }}
```

Exemple :

```text
Rédige un email destiné à {{ destinataire }}
concernant {{ sujet }}.
```

Le backend détecte automatiquement :

```text
destinataire
sujet
```

---

## 📄 Import de documents Word

L'application permet d'importer des documents Word contenant des prompts.

Le backend :

1. reçoit le fichier ;
2. extrait son contenu ;
3. identifie les variables `{{ }}` ;
4. prépare le prompt pour son utilisation dans l'application.

---

## 🤖 Intégration des modèles IA

Le backend permet d'utiliser plusieurs fournisseurs d'intelligence artificielle.

### Gemini

Google Gemini est utilisé notamment pour :

* générer des réponses à partir des prompts ;
* analyser les documents ;
* réaliser l'évaluation technique.

### Groq

Groq peut également être utilisé pour l'exécution des prompts.

Les clés API sont configurées dans le fichier `.env` et **ne doivent jamais être versionnées dans Git**.

---

# 📊 Évaluation technique des offres fournisseurs

Le projet intègre un module destiné à assister l'évaluation technique d'une offre fournisseur dans le cadre d'un appel d'offres informatique.

## Principe

L'utilisateur fournit deux documents PDF :

```text
Cahier des charges
        +
Offre fournisseur
        ↓
Extraction des informations
        ↓
Identification des exigences techniques
        ↓
Extraction des caractéristiques de l'offre
        ↓
Comparaison
        ↓
Résultat de conformité
```

Le système utilise l'intelligence artificielle pour comparer les exigences du cahier des charges avec les caractéristiques indiquées dans l'offre fournisseur.

### Exemple

Le cahier des charges demande :

```text
Mémoire RAM : minimum 32 Go
```

L'offre fournisseur indique :

```text
Mémoire RAM : 32 Go
```

Résultat :

```text
Conforme
```

Si l'offre indique :

```text
Mémoire RAM : 16 Go
```

le résultat attendu est :

```text
Non conforme
```

Le module est conçu comme un **outil d'aide à l'analyse technique**. La décision finale d'une procédure d'achat reste réalisée par les personnes responsables de l'évaluation.

---

# 🏗️ Architecture

L'application est organisée selon une architecture séparant le frontend, le backend, la base de données et les services nécessaires à l'exécution.

```text
                    ┌─────────────────────┐
                    │   Vue.js Frontend   │
                    │      Port 5173      │
                    └──────────┬──────────┘
                               │
                               │ HTTP / REST API
                               ▼
                    ┌─────────────────────┐
                    │    Nginx            │
                    │      Port 8000      │
                    └──────────┬──────────┘
                               │
                               ▼
                    ┌─────────────────────┐
                    │ Laravel Backend     │
                    │      PHP 8.3        │
                    └──────┬───────┬──────┘
                           │       │
                ┌──────────┘       └──────────┐
                ▼                             ▼
       ┌────────────────┐            ┌─────────────────┐
       │    MySQL 8     │            │  Gemini / Groq  │
       │    Database    │            │   APIs IA       │
       └────────────────┘            └─────────────────┘
```

---

<<<<<<< HEAD
## ⚙️ Configuration

Copier `.env.docker.example` vers `.env.docker` (ou `.env.example` vers `.env` en local) et remplir les valeurs :

### Clé API Gemini (gratuit)
1. Aller sur [https://aistudio.google.com/](https://aistudio.google.com/)
2. Cliquer sur **"Get API Key"** → **"Create API Key"**
3. Coller la clé :
```env
GEMINI_API_KEY=votre_cle_ici
```

### Clé API Groq (gratuit, rapide)
1. Aller sur [https://console.groq.com/](https://console.groq.com/)
2. Aller dans **"API Keys"** → **"Create API Key"**
3. Coller la clé :
```env
GROQ_API_KEY=votre_cle_ici
```

### Email (Gmail recommandé)
1. Aller sur [https://myaccount.google.com/apppasswords](https://myaccount.google.com/apppasswords)
2. Créer un mot de passe d'application (nécessite la vérification en 2 étapes activée)
3. Configurer :
```env
MAIL_USERNAME=votre_email@gmail.com
MAIL_PASSWORD=votre_mot_de_passe_application_sans_espaces
MAIL_FROM_ADDRESS=votre_email@gmail.com
```

### Base de données
=======
# 🛠️ Stack technique

| Composant                 | Technologie          |
| ------------------------- | -------------------- |
| Framework backend         | Laravel 13           |
| Langage                   | PHP 8.3              |
| Base de données           | MySQL 8.0            |
| Serveur web               | Nginx                |
| Serveur PHP               | PHP-FPM              |
| Intelligence artificielle | Google Gemini / Groq |
| Gestion des dépendances   | Composer             |
| Conteneurisation          | Docker               |
| Orchestration             | Docker Compose       |
| API                       | REST                 |
| Frontend associé          | Vue.js 3             |

---

# 📁 Structure du backend

```text
promptbcp-backend/
│
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── TechnicalEvaluationController.php
│   │
│   ├── Models/
│   │   ├── TechnicalEvaluation.php
│   │   ├── TechnicalRequirement.php
│   │   └── TechnicalEvaluationResult.php
│   │
│   └── Services/
│       └── TechnicalEvaluation/
│           └── TechnicalEvaluationService.php
│
├── database/
│   └── migrations/
│
├── docker/
│   └── nginx.conf
│
├── routes/
│   └── api.php
│
├── storage/
│
├── Dockerfile
├── docker-compose.yml
├── composer.json
├── .env.docker.example
└── README.md
```

---

# 🐳 Installation avec Docker

Docker constitue la méthode recommandée pour exécuter le projet.

## Prérequis

Installer :

* Docker Desktop
* Git

Docker Desktop doit être démarré avant le lancement de l'application.

Aucune installation locale de PHP, Composer, MySQL ou Nginx n'est nécessaire pour l'utilisation avec Docker.

---

# ⚙️ Configuration

Le projet utilise des variables d'environnement pour sa configuration.

Un fichier exemple est fourni :

```text
.env.docker.example
```

Copier ce fichier :

```powershell
copy .env.docker.example .env.docker
```

Puis renseigner les paramètres nécessaires dans `.env.docker`.

Ensuite :

```powershell
copy .env.docker .env
```

## Base de données Docker

La configuration utilisée dans Docker est :

>>>>>>> feature/technical-evaluation-v2
```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
<<<<<<< HEAD
DB_DATABASE=promptbcp
DB_USERNAME=root
DB_PASSWORD=votre_mot_de_passe
=======
DB_DATABASE=promptBCP
DB_USERNAME=root
DB_PASSWORD=root
```

Important :

`DB_HOST` doit être :

```text
mysql
```

et non :

```text
127.0.0.1
```

car Laravel communique avec MySQL à travers le réseau Docker.

---

# 🔑 Variables IA

Les clés API doivent être configurées dans `.env`.

Exemple :

```env
GEMINI_API_KEY=your_gemini_key
GROQ_API_KEY=your_groq_key
```

Ne jamais publier les clés API dans GitHub.

Les fichiers suivants ne doivent pas être versionnés :

```text
.env
.env.docker
.env.backup
>>>>>>> feature/technical-evaluation-v2
```

---

<<<<<<< HEAD
## 📁 Structure du projet

```
promptbcp-backend/
├── app/
│   ├── Http/Controllers/
│   │   ├── PromptController.php           # CRUD prompts
│   │   ├── PromptExecutionController.php  # Exécutions
│   │   ├── AIController.php               # Intégration Gemini + Groq
│   │   ├── EmailController.php            # Envoi emails
│   │   └── PDFController.php              # Génération PDF
│   ├── Services/
│   │   ├── PromptService.php              # Extraction/remplacement variables
│   │   └── AIService.php                  # Appels API IA
│   └── Models/
│       ├── Category.php
│       ├── Prompt.php
│       ├── PromptVariable.php
│       └── PromptExecution.php
├── config/                                # Configuration Laravel
├── database/
│   └── migrations/                        # Schéma de la base de données
├── docker/
│   └── nginx.conf                         # Configuration Nginx backend
├── routes/
│   └── api.php                            # Définition des routes API
├── tests/                                 # Tests PHPUnit
├── Dockerfile                             # Image Docker backend
├── docker-compose.yml                     # Orchestration des services
├── .env.docker.example                    # Template de configuration
└── NOTICE_UTILISATION.txt                 # Guide d'utilisation simplifié
=======
# 📧 Configuration email

Si l'envoi d'emails est utilisé, renseigner également les paramètres SMTP nécessaires dans `.env`.

Exemple :

```env
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email
MAIL_FROM_NAME="Prompt Manager BCP"
```

Les identifiants SMTP sont des informations sensibles et ne doivent pas être publiés.

---

# 🚀 Lancement de l'application

Depuis le dossier du backend :

```powershell
docker compose up -d --build
```

Cette commande :

* construit l'image Laravel ;
* construit l'image frontend ;
* démarre MySQL ;
* démarre Laravel/PHP-FPM ;
* démarre Nginx ;
* démarre le frontend.

Vérifier les conteneurs :

```powershell
docker compose ps
```

Les quatre services doivent être actifs :

```text
promptbcp_backend
promptbcp_frontend
promptbcp_mysql
promptbcp_webserver
>>>>>>> feature/technical-evaluation-v2
```

---

<<<<<<< HEAD
## 🗄️ Modèle de données

```
Category (1) ──── (N) Prompt
Prompt   (1) ──── (N) PromptVariable
Prompt   (1) ──── (N) PromptExecution
```

| Table | Description |
|---|---|
| `categories` | Organisation des prompts par thématique |
| `prompts` | Templates de prompts avec variables `{{ }}` |
| `prompt_variables` | Variables détectées dans chaque prompt |
| `prompt_executions` | Historique des générations avec résultats |

---

## 🌐 API REST

| Endpoint | Méthode | Description |
|---|---|---|
| `/api/prompts` | GET | Liste des prompts |
| `/api/prompts` | POST | Créer un prompt |
| `/api/prompts/upload` | POST | Uploader un fichier Word |
| `/api/prompts/{id}/execute` | POST | Générer le prompt final |
| `/api/executions/{id}/download` | GET | Télécharger le fichier Word |
| `/api/ai/executions/{id}/gemini` | POST | Générer avec Gemini |
| `/api/ai/executions/{id}/groq` | POST | Générer avec Groq |
| `/api/executions/{id}/generate-pdf` | POST | Générer un PDF |
| `/api/executions/{id}/send-email` | POST | Envoyer par email |

---

## 🐳 Services Docker

| Service | Description | Port |
|---|---|---|
| `backend` | PHP-FPM (Laravel) | Interne |
| `webserver` | Nginx (API) | 8000 |
| `mysql` | MySQL 8.0 | 3307 |

---

## 🧪 Tests

```bash
# Avec Docker
docker-compose exec backend php artisan test

# En local
php artisan test
=======
# 🗄️ Base de données

Après le premier lancement, exécuter les migrations :

```powershell
docker compose exec backend php artisan migrate
```

Pour vérifier l'état des migrations :

```powershell
docker compose exec backend php artisan migrate:status
>>>>>>> feature/technical-evaluation-v2
```

---

<<<<<<< HEAD
## 🛠️ Dépannage

| Problème | Solution |
|---|---|
| `docker-compose build` échoue | Vérifier que Docker Desktop est lancé et dispose d'assez de mémoire allouée |
| Erreur de connexion MySQL | Vérifier que le conteneur `mysql` est bien démarré (`docker-compose ps`) et que `DB_HOST=mysql` dans `.env` |
| Migrations non appliquées | Relancer `docker-compose exec backend php artisan migrate` |
| Clé API IA invalide | Vérifier la clé dans `.env.docker`, puis redémarrer avec `docker-compose down && docker-compose up` |

---

## 📦 Repository frontend

Le code source du frontend Vue.js est disponible sur un repository séparé :
[promptbcp-frontend](https://github.com/mbarkimahmoud-maker/promptbcp-frontend)

---

## 📝 Licence

Projet développé dans le cadre d'un stage professionnel — usage interne.
=======
# 🌐 Accès à l'application

Frontend :

```text
http://localhost:5173
```

API backend :

```text
http://localhost:8000
```

MySQL depuis la machine hôte :

```text
Host: localhost
Port: 3307
Database: promptBCP
Username: root
```

À l'intérieur du réseau Docker, Laravel utilise :

```text
Host: mysql
Port: 3306
```

---

# 🔌 API principales

## Prompts

Les routes principales permettent notamment de :

* créer un prompt ;
* consulter les prompts ;
* modifier un prompt ;
* supprimer un prompt ;
* importer un document ;
* exécuter un prompt.

---

## Intelligence artificielle

Exemples de routes :

```text
POST /api/ai/executions/{execution}/gemini
POST /api/ai/executions/{execution}/groq
```

---

## Évaluation technique

### Lister les évaluations

```text
GET /api/technical-evaluations
```

### Créer une évaluation

```text
POST /api/technical-evaluations
```

Cette route reçoit notamment :

* le nom du fournisseur ;
* le cahier des charges PDF ;
* l'offre fournisseur PDF ;
* le fournisseur IA utilisé.

### Consulter une évaluation

```text
GET /api/technical-evaluations/{technicalEvaluation}
```

---

# 📊 Fonctionnement de l'évaluation technique

Lorsqu'une évaluation est créée :

### 1. Upload

Le backend reçoit :

```text
Cahier des charges.pdf
Offre fournisseur.pdf
```

### 2. Extraction

Le contenu textuel des PDF est extrait côté backend.

### 3. Analyse IA

Gemini analyse les documents afin d'identifier :

* les exigences techniques ;
* les valeurs demandées ;
* les caractéristiques proposées par le fournisseur.

### 4. Comparaison

Les informations extraites sont comparées.

### 5. Résultat

Le système produit les résultats d'évaluation avec notamment :

```text
Conforme
Non conforme
```

ainsi que les informations nécessaires à la consultation du résultat.

---

# ⏱️ Timeout de l'évaluation technique

L'analyse de documents avec une API d'intelligence artificielle peut prendre plus d'une minute.

Le serveur Nginx est donc configuré avec un délai plus important :

```nginx
fastcgi_read_timeout 300;
```

Cette configuration permet au backend de disposer de jusqu'à **5 minutes** pour répondre à une requête PHP-FPM avant que Nginx ne retourne une erreur de timeout.

---

# 🧪 Vérification de l'installation

Après le démarrage :

```powershell
docker compose ps
```

Puis vérifier les routes :

```powershell
docker compose exec backend php artisan route:list
```

Pour vérifier les routes d'évaluation technique :

```powershell
docker compose exec backend php artisan route:list --path=technical-evaluations
```

---

# 📜 Logs

## Logs du backend

```powershell
docker compose logs backend --tail 100
```

Pour suivre les logs en temps réel :

```powershell
docker compose logs -f backend
```

## Logs Nginx

```powershell
docker compose logs webserver --tail 100
```

## Logs MySQL

```powershell
docker compose logs mysql --tail 100
```

---

# 🛑 Arrêter l'application

Pour arrêter les conteneurs :

```powershell
docker compose down
```

Cette commande arrête les conteneurs sans supprimer le volume de données MySQL.

Pour redémarrer :

```powershell
docker compose up -d
```

---

# 🔄 Reconstruire l'application

Après une modification du Dockerfile ou du frontend :

```powershell
docker compose up -d --build
```

Pour reconstruire uniquement le frontend :

```powershell
docker compose build frontend
docker compose up -d frontend
```

Pour reconstruire uniquement le backend :

```powershell
docker compose build backend
docker compose up -d backend
```

---

# 🧹 Commandes Laravel utiles

Entrer dans le conteneur backend :

```powershell
docker compose exec backend bash
```

Vider le cache de configuration :

```powershell
docker compose exec backend php artisan config:clear
```

Vider les caches Laravel :

```powershell
docker compose exec backend php artisan optimize:clear
```

Vérifier la version Laravel :

```powershell
docker compose exec backend php artisan --version
```

---

# 🔐 Sécurité

Les informations suivantes sont sensibles :

* clés API Gemini ;
* clés API Groq ;
* identifiants SMTP ;
* mots de passe de base de données.

Elles doivent être stockées uniquement dans les fichiers `.env` locaux ou dans un système sécurisé de gestion des secrets.

Ne jamais publier de véritables clés API dans :

* GitHub ;
* le README ;
* le code source ;
* les captures d'écran ;
* les messages de commit.

Les fichiers `.env` doivent rester exclus du contrôle de version.

---

# 🔗 Frontend

Le frontend Vue.js est disponible dans un repository séparé :

**Prompt Manager BCP — Frontend**

```text
https://github.com/mbarkimahmoud-maker/promptbcp-frontend
```

Il communique avec l'API Laravel via :

```text
http://localhost:8000/api
```

---

# 📂 Repositories

### Backend

```text
https://github.com/mbarkimahmoud-maker/promptbcp-backend
```

### Frontend

```text
https://github.com/mbarkimahmoud-maker/promptbcp-frontend
```

---

# 🌿 Git — Branches

Le développement de la fonctionnalité d'évaluation technique est réalisé sur :

```text
feature/technical-evaluation-v2
```

La branche principale du projet est :

```text
main
```

---

# 👨‍💻 Contexte du projet

Projet développé dans le cadre d'un **stage professionnel à la Banque Centrale Populaire (BCP)**.

Le projet vise à explorer l'utilisation de l'intelligence artificielle pour assister les utilisateurs dans la gestion de prompts et dans l'analyse technique de documents liés aux achats informatiques.

---

# 📄 Licence

Projet développé dans un cadre professionnel et académique.

Usage interne.
>>>>>>> feature/technical-evaluation-v2
