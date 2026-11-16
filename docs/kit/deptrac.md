# deptrac : vérifier les dépendances

[deptrac](https://github.com/deptrac/deptrac) range les classes en **couches** (`layers`), puis vérifie que chaque couche ne dépend que des couches autorisées (`ruleset`). Ce dépôt a deux configurations.

## `deptrac_hexa.yaml` : les couches de l'hexagone

Fichier : [deptrac_hexa.yaml](../../deptrac_hexa.yaml). Analyse `src/BookStore` et `src/Shared`.

- **Domain**, **Application**, **Infrastructure** : les dossiers du même nom.
- **Vendors** : les bibliothèques d'infrastructure (API Platform, Symfony, Doctrine, etc.).
- **Attributes** : les attributs de mapping Doctrine (`Doctrine\ORM\Mapping`).
- **Helpers** : les bibliothèques sans infrastructure que le Domain peut utiliser (UUID, assertions, collections, horloge PSR-20).

Règles : le Domain ne dépend que de Helpers et Attributes ; l'Application ajoute le Domain ; l'Infrastructure ajoute l'Application, et elle seule a droit à Vendors.

## `deptrac_bc.yaml` : les bounded contexts

Fichier : [deptrac_bc.yaml](../../deptrac_bc.yaml). Analyse tout `src`.

- **BookStore**, **Subscription**, **Shared** : les dossiers du même nom.
- **Vendors** : toutes les bibliothèques.

Règles : BookStore et Subscription ne dépendent que de Shared et de Vendors. Ils ne se connaissent pas : ils échangent par les événements d'intégration de `src/Shared/Domain/Event/Integration`.

## Commandes

```
docker compose exec php vendor/bin/deptrac analyse -c deptrac_hexa.yaml --fail-on-uncovered --report-uncovered --no-progress
docker compose exec php vendor/bin/deptrac analyse -c deptrac_bc.yaml --fail-on-uncovered --report-uncovered --no-progress
```

- `--report-uncovered` affiche les dépendances vers des classes qui ne sont dans aucune couche ; `--fail-on-uncovered` les fait échouer.
- `vendor/bin/deptrac debug:layer <couche>` liste les classes d'une couche ; `vendor/bin/deptrac debug:token <classe>` dit à quelle couche une classe appartient.

## Baseline : geler les violations connues

Sur un projet existant, on gèle les violations d'aujourd'hui pour n'échouer que sur les nouvelles :

```
docker compose exec php vendor/bin/deptrac analyse -c deptrac_hexa.yaml --formatter=baseline --output=deptrac_hexa.baseline.yaml
```

Le fichier produit contient une section `skip_violations`. Importez-le en haut de la configuration :

```yaml
imports:
    - deptrac_hexa.baseline.yaml
```

Les violations gelées passent en `Skipped violations` ; `--report-skipped` les affiche. Une baseline est une dette : notez-la dans les risques (arc42, section 11) et faites-la diminuer.

Ce que deptrac ne voit pas : un handler de commande qui renvoie une valeur, un `new \DateTimeImmutable()` dans le Domain (classe du langage, dans aucune couche). Cela relève des tests et de la revue de code.
