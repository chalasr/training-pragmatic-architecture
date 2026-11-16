# Vue C4 Conteneurs (Mermaid)

Ce qui se déploie et se lance à part : applications, bases de données, files de messages, workers. Chaque conteneur a une technologie.

À coller dans [mermaid.live](https://mermaid.live), ou dans un bloc ` ```mermaid ` d'un fichier Markdown : GitHub l'affiche comme un diagramme.

## Modèle à copier

```mermaid
C4Container
  title Conteneurs : {nom du système}
  Person(utilisateur, "{Personne}", "{ce qu'elle fait}")
  System_Ext(externe, "{Système externe}", "{son rôle}")
  Container_Boundary(systeme, "{Nom du système}") {
    Container(app, "{Application}", "{technologie}", "{ce qu'elle fait}")
    Container(worker, "{Worker}", "{technologie}", "{ce qu'il traite}")
    ContainerQueue(file, "{File de messages}", "{technologie}", "{ce qu'elle transporte}")
    ContainerDb(db, "{Base de données}", "{technologie}", "{ce qu'elle stocke}")
  }
  Rel(utilisateur, app, "{ce qu'elle fait}", "{protocole}")
  Rel(app, db, "{lit et écrit}", "{protocole}")
  Rel(app, file, "{publie}", "{protocole}")
  Rel(worker, file, "{consomme}", "{protocole}")
  Rel(worker, externe, "{ce qu'il demande}", "{protocole}")
```

## Syntaxe

- `Container(alias, "Nom", "Technologie", "Description")` ; `Container_Ext(...)` pour un conteneur externe.
- `ContainerDb(...)` pour une base de données, `ContainerQueue(...)` pour une file de messages.
- `Container_Boundary(alias, "Nom") { ... }` regroupe les conteneurs d'un même système.
- `Rel(de, vers, "Libellé", "Technologie")`.
- Référence : [mermaid.js.org/syntax/c4.html](https://mermaid.js.org/syntax/c4.html).
