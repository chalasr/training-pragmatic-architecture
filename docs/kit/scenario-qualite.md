# Scénario de qualité

Un scénario rend un attribut de qualité mesurable. Une ligne par partie.

| Partie | À remplir | Exemple |
|---|---|---|
| 1. Source | Qui ou quoi déclenche ? | Un libraire |
| 2. Stimulus | Ce qui arrive | remise un livre suivi par 500 000 abonnés |
| 3. Artefact | La partie du système touchée | l'envoi des annonces |
| 4. Environnement | Dans quelles conditions | un samedi, en pleine charge |
| 5. Réponse | Ce que le système fait | la remise est enregistrée, les e-mails partent en arrière-plan |
| 6. Mesure de la réponse | Le chiffre qui dit « réussi » | réponse de l'API en moins de 300 ms ; tous les e-mails partis en moins de 1 h |

**Qui fournit la mesure** : {la personne ou l'outil qui donne le chiffre, et où on le lit : supervision, logs, tableau de bord, test de charge}

## Modèle à copier

```markdown
### {Nom court du scénario}

- **Source** :
- **Stimulus** :
- **Artefact** :
- **Environnement** :
- **Réponse** :
- **Mesure de la réponse** :
- **Qui fournit la mesure** :
```
