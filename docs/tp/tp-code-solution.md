# TP de code : la solution

Pour voir tout ce que la solution change : `git diff origin/etape-0 origin/etape-1`.

## Étape 1 : l'événement publié

- `Book::applyDiscount()` enregistre `BookStore\Domain\Event\BookDiscounted` (value objects), sans rien publier.
- `DiscountBookCommandHandler` publie les événements du livre, comme `CreateBookCommandHandler`.
- `AnnounceBookDiscountedListener` (BookStore, `Application`) traduit l'événement interne en `Shared\Domain\Event\Integration\BookDiscounted` : quatre scalaires, `bookId`, `title`, `previousPrice`, `price`.
- `AnnounceDiscountToSubscribersListener` (Subscription) n'importe que ce contrat.

## Étape 2 : l'erreur volontaire

Si Subscription écoute l'événement interne, `deptrac_bc.yaml` répond :

```
DependsOnDisallowedLayer   App\Subscription\EventListener\AnnounceDiscountToSubscribersListener must not depend on App\BookStore\Domain\Event\BookDiscounted
                           You are depending on token that is a part of a layer that you are not allowed to depend on. (BookStore)
```

L'annonce, elle, part toujours : seule la règle d'architecture voit le couplage.

## Étape 3 : l'asynchrone

Dans `config/packages/messenger.yaml`, un transport `async` et une route pour l'événement d'intégration. La route `App\Shared\Domain\Event\EventInterface: sync` disparaît : Messenger envoie un message à **tous** les transports qui correspondent à sa classe, à ses parents et à ses interfaces. Avec les deux routes, l'annonce partait deux fois, une fois tout de suite et une fois par le worker. Un événement sans route est traité tout de suite, comme avant.

Le test consomme le transport en mémoire avec la vraie commande `messenger:consume`. L'option `--no-reset` garde le journal de test, que le worker viderait sinon après chaque message.

## Étape 4 : la lecture dédiée

Le domaine ne garde que le nouveau prix : les livres remisés ne se retrouvent pas dans la table `book`. Plutôt que d'ajouter au modèle un état dont il n'a pas besoin, la liste est une **projection alimentée par l'événement**, dans sa propre table `discounted_book`.

- `DiscountedBookView` et le port `DiscountedBookProjection` vivent dans `Infrastructure/ReadModel`, à côté de `BookViewFinder` : les lectures contournent le modèle.
- Le projecteur `ProjectDiscountedBookListener` dépend de ce port : il vit donc dans `Infrastructure`. Dans `Application`, `deptrac_hexa.yaml` le refuse.
- L'adaptateur Doctrine écrit en SQL (`INSERT ... ON CONFLICT (id) DO UPDATE`) : traiter deux fois le même événement donne la même ligne. La projection est idempotente, l'annonce aux abonnés ne l'est pas.
- Ce n'est pas de l'event sourcing : les événements ne sont pas conservés. Une remise antérieure à la projection n'y figure pas, et une projection perdue ne se reconstruit pas.
