# ADR au format MADR 4.0.0

Un fichier par décision, dans `docs/decisions/NNNN-<slug>.md` (ex. `0001-garder-subscription-dans-le-monolithe.md`).

Les titres de section sont ceux de [MADR 4.0.0](https://github.com/adr/madr/blob/4.0.0/template/adr-template.md), en anglais. Le contenu peut être en français. Les sections marquées *(facultatif)* peuvent être supprimées.

## Modèle à copier

```markdown
---
status: {proposed | rejected | accepted | deprecated | superseded by ADR-NNNN}
date: {AAAA-MM-JJ}
decision-makers: {qui décide}
consulted: {qui est consulté}
informed: {qui est informé}
---

# {titre court : le problème et la solution retenue}

## Context and Problem Statement

{le contexte et le problème, en deux ou trois phrases}

## Decision Drivers

* {critère 1, ex. un scénario de qualité}
* {critère 2}

## Considered Options

* {option 1}
* {option 2}

## Decision Outcome

Chosen option: "{option 1}", because {justification}.

### Consequences

* Good, because {conséquence positive}
* Bad, because {conséquence négative}

### Confirmation

{comment vérifier que la décision est appliquée : revue, test, règle deptrac}

## Pros and Cons of the Options

### {option 1}

* Good, because {argument}
* Neutral, because {argument}
* Bad, because {argument}

### {option 2}

* Good, because {argument}
* Bad, because {argument}

## More Information

{liens vers d'autres décisions, vers la vue C4, quand revoir la décision}
```

Facultatif : la partie entre `---` en haut, *Decision Drivers*, *Consequences*, *Confirmation*, *Pros and Cons of the Options*, *More Information*. La version courte de MADR garde seulement le titre, *Context and Problem Statement*, *Considered Options*, *Decision Outcome* et *Consequences* : [adr-template-minimal.md](https://github.com/adr/madr/blob/4.0.0/template/adr-template-minimal.md).
