# SFB — Annuaire des adhérents (Syndicat de la Filière Bois)

Application de gestion des adhérents du **Syndicat de la Filière Bois** : consultation,
recherche, ajout, modification et suppression des entreprises adhérentes et de leurs
représentants, plus un petit **web service** d'interrogation par SIRET.

## Contexte & historique

Application métier **en production depuis 2023**. Ce dépôt présente la **refonte technique**
de l'application : fonctionnellement identique à la version exploitée en production, elle a été
**ré-architecturée** d'un PHP procédural vers **Symfony 7**, avec Docker, Elasticsearch et Redis.

> L'historique Git de ce dépôt ne couvre que cette phase de modernisation : il ne reflète donc
> pas l'ancienneté réelle du projet. La refonte a été déployée sur l'environnement de production ;
> l'instance publique ci-dessous est une **démonstration**.

## Démonstration

Instance de démonstration **isolée** : même application qu'en production, sur une base de données
et un serveur séparés, avec des **données 100 % fictives** (entreprises et représentants générés).
Toute action (ajout, modification, suppression) est **sans impact** sur le site réel, et la base
est **réinitialisée automatiquement chaque nuit**.

**Compte de démonstration :** `demo@sfbois.com` / `demo`

## Stack technique

| Domaine | Choix |
|---|---|
| Framework | Symfony 7.4 (PHP 8.4) |
| Base de données | MySQL 8.4 (Doctrine ORM) |
| Recherche | Elasticsearch 8 (recherche instantanée par préfixe) |
| Cache | Redis 7 (via `predis`) |
| Front | Webpack Encore + Tailwind CSS 4 + Stimulus / Turbo |
| Infra de dev | Docker Compose |

## Architecture

Découpage en couches **Controller → Service → Repository → Entity**, sans logique métier dans
les contrôleurs, validation systématique des entrées via DTO + contraintes.

```
src/
├── Command/        Commandes CLI (seed, réindexation, reset démo)
├── Controller/     Couche HTTP (members, recherche, web service, sécurité)
├── Dto/            Objets de transfert validés (formulaires)
├── Entity/         User, Member, Representative
├── Form/           Types de formulaire (sur DTO)
├── Repository/     Accès données (requêtes optimisées, anti N+1)
├── Service/
│   ├── Member/     MemberManager, MemberGenerator, MemberSeeder
│   ├── Search/     Client ES, indexeur, service de recherche
│   └── WebService/ Service SIRET (réponse XML)
└── Validator/      Contrainte SIRET (validation Luhn)
```

## Fonctionnalités

- Authentification sécurisée (Symfony Security, rôles `ROLE_MEMBRE` / `ROLE_ADMIN`).
- Liste paginée des adhérents (15 / page), tri par entreprise, jointure du représentant (anti N+1).
- Ajout / modification / suppression d'un adhérent (entreprise + représentant) avec validation
  stricte (email, code postal, téléphone FR, **SIRET Luhn**) et erreurs affichées sous les champs.
- Recherche instantanée via Elasticsearch (entreprise, contact, ville, SIRET) — analyse edge-ngram.
- Web service d'interrogation par SIRET renvoyant une réponse **XML**.
- Cache Redis du comptage des adhérents (invalidé automatiquement à l'écriture).

## Installation (développement)

Prérequis : Docker, PHP 8.4, Composer, Node.js.

```bash
# 1. Dépendances
composer install
npm install

# 2. Services (MySQL, Elasticsearch, Redis)
docker compose up -d

# 3. Schéma de base de données
php bin/console doctrine:migrations:migrate -n

# 4. Compte de démonstration
php bin/console doctrine:fixtures:load -n

# 5. Données de démonstration + index Elasticsearch
php bin/console app:members:seed --fresh
php bin/console app:search:reindex

# 6. Compilation des assets
npm run dev      # ou: npm run watch

# 7. Serveur de développement
symfony server:start -d
```

L'application est disponible sur http://127.0.0.1:8000.

## Commandes utiles

| Commande | Rôle |
|---|---|
| `app:members:seed [--count=N] [--fresh]` | Génère des adhérents fictifs (par lots) |
| `app:search:reindex` | Recrée l'index Elasticsearch et indexe tous les adhérents |
| `app:demo:reset` | Réinitialise la démo (adhérents + compte démo + index) |

## Réinitialisation automatique (production de la démo)

Réinitialisation nocturne via cron (4h00, avec verrou anti-chevauchement) :

```cron
0 4 * * * /usr/bin/flock -n /tmp/sfb-demo-reset.lock -c 'cd /chemin/vers/new_sfb && /usr/bin/php bin/console app:demo:reset --env=prod >> var/log/demo-reset.log 2>&1'
```

## Qualité

```bash
vendor/bin/phpstan analyse   # analyse statique (niveau 7)
```
