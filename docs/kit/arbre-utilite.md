# Arbre d'utilité

Il classe les scénarios de qualité. Chaque feuille porte deux notes :

- **I** : importance pour le métier ;
- **D** : difficulté technique.

Notes : **H** (haute), **M** (moyenne), **B** (basse). On traite d'abord les feuilles (H, H).

## Modèle à copier

```
Utilité
├── {Attribut de qualité, ex. Performance}
│   └── {Raffinement, ex. Pics d'annonces}
│       ├── (I, D) {Scénario, ex. 500 000 e-mails partis en moins de 1 h}
│       └── (I, D) {Scénario}
├── {Attribut de qualité, ex. Disponibilité}
│   └── {Raffinement}
│       └── (I, D) {Scénario}
└── {Attribut de qualité, ex. Modifiabilité}
    └── {Raffinement}
        └── (I, D) {Scénario}
```

Exemple de feuille notée : `(H, M) La remise répond en moins de 300 ms pendant un envoi de 500 000 e-mails.`

Chaque feuille se détaille ensuite avec le [scénario de qualité](scenario-qualite.md).
