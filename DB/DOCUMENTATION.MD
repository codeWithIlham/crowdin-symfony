# **Documentation - Partie Base de Données (DB)**  

---

# 1. Objectifs de la base de données

La base de données du projet Crowdin a été conçue pour répondre à plusieurs objectifs fonctionnels :

- Gérer des utilisateurs authentifiés  
- Organiser des projets de traduction  
- Stocker des sources textuelles  
- Associer des traductions à chaque source  
- Garantir l’intégrité des données  
- Assurer la sécurité (hashage, unicité, relations cohérentes)

L’architecture repose sur **MySQL** et **Doctrine ORM**, avec une modélisation claire et extensible.

---

# 2. Modèle conceptuel (MCD)

Le système repose sur **4 entités principales** :

- **User**
- **Project**
- **Source**
- **Translation**

### Relations

- Un **User** possède plusieurs **Projects**  
- Un **Project** possède plusieurs **Sources**  
- Une **Source** possède plusieurs **Translations**  
- Une **Translation** appartient à **une seule Source** et **une seule langue**

---

# 3. Structure des tables

## 3.1 Table `user`

| Champ        | Type         | Contraintes |
|--------------|--------------|-------------|
| id           | INT          | PK, AI      |
| email        | VARCHAR(180) | UNIQUE      |
| roles        | JSON         | NOT NULL    |
| password     | VARCHAR(255) | NOT NULL    |
| first_name   | VARCHAR(100) | NULL        |
| last_name    | VARCHAR(100) | NULL        |
| languages    | JSON         | NULL        |
| created_at   | DATETIME     | NOT NULL    |
| updated_at   | DATETIME     | NOT NULL    |

### Points importants

- **Hashage Argon2id** (sécurité maximale)
- Email **unique**
- Stockage des langues utilisateur en JSON
- Timestamps automatiques

---

## 3.2 Table `project`

| Champ        | Type         | Contraintes |
|--------------|--------------|-------------|
| id           | INT          | PK, AI      |
| name         | VARCHAR(255) | NOT NULL    |
| description  | TEXT         | NULL        |
| user_id      | INT          | FK → user   |
| created_at   | DATETIME     | NOT NULL    |
| updated_at   | DATETIME     | NOT NULL    |

### Points importants

- Un projet appartient **à un seul utilisateur**
- Suppression d’un utilisateur → suppression des projets (cascade)

---

## 3.3 Table `source`

| Champ      | Type     | Contraintes  |
|------------|----------|--------------|
| id         | INT      | PK, AI       |
| content    | TEXT     | NOT NULL     |
| project_id | INT      | FK → project |
| created_at | DATETIME | NOT NULL     |
| updated_at | DATETIME | NOT NULL     |

### Points importants

- Une source appartient à un seul projet
- Suppression d’un projet → suppression des sources (cascade)

---

## 3.4 Table `translation`

| Champ        | Type         | Contraintes |
|--------------|--------------|-------------|
| id           | INT          | PK, AI      |
| language     | VARCHAR(10)  | NOT NULL    |
| content      | TEXT         | NOT NULL    |
| source_id    | INT          | FK → source |
| created_at   | DATETIME     | NOT NULL    |
| updated_at   | DATETIME     | NOT NULL    |

### Points importants

- Une traduction appartient à une seule source
- Une source peut avoir plusieurs traductions (fr, en, es…)
- Unicité logique : une langue par source

---

# 4. Sécurité des données

### 4.1 Hashage des mots de passe

Le projet utilise :

```
algorithm: argon2id
```

Avantages :

- Résistant aux attaques GPU
- Paramétrable (mémoire, temps)
- Conforme aux recommandations OWASP

### 4.2 Validation

- Email unique
- Mot de passe non vide
- Format email validé
- Longueur minimale des champs

### 4.3 Intégrité référentielle

Doctrine gère :

- `cascade={"remove"}`  
- `orphanRemoval=true`  
- Relations ManyToOne sécurisées

---

# 5. Migrations

Chaque modification du schéma est versionnée via :

```
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

Avantages :

- Historique complet  
- Reproductibilité  
- Déploiement fiable  

---

# 6. Tests liés à la base de données

Les tests Kernel vérifient :

### Hashage Argon2id  

→ Le hash commence par `$argon2id$`

### Deux hash différents pour le même mot de passe  

→ Preuve de l’utilisation d’un salt

### Vérification correcte du mot de passe  

→ `password_verify()` fonctionne

### Mot de passe vide interdit  

→ Warning attendu → test validé

### Relations cohérentes  

→ Création / suppression en cascade

---

# 7. Choix techniques justifiés

### Pourquoi Argon2id ?

- Plus sécurisé que bcrypt  
- Recommandé par OWASP  
- Résistant aux attaques modernes

### Pourquoi JSON pour les langues ?

- Flexible  
- Extensible  
- Pas besoin d’une table supplémentaire

### Pourquoi cascade remove ?

- Simplifie la gestion des projets  
- Évite les données orphelines

### Pourquoi Doctrine ORM ?

- Mapping objet clair  
- Relations automatiques  
- Migrations intégrées

---

# 8. Conclusion

La base de données du projet Crowdin est :

- **Solide** (relations cohérentes)  
- **Sécurisée** (Argon2id, validations)  
- **Extensible** (JSON, entités modulaires)  
- **Testée** (KernelTestCase)  
- **Alignée avec les bonnes pratiques Symfony**  

Elle constitue un socle fiable pour l’ensemble de l’application.
