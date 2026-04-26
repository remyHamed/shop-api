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

Pour tester les routes sécurisées (POST), vous devez créer un compte administrateur. Suivez cette procédure :

Créez un fichier nommé init.php à la racine du projet.

Collez le code suivant à l'intérieur :

```php
<?php
try {
    $pdo = new PDO('mysql:host=db;dbname=store_db', 'root', 'root');
    $hash = password_hash('admin', PASSWORD_BCRYPT);

    $pdo->prepare("DELETE FROM users WHERE email = 'admin@test.com'")->execute();

    $stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, 'ROLE_ADMIN')");
    $stmt->execute(['admin@test.com', $hash]);

    echo "✅ Compte administrateur créé avec succès !\n";
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
```

Exécutez le script via Docker :

```bash
docker exec -it store_api_php php init.php
```

Supprimez le fichier init.php

Identifiants de test

    Email : admin@test.com

    Mot de passe : admin
