# Atelier 6 : l'ADR de l'atelier 4

**Durée** : 30 min · **Individuel** · **Résultat** : `docs/decisions/0001-<slug>.md`, au format [MADR 4.0.0](https://github.com/adr/madr), commité

## Le squelette

Les titres de section sont ceux de MADR 4.0.0, en anglais. Écrivez le contenu en français.

```markdown
---
status: accepted
date: 2026-11-17
decision-makers: {vos noms}
---

# {titre court : le problème et la solution retenue}

## Context and Problem Statement

{le besoin, en deux ou trois phrases}

## Decision Drivers

* {scénario de qualité 1}
* {scénario de qualité 2}

## Considered Options

* {option A}
* {option B}

## Decision Outcome

Chosen option: "{option retenue}", because {justification}.

### Consequences

* Good, because {ce que l'on gagne}
* Bad, because {ce que l'on accepte de perdre}

### Confirmation

{comment on vérifiera que la décision est respectée : un test, une règle deptrac, une revue}

## More Information

{lien vers docs/architecture.md}
```

Le modèle officiel, avec toutes les sections : [adr-template.md](https://github.com/adr/madr/blob/4.0.0/template/adr-template.md).

## Étapes

1. **Créez le fichier** `docs/decisions/0001-<slug>.md`. Le slug résume la décision, par exemple `0001-garder-subscription-dans-le-monolithe.md`. Copiez-y le squelette.
   *Terminé quand* : le fichier existe et son titre dit la décision.
2. **Remplissez-le** avec le texte de l'atelier 4 :
   - *Context and Problem Statement* : le besoin (une autre équipe, 500 000 e-mails par annonce) ;
   - *Decision Drivers* : les scénarios de qualité qui ont compté ;
   - *Considered Options* : A et B ;
   - *Decision Outcome* : votre choix et vos 5 lignes de justification ;
   - *Consequences* : ce que vous gagnez (`Good, because`) et ce que vous acceptez de perdre (`Bad, because`) ;
   - *Confirmation* : comment on saura que la décision est respectée. Un test ? Une règle deptrac ? Une revue ?

   *Terminé quand* : chaque section contient au moins une phrase.
3. **Reliez les deux documents.** Dans `docs/architecture.md`, ajoutez un lien vers `decisions/0001-<slug>.md`. Dans l'ADR, section *More Information*, ajoutez un lien vers `../architecture.md`.
   *Terminé quand* : les deux liens s'ouvrent depuis l'aperçu Markdown de votre éditeur.
4. **Commitez** :
   ```
   git add docs/architecture.md docs/decisions
   git commit -m "Ajoute la vue C4 et l'ADR 0001"
   ```
   *Terminé quand* : `git log -1` montre votre commit.

## Bonus, s'il vous reste du temps

Dans `deptrac_hexa.yaml`, un commentaire explique pourquoi PSR-20 (`Psr\Clock`) est rangé dans `Helpers` et non dans `Vendors`. C'est une décision. Écrivez-la en ADR dans `docs/decisions/0002-<slug>.md`.
