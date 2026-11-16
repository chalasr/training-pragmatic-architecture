# Atelier 7 : écrire les règles deptrac

**Durée** : 45 min · **Individuel**, sur votre machine · **Partir de** `atelier-7`

Sur la branche `atelier-7`, les couches de `deptrac_hexa.yaml` et `deptrac_bc.yaml` sont en place, mais les règles (`ruleset:`) sont vides. À vous de les écrire.

Les commandes sont à préfixer par `docker compose exec php`, sauf dans le shell du conteneur.

## Étapes

1. **Changez de branche.** Commitez d'abord votre travail du matin, puis :
   ```
   git add -A
   git commit -m "Mon travail du TP"
   git fetch origin
   git switch -c atelier-7 origin/atelier-7
   docker compose exec php bin/console doctrine:schema:update --force
   ```
   *Terminé quand* : `git status` affiche `On branch atelier-7`.

2. **Les couches : `deptrac_hexa.yaml`.** Lisez les couches (`layers:`). Sous `ruleset:`, écrivez de quelles couches chaque couche a le droit de dépendre. Puis lancez :
   ```
   docker compose exec php vendor/bin/deptrac analyse -c deptrac_hexa.yaml --fail-on-uncovered --report-uncovered --no-progress
   ```
   *Terminé quand* : chaque violation restante vous semble être un vrai problème du code, et non une règle trop stricte.

3. **Les contextes : `deptrac_bc.yaml`.** Même travail pour les contextes. Puis lancez :
   ```
   docker compose exec php vendor/bin/deptrac analyse -c deptrac_bc.yaml --fail-on-uncovered --report-uncovered --no-progress
   ```
   *Terminé quand* : même critère qu'à l'étape 2.

4. **Décidez, pour chaque violation.** Trois choix possibles :
   - **corriger** le code ;
   - **accepter**, et tracer la décision dans un ADR (`docs/decisions/`) ;
   - **geler** dans une baseline : deptrac ignore les violations connues, et ne signale que les nouvelles.

   Pour créer une baseline :
   ```
   docker compose exec php vendor/bin/deptrac analyse -c deptrac_hexa.yaml --formatter=baseline --output=deptrac_hexa.baseline.yaml
   ```
   puis importez-la en haut de `deptrac_hexa.yaml` :
   ```yaml
   imports:
       - deptrac_hexa.baseline.yaml
   ```
   Les violations gelées apparaissent alors en `Skipped violations`. L'option `--report-skipped` les affiche.

   *Terminé quand* : chaque violation a une décision, écrite en une ligne.

5. **Ce que deptrac ne voit pas.** Ce projet contient 4 raccourcis. deptrac en voit combien ? Trouvez les autres en lisant le code : quel test ou quelle revue les attraperait ?
   *Terminé quand* : pour chaque raccourci, vous avez le fichier, la règle cassée, et le moyen de l'attraper.

6. **Comparez avec les règles d'origine** :
   ```
   git diff origin/etape-1 -- deptrac_hexa.yaml deptrac_bc.yaml
   ```
   *Terminé quand* : vous savez expliquer chaque écart entre vos règles et celles d'origine.

## Indices

<details>
<summary>Indice 1 : la forme d'un ruleset</summary>

Chaque couche liste les couches dont elle peut dépendre. Une couche qui n'est pas listée n'a droit à rien. Exemple, avec d'autres noms :

```yaml
    ruleset:
        Interface:
            - Metier
            - Outils
        Metier:
            - Outils
```

Les couches qui ne contiennent que des classes de `vendor/` (`Vendors`, `Helpers`, `Attributes`) n'ont pas besoin de ligne : deptrac n'analyse que `src/`.

</details>

<details>
<summary>Indice 2 : les questions à se poser</summary>

- `deptrac_hexa.yaml` : dans une architecture hexagonale, les dépendances vont vers le centre. Qui est au centre ? Le Domain a-t-il droit aux attributs Doctrine (`Attributes`) ? À un UUID ou à une horloge (`Helpers`) ? L'Application a-t-elle droit à l'Infrastructure ?
- `deptrac_bc.yaml` : relisez le README. Quel est le seul langage que BookStore et Subscription partagent ?

</details>

<details>
<summary>Indice 3 : les raccourcis invisibles</summary>

- Relisez la description de la couche `Application` dans le README : qu'est-ce qu'une commande ne fait pas ?
- Relisez le commentaire PSR-20 dans `deptrac_hexa.yaml` : qu'est-ce qu'un agrégat ne doit pas faire ?
- En dernier recours : `git diff origin/etape-1 origin/atelier-7 -- src`.

</details>
