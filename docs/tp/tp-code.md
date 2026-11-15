# TP de code : hexagonal, événementiel, CQRS

**Durée** : 45 min, en binôme · **Partir de** `etape-0` · **Solution** `etape-1`

En pair programming : l'un code (le « pilote »), l'autre lit, questionne et vérifie les règles (le « navigateur »). Seul le poste du pilote a besoin de l'installation.

```
git switch -c tp-binome origin/etape-0
```

## Objectif

« Quand un livre est remisé, les abonnés sont prévenus. »

La remise vit dans **BookStore** (hexagonal, CQRS). Les abonnés vivent dans **Subscription** (un CRUD). Subscription doit réagir à la remise **sans rien connaître du modèle de BookStore** : seulement d'un contrat publié.

## Déjà en place sur `etape-0`

- **La remise.** `POST /api/books/{id}/discount` envoie une `DiscountBookCommand`. Son handler, `DiscountBookCommandHandler`, charge le livre et appelle `Book::applyDiscount()`. Rien n'est publié.
- **Le modèle à imiter : la publication d'un livre.** Lisez ces quatre fichiers dans l'ordre, c'est exactement le chemin que vous allez refaire :
  1. `src/BookStore/Domain/Model/Book.php` : le constructeur enregistre un événement interne, `BookPublished`, grâce au trait `RecordsEvents` (`$this->record(...)`). L'agrégat enregistre, il ne publie pas.
  2. `src/BookStore/Application/Command/CreateBookCommandHandler.php` : après l'enregistrement, le handler publie les événements du livre (`releaseEvents()`) sur l'`EventBusInterface`.
  3. `src/BookStore/Application/EventListener/AnnounceBookPublishedListener.php` : BookStore traduit son événement interne en **événement d'intégration**, `src/Shared/Domain/Event/Integration/BookPublished.php`, qui ne contient que des scalaires.
  4. `src/Subscription/EventListener/AnnounceBookToSubscribersListener.php` : Subscription écoute l'événement d'intégration et l'annonce à chaque abonné, dans le journal `subscription` (`var/log/subscription.log` en dev).
- **Comment on écoute un événement.** Une classe marquée `#[AsEventListener]`, avec une méthode `__invoke(UnEvenement $event)`. **C'est le type du paramètre qui abonne le listener.** Aucune configuration à toucher.
- **Les événements partent après la commande** : `MessengerEventBus` les retient jusqu'à la fin du traitement de la commande, donc après le commit.
- **Les règles d'isolation**, dans `deptrac_bc.yaml` (les contextes) et `deptrac_hexa.yaml` (les couches). **Lisez-les avant de coder.**
- **Les tests à faire passer** sont déjà écrits, et rouges. Ne les modifiez pas : lisez-les, ils précisent ce qu'on attend.

## À faire

### Étape 1 : l'événement publié (30 min)

1. **L'événement interne.** Créez `src/BookStore/Domain/Event/BookDiscounted.php`, avec des value objects comme `BookPublished` : `bookId` (`BookId`), `name` (`BookName`), `previousPrice` et `price` (`Price`). `Book::applyDiscount()` l'enregistre.
2. **Sa publication.** `DiscountBookCommandHandler` publie les événements du livre, comme `CreateBookCommandHandler`.
3. **Le contrat.** Créez l'événement d'intégration `src/Shared/Domain/Event/Integration/BookDiscounted.php`, **des scalaires seulement** : `bookId` (string), `title` (string), `previousPrice` et `price` (int, en centimes).
4. **La traduction.** Dans BookStore, `src/BookStore/Application/EventListener/AnnounceBookDiscountedListener.php` traduit l'événement interne en événement d'intégration et le publie.
5. **La réaction de Subscription.** `src/Subscription/EventListener/AnnounceDiscountToSubscribersListener.php` écoute l'événement d'intégration et écrit, pour chaque abonné, dans le journal `subscription` :
   `Announcing "{title}" now at {price} instead of {previousPrice} to {email}.`

```
docker compose exec php bin/phpunit --group tp
```

Attendu : `OK (3 tests, 5 assertions)`.

### Étape 2 : l'erreur volontaire (15 min)

1. Faites écouter à `AnnounceDiscountToSubscribersListener` **l'événement interne** `App\BookStore\Domain\Event\BookDiscounted`, au lieu de l'événement d'intégration (adaptez les accès : `$event->name->value`, etc.).
2. Lancez la vérification des contextes, et lisez le message :
   ```
   docker compose exec php vendor/bin/deptrac analyse -c deptrac_bc.yaml --fail-on-uncovered --report-uncovered --no-progress
   ```
3. Relancez `bin/phpunit --group tp`. Lequel devient rouge, et pourquoi ? Une remise faite par l'API préviendrait-elle quand même les abonnés ?
4. Remettez l'événement d'intégration. Attendu : deptrac affiche `Violations 0`, et `--group tp` est vert.

## Tests à faire passer

