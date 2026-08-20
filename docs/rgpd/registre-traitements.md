# Registre des activités de traitement — Watchly

**Responsable du traitement :** Gabriel MARTIN (particulier)
**Contact :** gabrielmartin13009@gmail.com
**Délégué à la protection des données :** non désigné — désignation non obligatoire au regard de l'article 37 du RGPD (traitements de faible volume, sans données sensibles ni suivi systématique à grande échelle)
**Dernière mise à jour :** 20 août 2026

Watchly est une application web de gestion de collection cinématographique développée
dans le cadre de la formation CDA (IPSSI, session Novembre 2025). Le présent registre
est tenu conformément à l'article 30 du RGPD.

---

## 1. Traitements

### T1 — Gestion des comptes utilisateurs

| | |
|---|---|
| **Finalité** | Créer et authentifier un compte permettant l'accès aux fonctionnalités personnalisées |
| **Base légale** | Exécution du contrat (art. 6.1.b) — les CGU acceptées à l'inscription |
| **Personnes concernées** | Utilisateurs inscrits |
| **Catégories de données** | Adresse e-mail, nom d'utilisateur, mot de passe haché, rôles, horodatage du consentement, dates de création et de modification |
| **Destinataires** | Responsable du traitement uniquement |
| **Durée de conservation** | Jusqu'à la suppression du compte à l'initiative de l'utilisateur |
| **Transferts hors UE** | Aucun |

### T2 — Profil public

| | |
|---|---|
| **Finalité** | Permettre à un utilisateur de se présenter et d'être identifié par les autres membres |
| **Base légale** | Exécution du contrat (art. 6.1.b) |
| **Personnes concernées** | Utilisateurs inscrits |
| **Catégories de données** | Nom d'utilisateur, biographie, avatar (image redimensionnée en WebP) |
| **Destinataires** | Tous les visiteurs de l'application — le profil est public ; l'adresse e-mail n'y figure jamais |
| **Durée de conservation** | Jusqu'à la suppression du compte |
| **Transferts hors UE** | Aucun |

### T3 — Collection, notes et avis

| | |
|---|---|
| **Finalité** | Enregistrer les films à voir, vus et favoris d'un utilisateur, ses notes et ses avis |
| **Base légale** | Exécution du contrat (art. 6.1.b) |
| **Personnes concernées** | Utilisateurs inscrits |
| **Catégories de données** | Identifiants TMDB des films, statut (WATCHLIST / WATCHED), indicateur favori, note de 1 à 5, avis textuel, dates d'ajout et de visionnage |
| **Destinataires** | L'utilisateur pour sa collection ; les avis sont visibles publiquement sur la fiche du film |
| **Durée de conservation** | Jusqu'à la suppression du contenu ou du compte (suppression en cascade) |
| **Transferts hors UE** | Aucun |

### T4 — Listes et commentaires

| | |
|---|---|
| **Finalité** | Permettre la création de listes thématiques et leur commentaire par la communauté |
| **Base légale** | Exécution du contrat (art. 6.1.b) |
| **Personnes concernées** | Utilisateurs inscrits |
| **Catégories de données** | Titre, description et visibilité de la liste, films qu'elle contient, contenu des commentaires, signalements de commentaires |
| **Destinataires** | Tous les visiteurs pour les listes publiques ; l'auteur seul pour les listes privées |
| **Durée de conservation** | Jusqu'à la suppression du contenu ou du compte (suppression en cascade) |
| **Transferts hors UE** | Aucun |

### T5 — Sécurité et prévention des abus

| | |
|---|---|
| **Finalité** | Authentifier les requêtes API et limiter les inscriptions automatisées |
| **Base légale** | Intérêt légitime (art. 6.1.f) — protection du service contre la fraude et les attaques par force brute |
| **Personnes concernées** | Utilisateurs inscrits et visiteurs |
| **Catégories de données** | Jeton JWT (stocké côté navigateur), adresse IP utilisée comme clé du compteur de limitation de débit |
| **Destinataires** | Responsable du traitement uniquement |
| **Durée de conservation** | Jeton : durée de la session, effacé à la déconnexion. Compteur d'inscriptions : fenêtre glissante d'une heure (3 inscriptions maximum par IP) |
| **Transferts hors UE** | Aucun |

---

## 2. Sous-traitants et tiers

| Tiers | Rôle | Données transmises |
|---|---|---|
| TMDB (The Movie Database) | Fourniture des métadonnées de films | Aucune donnée personnelle. Seuls des identifiants ou titres de films sont envoyés dans les requêtes. |

Aucun autre tiers n'intervient : ni outil de mesure d'audience, ni régie publicitaire, ni
service d'envoi d'e-mails.

---

## 3. Cookies et traceurs

Watchly ne dépose aucun cookie. Le jeton d'authentification est conservé dans le
`localStorage` du navigateur sous la clé `watchly_token`. Ce stockage est strictement
nécessaire au fonctionnement du service demandé par l'utilisateur : il est donc exempté
de recueil du consentement au titre de l'article 82 de la loi Informatique et Libertés.
Aucun bandeau cookies n'est requis.

---

## 4. Mesures de sécurité

- Mots de passe hachés (algorithme automatique Symfony, jamais stockés en clair)
- Authentification par jeton JWT signé avec une paire de clés RSA (LexikJWTAuthenticationBundle)
- Contrôle d'accès par règles de pare-feu : toute route `/api` exige une authentification, sauf liste explicite de routes publiques
- En-têtes de sécurité HTTP appliqués globalement : `Content-Security-Policy`, `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`
- Limitation de débit sur l'inscription (3 par heure et par IP)
- Validation systématique des entrées côté serveur (composant Validator)
- Secrets et clés hors du dépôt Git (`.env.local`)

---

## 5. Exercice des droits des personnes

| Droit | Modalité | Mise en œuvre technique |
|---|---|---|
| Accès et portabilité (art. 15 et 20) | Bouton « Exporter mes données » dans les paramètres | `GET /api/profile/me/export` — export JSON complet |
| Rectification (art. 16) | Modification depuis le profil et les paramètres | `PUT /api/profile`, `PUT /api/profile/password`, `POST /api/profile/avatar` |
| Effacement (art. 17) | Bouton « Supprimer mon compte », confirmation par mot de passe | `DELETE /api/profile` — suppression en cascade de la collection, des avis, des listes et des commentaires |
| Effacement à l'initiative de l'administrateur | Back-office | Suppression d'un compte depuis la page Admin |
| Limitation et opposition (art. 18 et 21) | Demande par e-mail | Traitement manuel, réponse sous un mois maximum |

Les modalités sont portées à la connaissance des utilisateurs dans la politique de
confidentialité (`/confidentialite`), accessible depuis le pied de page, le formulaire de
connexion et le formulaire d'inscription.

---

## 6. Recueil du consentement

L'acceptation des CGU et de la politique de confidentialité est obligatoire à
l'inscription : une case à cocher non pré-cochée conditionne l'envoi du formulaire, et le
serveur rejette toute inscription dont le champ `consent` n'est pas à `true` (réponse
`422`). La date d'acceptation est horodatée dans la colonne `consented_at` de la table
`utilisateur`, ce qui constitue la preuve du consentement exigée par l'article 7.1 du RGPD.

---

## 7. Points à traiter avant une mise en production

- Compléter les mentions légales avec l'identité et l'adresse de l'hébergeur retenu
- Vérifier la localisation de l'hébergement et documenter tout transfert hors UE
- Servir l'application en HTTPS exclusivement
- Définir une politique de purge des comptes inactifs si la durée de conservation
  « jusqu'à suppression par l'utilisateur » devait être bornée
