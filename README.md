# 🛒 Store Management API

API REST de gestion de magasins développée en PHP, utilisant une architecture hexagonale et sécurisée par JWT.

## 🚀 Installation et Démarrage

### 1. Lancer les containers

Dans le dossier du projet, exécutez la commande suivante :

```bash
docker-compose up -d
```

Note : Attendez environ 20 secondes lors du premier lancement pour que la base de données MySQL s'initialise correctement.
pour vérifier

```bash
 docker logs store_api_db
```

il sera ecrit ready to connect quand ce sera bon.

2. Initialiser l'administrateur

Pour tester les routes sécurisées (POST), vous devez créer un compte administrateur. Exécutez cette commande dans votre terminal :

```bash
docker exec -it store_api_db mysql -u root -proot store_db -e "INSERT INTO users (email, password, role) VALUES ('admin@test.com', '\$2y\$10\$U5W0Xq.Z7dK2yG1mZ.X7ueY9X8eH6vB6h1.R.YxY/Z8mY/XqY/YmY', 'ROLE_ADMIN');"
```

Identifiants de test

    Email : admin@test.com

    Mot de passe : admin