| Étape | Commande | Attendu |
|---|---|---|
| 1 | `bin/phpunit --group tp` | `OK (3 tests, 5 assertions)` |
| 2 | `vendor/bin/deptrac analyse -c deptrac_bc.yaml --fail-on-uncovered --report-uncovered --no-progress` | 1 violation pendant l'erreur, `Violations 0` ensuite |
| Fin | `bin/phpunit`, les deux deptrac, `vendor/bin/phpstan analyse --memory-limit=512M` | tout vert |

Les commandes sont à préfixer par `docker compose exec php`, sauf dans le shell du conteneur.

## Questions à se poser

- Pourquoi des scalaires seulement dans l'événement d'intégration ? Qu'est-ce qui casserait le jour où BookStore refactore `BookName` ou `Price` ?
- Pendant l'erreur volontaire, l'annonce partait-elle quand même ? Qu'est-ce que ça dit de la valeur d'un test d'architecture ?
- Qui est upstream, qui est downstream ? Qui possède le contrat `BookDiscounted`, et qui a le droit de le changer ?
- Si l'annonce échoue, la remise est-elle annulée ?
- Pour demain matin : que faudrait-il changer pour que Subscription devienne un service à part ?

## Pour aller plus loin : à étudier dans la solution

La branche `etape-1` va plus loin que les deux étapes du TP. Rien à coder : lisez le code et les tests, et lancez-les.

```
git switch -c tp-solution origin/etape-1
docker compose exec php bin/console doctrine:schema:update --force
docker compose exec php bin/phpunit --group tp       # OK (9 tests, 23 assertions)
```

Ce que la solution change : `git diff origin/etape-0 origin/etape-1`. Ses choix sont expliqués dans `docs/tp/tp-code-solution.md`.

### L'asynchrone : un transport et un worker

- **Où regarder** : `config/packages/messenger.yaml` (un transport `async`, une route pour `App\Shared\Domain\Event\Integration\BookDiscounted`) ; `MESSENGER_TRANSPORT_DSN` dans `.env` (`doctrine://default`, une table `messenger_messages` dans PostgreSQL) et `.env.test` (`in-memory://`).
- **Le test** : `testTheDiscountAnnouncementWaitsForTheWorker`, dans `tests/Subscription/Acceptance/AnnounceDiscountToSubscribersTest.php`. Après la remise, rien n'est annoncé ; un message attend sur le transport ; l'annonce part quand le worker (`messenger:consume`) le traite.
- **À remarquer** : la route `EventInterface: sync` a disparu. Messenger envoie un message à tous les transports qui correspondent à sa classe, ses parents et ses interfaces : avec les deux routes, l'annonce partait deux fois.
- **En dev**, pour le voir : remisez un livre, puis `bin/console messenger:stats` (un message attend), `bin/console messenger:consume async -vv --limit=1`, et `tail -n 3 var/log/subscription.log`.

### La lecture dédiée : une projection

- **Le besoin** : `GET /api/books/discounted`, la liste des livres remisés, sans charger un seul agrégat `Book`.
- **Le choix** : `Book::applyDiscount()` ne garde que le nouveau prix ; les livres remisés ne se retrouvent pas dans la table `book`. La liste est donc **alimentée par l'événement** `BookDiscounted`, dans sa propre table `discounted_book`.
- **Où regarder** : dans `src/BookStore/Infrastructure/`, `ReadModel/DiscountedBookView.php`, le port `ReadModel/DiscountedBookProjection.php`, ses deux adaptateurs `InMemory/InMemoryDiscountedBookProjection.php` et `Doctrine/DoctrineDiscountedBookProjection.php`, le projecteur `ReadModel/ProjectDiscountedBookListener.php` ; l'opération `/books/discounted` dans `ApiPlatform/Resource/BookResource.php`.
- **Les tests** : `tests/BookStore/Integration/DiscountedBookProjectionTestCase.php`, le contrat commun aux deux adaptateurs, et `testListTheDiscountedBooks` dans `tests/BookStore/Acceptance/DiscountBookTest.php`.
- **À remarquer** : le projecteur dépend du port, qui est dans `Infrastructure` ; dans `Application`, `deptrac_hexa.yaml` le refuserait. L'adaptateur Doctrine écrit avec `INSERT ... ON CONFLICT DO UPDATE`.

### Questions

- **Que se passe-t-il si le message est traité deux fois ?** Par exemple, un worker qui s'arrête après avoir envoyé les e-mails, mais avant d'avoir acquitté le message. La projection le supporte-t-elle ? Et l'annonce ?
- Si le serveur s'arrête entre le commit de la remise et l'écriture du message, que perd-on ? Qu'apporterait une outbox ?
- Deux remises successives du même livre, traitées dans le désordre : que voient les abonnés ? que montre la projection ?
- La projection est perdue : peut-on la reconstruire ? Qu'est-ce que ça dit de la différence entre CQRS et event sourcing ?
