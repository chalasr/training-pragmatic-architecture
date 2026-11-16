# Atelier 4 : extraire Subscription ?

**Durée** : 35 min · **En binôme**, sur un tableau Excalidraw partagé · **Résultat** : un choix justifié en 5 lignes

## Le besoin

- Une autre équipe reprend la gestion des abonnés.
- Les annonces grossissent : 500 000 e-mails par annonce.

## Les deux options

- **A. Rester en monolithe modulaire.** Subscription reste dans l'application. Un worker de la même application envoie les e-mails. Une seule base PostgreSQL.
- **B. Subscription devient un microservice.** Sa propre base, son propre déploiement, et un broker de messages entre BookStore et Subscription.

## Ce que B doit encore payer

Extraire Subscription ne se résume pas à déplacer un dossier. B doit aussi avoir :

- sa base de données ;
- son déploiement ;
- une outbox côté BookStore, pour ne perdre aucun événement entre le commit et le broker ;
- un contrat versionné : `BookDiscounted`, `BookPublished` ;
- un consommateur idempotent : un message traité deux fois ne doit pas envoyer deux fois les e-mails ;
- une gestion de l'ordre des messages ;
- une supervision de bout en bout, de la remise jusqu'à l'e-mail.

## Étapes

1. **Évaluez A et B** avec vos scénarios de qualité d'hier (ateliers 2 et 3) : performance pendant les pics, disponibilité, taille de l'équipe, coût, etc. Pour chaque scénario, notez ce que donne A, ce que donne B, et à quel prix.
   *Terminé quand* : chaque scénario a une réponse pour A et une pour B.
2. **Choisissez** A ou B.
   *Terminé quand* : le binôme est d'accord sur une option.
3. **Justifiez votre choix en 5 lignes.** Citez les scénarios qui ont décidé.
   *Terminé quand* : les 5 lignes sont écrites sur le tableau.
4. **Listez ce que vous acceptez de perdre** avec ce choix.
   *Terminé quand* : la liste contient au moins deux éléments.

Gardez ce texte : il devient l'ADR de l'atelier 6.
