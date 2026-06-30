# 🤖 Prompt Manager BCP — Backend

API backend (Laravel) de la plateforme de gestion, personnalisation et exécution de prompts pour intelligences artificielles génératives, développée dans un contexte bancaire professionnel.

> 🔗 Frontend associé : [promptbcp-frontend](https://github.com/mbarkimahmoud-maker/promptbcp-frontend)

---

## ✨ Fonctionnalités

- 📄 **Upload de fichiers Word** — extraction automatique du texte et détection des variables `{{ }}`
- ✍️ **Création manuelle** de prompts avec variables personnalisées
- 📋 **Formulaire dynamique** — remplissage guidé des variables sans voir le prompt brut
- 🤖 **IA intégrée** — génération de réponses via **Google Gemini** et **Groq (Llama)**
- 🌐 **IAs externes** — envoi vers ChatGPT, Claude, Perplexity, Mistral, DeepSeek, Grok, Copilot
- 📥 **Export multi-format** — Word (.docx), PDF, TXT, copie presse-papier
- ✉️ **Envoi par email** — multi-destinataires, mise en forme HTML préservée (tableaux inclus)
- 🎨 **Interface professionnelle** — charte graphique personnalisée, rendu Markdown stylé

---

## 🛠️ Stack technique

| Composant | Technologie |
|---|---|
| Backend / API | Laravel 11 (PHP 8.3) |
| Base de données | MySQL 8.0 |
| Traitement Word | PHPWord |
| Génération PDF | Dompdf |
| IA générative | Google Gemini API + Groq API |
| Emailing | Laravel Mail (SMTP) |
| Tests | PHPUnit |
| Déploiement | Docker (PHP-FPM, Nginx, MySQL) |

---

## 🚀 Démarrage rapide avec Docker

### Prérequis
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installé et lancé

### Installation (une seule fois)

```bash
# 1. Cloner le repository backend
git clone https://github.com/mbarkimahmoud-maker/promptbcp-backend.git
cd promptbcp-backend

# 2. Copier et configurer le fichier d'environnement
copy .env.docker.example .env.docker
# Ouvrir .env.docker et remplir vos propres clés API (voir section Configuration)

# 3. Copier vers .env
copy .env.docker .env

# 4. Construire les images Docker (peut prendre 10-20 minutes la première fois)
docker-compose build

# 5. Démarrer l'application
docker-compose up

# 6. Dans un nouveau terminal, lancer les migrations (première fois uniquement)
docker-compose exec backend php artisan migrate
```

> 💡 Sous macOS/Linux, remplacez `copy` par `cp`.

### Utilisation quotidienne

```bash
# Démarrer
docker-compose up

# Accéder à l'API
http://localhost:8000

# Arrêter
docker-compose down
```

### Installation locale (sans Docker)

```bash
git clone https://github.com/mbarkimahmoud-maker/promptbcp-backend.git
cd promptbcp-backend

composer install
copy .env.example .env
php artisan key:generate

# Configurer la base de données et les clés API dans .env

php artisan migrate
php artisan serve
```

---

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
```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=promptbcp
DB_USERNAME=root
DB_PASSWORD=votre_mot_de_passe
```

---

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
```

---

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
```

---

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
