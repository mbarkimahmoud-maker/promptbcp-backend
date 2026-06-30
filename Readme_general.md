# 🤖 Prompt Manager BCP

Plateforme web de gestion, personnalisation et exécution de prompts pour intelligences artificielles génératives, développée dans un contexte bancaire professionnel.

Le projet est organisé en **deux repositories séparés** :

| Repository | Rôle | Lien |
|---|---|---|
| `promptbcp-backend` | API REST (Laravel) — logique métier, base de données, génération IA, emails, PDF/Word | [promptbcp-backend](https://github.com/mbarkimahmoud-maker/promptbcp-backend) |
| `promptbcp-frontend` | Interface utilisateur (Vue.js 3) — formulaires dynamiques, rendu, export | [promptbcp-frontend](https://github.com/mbarkimahmoud-maker/promptbcp-frontend) |

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

## 🏗️ Architecture

```
┌─────────────────────────┐         ┌──────────────────────────┐
│   promptbcp-frontend     │  HTTP   │    promptbcp-backend       │
│   Vue.js 3 + Vite          │ ──────▶ │    Laravel 11 (API REST)    │
│   Tailwind CSS              │ ◀────── │    MySQL 8.0                  │
└─────────────────────────┘         └──────────────────────────┘
        :5173                                  :8000
                                                    │
                                      ┌─────────────┴──────────────┐
                                      ▼                             ▼
                              Google Gemini API              Groq API (Llama)
```

Le frontend consomme l'API exposée par le backend ; le backend gère la persistance, l'exécution des prompts, les appels aux IA génératives, la génération de fichiers (Word/PDF) et l'envoi d'emails.

---

## 🛠️ Stack technique

| Couche | Technologie |
|---|---|
| Backend / API | Laravel 11 (PHP 8.3) |
| Frontend | Vue.js 3 + Vite + Tailwind CSS |
| Base de données | MySQL 8.0 |
| Traitement Word | PHPWord |
| Génération PDF | Dompdf |
| IA générative | Google Gemini API + Groq API |
| Emailing | Laravel Mail (SMTP) |
| Rendu Markdown | marked.js |
| Déploiement | Docker (PHP-FPM, Nginx, MySQL) |

---

## 🚀 Démarrage rapide (projet complet)

### Prérequis
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installé et lancé

### Installation

```bash
# 1. Cloner le backend (contient le docker-compose.yml orchestrant tout le projet)
git clone https://github.com/mbarkimahmoud-maker/promptbcp-backend.git
cd promptbcp-backend

# 2. Configurer l'environnement
copy .env.docker.example .env.docker
# Remplir les clés API (voir section Configuration ci-dessous)
copy .env.docker .env

# 3. Construire et démarrer les services
docker-compose build
docker-compose up

# 4. Dans un nouveau terminal, lancer les migrations (première fois uniquement)
docker-compose exec backend php artisan migrate
```

### Accès à l'application

| Service | URL |
|---|---|
| Interface (frontend) | http://localhost:5173 |
| API (backend) | http://localhost:8000 |

Pour une installation et un développement détaillés de chaque partie, voir le README dédié :
- [README backend](https://github.com/mbarkimahmoud-maker/promptbcp-backend/blob/main/README.md)
- [README frontend](https://github.com/mbarkimahmoud-maker/promptbcp-frontend/blob/main/README.md)

---

## ⚙️ Configuration

Les clés API et identifiants se configurent dans le `.env.docker` du backend.

### Clé API Gemini (gratuit)
1. Aller sur [https://aistudio.google.com/](https://aistudio.google.com/)
2. **"Get API Key"** → **"Create API Key"**
3. `GEMINI_API_KEY=votre_cle_ici`

### Clé API Groq (gratuit, rapide)
1. Aller sur [https://console.groq.com/](https://console.groq.com/)
2. **"API Keys"** → **"Create API Key"**
3. `GROQ_API_KEY=votre_cle_ici`

### Email (Gmail recommandé)
1. Aller sur [https://myaccount.google.com/apppasswords](https://myaccount.google.com/apppasswords)
2. Créer un mot de passe d'application (nécessite la vérification en 2 étapes activée)
3. Configurer `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`

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

## 🌐 API REST (résumé)

| Endpoint | Méthode | Description |
|---|---|---|
| `/api/prompts` | GET / POST | Lister / créer des prompts |
| `/api/prompts/upload` | POST | Uploader un fichier Word |
| `/api/prompts/{id}/execute` | POST | Générer le prompt final |
| `/api/executions/{id}/download` | GET | Télécharger le fichier Word |
| `/api/ai/executions/{id}/gemini` | POST | Générer avec Gemini |
| `/api/ai/executions/{id}/groq` | POST | Générer avec Groq |
| `/api/executions/{id}/generate-pdf` | POST | Générer un PDF |
| `/api/executions/{id}/send-email` | POST | Envoyer par email |

Détail complet dans le [README backend](https://github.com/mbarkimahmoud-maker/promptbcp-backend/blob/main/README.md#-api-rest).

---

## 🐳 Services Docker

| Service | Description | Port |
|---|---|---|
| `backend` | PHP-FPM (Laravel) | Interne |
| `webserver` | Nginx (API) | 8000 |
| `mysql` | MySQL 8.0 | 3307 |
| `frontend` | Nginx (build de production Vue.js) | 5173 |

---

## 📦 Repositories du projet

- **Backend** : [promptbcp-backend](https://github.com/mbarkimahmoud-maker/promptbcp-backend)
- **Frontend** : [promptbcp-frontend](https://github.com/mbarkimahmoud-maker/promptbcp-frontend)

---

## 📝 Licence

Projet développé dans le cadre d'un stage professionnel — usage interne.
