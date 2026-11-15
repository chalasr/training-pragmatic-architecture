# La librairie : projet de travaux pratiques, formation Architecture pragmatique

Une librairie en ligne : un catalogue de livres, des avis, des commandes, et des abonnés prévenus des nouveautés. C'est une API (API Platform 4.3, Symfony 8.1, PHP 8.5, PostgreSQL), servie sur deux transports : `/api` pour les navigateurs, `/mcp` pour les agents IA.

Elle est organisée en **monolithe modulaire** : un seul déployable, deux bounded contexts dont la frontière est vérifiée par un outil.

- **BookStore** (`src/BookStore`) porte l'architecture : **hexagonale** et **CQRS**.
  - `Domain` : le modèle (agrégats, value objects, événements) et les ports (interfaces des repositories).
  - `Application` : les cas d'usage, une commande et un handler chacun. Une commande ne renvoie rien.
  - `Infrastructure` : tout ce qui parle au monde extérieur (Doctrine, API Platform, Symfony), et un adaptateur en mémoire pour chaque repository.
  - Les écritures passent par le modèle ; les lectures le contournent, par une projection (`BookViewFinder`).
- **Subscription** (`src/Subscription`) est **volontairement un simple CRUD** : une entité Doctrine exposée directement par API Platform. Une architecture se paie là où elle rapporte, pas sur chaque table.
- **Shared** (`src/Shared`) contient le socle commun, dont les **événements d'intégration** (`src/Shared/Domain/Event/Integration`) : le seul langage que les deux contextes partagent.

Deux règles sont vérifiées par [deptrac](https://github.com/deptrac/deptrac) : `deptrac_bc.yaml` empêche BookStore et Subscription de se connaître, `deptrac_hexa.yaml` garde les couches dans l'ordre.

L'énoncé du TP de code : [docs/tp/tp-code.md](docs/tp/tp-code.md).

## D'où vient ce projet

Ce dépôt part d'[apip-ddd](https://github.com/mtarld/apip-ddd), de [Mathias Arlaud](https://github.com/mtarld) avec l'aide de [Robin Chalas](https://github.com/chalasr), sous licence MIT (voir [LICENSE](LICENSE)). L'historique d'origine est conservé. C'est une approche, discutable et discutée en formation, pas un modèle à recopier tel quel.

## Prérequis

- **Git**.
- **Docker installé et démarré** (Docker Desktop sous macOS et Windows ; sous Windows, backend WSL 2). C'est tout : pas besoin de PHP ni de PostgreSQL sur votre machine.
- Vérification : `git --version` et `docker run --rm hello-world`.
- Un éditeur de code : PhpStorm, ou VS Code avec l'extension **PHP Intelephense** (qui fonctionne sans PHP installé).
- Chaque participant fait le TP sur sa propre machine : l'installation est indispensable pour tout le monde, à faire avant la formation.

## Installation (une seule fois, avant la formation)

```
git clone https://github.com/chalasr/training-pragmatic-architecture.git
cd training-pragmatic-architecture
docker compose build
docker compose up -d --wait
docker compose exec php bin/console doctrine:schema:update --force
docker compose exec php bin/phpunit --exclude-group tp
```

- `docker compose build` prend quelques minutes la première fois (téléchargement de l'image PHP 8.5 et compilation des extensions). Lancez-le avant la formation, pas pendant.
- `docker compose up -d --wait` installe aussi les dépendances PHP au premier démarrage (dossier `vendor/`), puis rend la main quand tout est prêt.
- **C'est bon si** la dernière commande se termine par une ligne verte `OK (168 tests, 499 assertions)`. Les tests du TP (`--group tp`) sont exclus : ils sont rouges tant que le TP n'est pas fait.

L'API se visite sur https://localhost/api (le certificat est auto-signé : acceptez-le dans le navigateur).

### Si un port est déjà pris

L'application utilise les ports 80 et 443, la base le port 5432. Si `docker compose up` répond `port is already allocated`, choisissez d'autres ports, dans le même terminal :

```
export HTTP_PORT=8080 HTTPS_PORT=8443 HTTP3_PORT=8443 POSTGRES_PORT=15432
docker compose up -d --wait
```

Sous PowerShell : `$env:HTTP_PORT="8080"; $env:HTTPS_PORT="8443"; $env:HTTP3_PORT="8443"; $env:POSTGRES_PORT="15432"`. L'API est alors sur https://localhost:8443/api. Refaites l'`export` dans chaque nouveau terminal avant un `docker compose up`.

## Au quotidien

```
docker compose up -d --wait         # démarrer
docker compose exec php bash        # ouvrir un shell dans le conteneur
docker compose stop                 # arrêter
```

Dans le shell du conteneur, les commandes ci-dessous se lancent sans le préfixe `docker compose exec php`.

## Vérifier son travail

```
docker compose exec php bin/phpunit                          # tous les tests
docker compose exec php bin/phpunit --testdox                # les tests, lus comme des phrases
docker compose exec php bin/phpunit --filter DiscountBook    # les tests dont le nom contient DiscountBook
docker compose exec php bin/phpunit --group tp               # les tests du TP
docker compose exec php vendor/bin/deptrac analyse -c deptrac_bc.yaml --fail-on-uncovered --report-uncovered --no-progress
docker compose exec php vendor/bin/deptrac analyse -c deptrac_hexa.yaml --fail-on-uncovered --report-uncovered --no-progress
docker compose exec php vendor/bin/phpstan analyse --memory-limit=512M
```

- Les tests utilisent leur propre base (`app_test`), créée au premier lancement : ils ne touchent pas à vos données de dev.
- `deptrac_bc.yaml` vérifie les **contextes** (BookStore, Subscription, Shared), `deptrac_hexa.yaml` les **couches** (Domain, Application, Infrastructure).
- Le style et les refactorings automatiques, comme en CI : `vendor/bin/php-cs-fixer fix --dry-run --diff` et `vendor/bin/rector process --dry-run` (sans `--dry-run`, ils corrigent).

Le fichier `castor.php` regroupe les mêmes commandes pour [Castor](https://castor.jolicode.com) ; il n'est pas nécessaire de l'installer.

## Déroulé du TP

| Branche | Contenu |
|---|---|
| `etape-0` | Le point de départ : l'énoncé du TP et les tests à faire passer |
| `etape-1` | La solution, avec deux étapes de plus à étudier : l'asynchrone et une projection de lecture |

Travaillez dans votre propre branche (le préfixe `origin/` est nécessaire : après le clone, seule `etape-0` existe en local) :

```
git switch -c tp origin/etape-0
```

En retard ou bloqué ? Regardez ce que la solution change, `git diff origin/etape-0 origin/etape-1`, ou repartez-en : `git switch -c tp-solution origin/etape-1`.

Après un changement de branche, remettez la base de dev à jour : `docker compose exec php bin/console doctrine:schema:update --force`.

## En cas de problème

- **`Cannot connect to the Docker daemon`** : Docker n'est pas démarré. Lancez-le et attendez qu'il soit prêt.
- **`port is already allocated`** : voir « Si un port est déjà pris ».
- **`service "php" is not running`** : lancez d'abord `docker compose up -d --wait`.
- **Lenteur sous Windows** : clonez le dépôt dans le système de fichiers WSL (`~/` sous Ubuntu) plutôt que dans `C:\Users\...`.
- **Proxy ou réseau d'entreprise qui bloque Docker Hub, GitHub ou Packagist** : prévenez le formateur avant la formation.
