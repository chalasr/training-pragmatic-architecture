# Vue C4 Contexte (Mermaid)

Le système au centre, les personnes qui l'utilisent, les systèmes externes avec lesquels il échange. Pas de technologie ici.

À coller dans [mermaid.live](https://mermaid.live), ou dans un bloc ` ```mermaid ` d'un fichier Markdown : GitHub l'affiche comme un diagramme.

## Modèle à copier

```mermaid
C4Context
  title Contexte : {nom du système}
  Person(utilisateur, "{Personne}", "{ce qu'elle fait avec le système}")
  Person(autre, "{Autre personne}", "{ce qu'elle fait}")
  System(systeme, "{Nom du système}", "{ce qu'il rend possible}")
  System_Ext(externe1, "{Système externe}", "{son rôle}")
  System_Ext(externe2, "{Autre système externe}", "{son rôle}")
  Rel(utilisateur, systeme, "{ce qu'elle fait}", "{protocole}")
  Rel(autre, systeme, "{ce qu'elle fait}", "{protocole}")
  Rel(systeme, externe1, "{ce qu'il demande}", "{protocole}")
  Rel(systeme, externe2, "{ce qu'il demande}", "{protocole}")
```

## Syntaxe

- `Person(alias, "Nom", "Description")` ; `Person_Ext(...)` pour une personne extérieure.
- `System(alias, "Nom", "Description")` ; `System_Ext(...)` pour un système externe.
- `Rel(de, vers, "Libellé", "Technologie")` ; le dernier argument est facultatif.
- Référence : [mermaid.js.org/syntax/c4.html](https://mermaid.js.org/syntax/c4.html).
