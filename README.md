# 🛒 Shop API

API REST de gestion de magasins, de stocks et de ventes développée en PHP pur.  
Architecture **Hexagonale** (Domain / Repository / Http), sécurisation par **JWT**.

---

## 🚀 Démarrage rapide

### 1. Lancer l'environnement Docker

```bash
git clone https://github.com/remyHamed/shop-api.git
cd shop-api
docker-compose up -d
```

> ⏳ Attendre ~20 secondes au premier lancement le temps que MySQL s'initialise.

---

### 2. Créer le compte administrateur

Créez un fichier `init.php` à la racine :

```php
<?php
$pdo  = new PDO('mysql:host=db;dbname=store_db', 'root', 'root');
$hash = password_hash('admin', PASSWORD_BCRYPT);
$pdo->prepare("DELETE FROM users WHERE email = 'admin@test.com'")->execute();
$pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, 'ROLE_ADMIN')")
    ->execute(['admin@test.com', $hash]);
echo "✅ Compte admin créé\n";
```

```bash
docker exec -it store_api_php php init.php
rm init.php
```

**Identifiants :** `admin@test.com` / `admin`

---

### 3. Lancer les tests unitaires

```bash
docker exec -it store_api_php ./vendor/bin/phpunit tests --testdox
```

> 49 tests, 113 assertions — logique métier complète couverte (Product, Sale, StockMovement, ProductService).

---

## 📡 Documentation des routes

> **Base URL :** `http://localhost:8080/api`  
> **Auth :** Les routes protégées attendent un header `Authorization: Bearer <token>`

---

### 🔐 Authentification

#### `POST /api/login`

```json
{
  "email": "admin@test.com",
  "password": "admin"
}
```

**Réponse 200**

```json
{
  "status": "success",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
```

---

### 👥 Utilisateurs

#### `POST /api/register` — Inscription client (public)

```json
{
  "email": "client@example.com",
  "password": "motdepasse"
}
```

**Réponse 201**

```json
{
  "message": "Compte client créé avec succès",
  "user": {
    "id": 2,
    "email": "client@example.com",
    "role": "ROLE_CLIENT"
  }
}
```

---

#### `POST /api/users/employee` — Créer un employé 🔒 `ROLE_ADMIN`

```json
{
  "email": "employe@magasin.com",
  "password": "motdepasse"
}
```

**Réponse 201**

```json
{
  "message": "Employé créé avec succès",
  "user": {
    "id": 3,
    "email": "employe@magasin.com",
    "role": "ROLE_EMPLOYEE"
  }
}
```

---

#### `GET /api/users` — Liste des utilisateurs 🔒 `ROLE_ADMIN`

_Pas de body._

**Réponse 200**

```json
[
  { "id": 1, "email": "admin@test.com", "role": "ROLE_ADMIN" },
  { "id": 2, "email": "client@example.com", "role": "ROLE_CLIENT" },
  { "id": 3, "email": "employe@magasin.com", "role": "ROLE_EMPLOYEE" }
]
```

---

### 🏪 Magasins

#### `GET /api/stores` — Liste tous les magasins (public)

_Pas de body._

**Réponse 200**

```json
[
  {
    "id": 1,
    "name": "Magasin Centre",
    "address": "12 rue de la Paix",
    "city": "Paris",
    "created_at": "2025-01-15 10:00:00"
  }
]
```

---

#### `POST /api/stores` — Créer un magasin 🔒 `ROLE_EMPLOYEE`

```json
{
  "name": "Magasin Nord",
  "address": "5 avenue du Général",
  "city": "Lyon"
}
```

> `address` est optionnel.

**Réponse 201**

```json
{
  "message": "Magasin créé avec succès",
  "store": {
    "id": 2,
    "name": "Magasin Nord",
    "address": "5 avenue du Général",
    "city": "Lyon",
    "created_at": "2025-06-10 14:22:00"
  }
}
```

---

### 📦 Produits

#### `GET /api/stores/{storeId}/products` — Produits d'un magasin (public)

_Pas de body._

**Réponse 200**

```json
[
  {
    "id": 1,
    "store_id": 1,
    "name": "Café Arabica 250g",
    "description": "Café de spécialité",
    "price": 12.5,
    "stock": 48,
    "created_at": "2025-06-01 09:00:00"
  }
]
```

---

#### `POST /api/products` — Créer un produit 🔒 `ROLE_EMPLOYEE`

```json
{
  "store_id": 1,
  "name": "Thé vert Bio",
  "description": "Sencha japonais",
  "price": 8.9
}
```

> `description` est optionnel. Le stock initial est automatiquement à `0`.

**Réponse 201**

```json
{
  "message": "Produit créé avec succès",
  "product": {
    "id": 3,
    "store_id": 1,
    "name": "Thé vert Bio",
    "description": "Sencha japonais",
    "price": 8.9,
    "stock": 0,
    "created_at": "2025-06-10 15:00:00"
  }
}
```

---

### 📥 Stock

#### `POST /api/products/{productId}/stock/add` — Ajouter du stock 🔒 `ROLE_EMPLOYEE`

Utilisé pour un réapprovisionnement, un retour, un transfert entrant, etc.

```json
{
  "quantity": 50,
  "reason": "Livraison fournisseur"
}
```

> `reason` est optionnel (défaut : `"Réapprovisionnement"`).

**Réponse 200**

```json
{
  "message": "Stock mis à jour",
  "product": {
    "id": 3,
    "store_id": 1,
    "name": "Thé vert Bio",
    "stock": 50,
    "price": 8.90,
    ...
  }
}
```

---

#### `GET /api/products/{productId}/movements` — Historique des mouvements (public)

_Pas de body._

**Réponse 200**

```json
[
  {
    "id": 1,
    "product_id": 3,
    "store_id": 1,
    "type": "IN",
    "quantity": 50,
    "reason": "Livraison fournisseur",
    "created_at": "2025-06-10 15:05:00"
  },
  {
    "id": 2,
    "product_id": 3,
    "store_id": 1,
    "type": "OUT",
    "quantity": 2,
    "reason": "Vente",
    "created_at": "2025-06-10 16:00:00"
  }
]
```

> `type` vaut `"IN"` (entrée) ou `"OUT"` (sortie / vente).

---

### 💰 Ventes

#### `POST /api/products/{productId}/sell` — Enregistrer une vente 🔒 `ROLE_EMPLOYEE`

Décrémente le stock **et** comptabilise la vente en une seule opération atomique.

```json
{
  "quantity": 2
}
```

**Réponse 201**

```json
{
  "message": "Vente enregistrée",
  "sale": {
    "id": 1,
    "product_id": 3,
    "store_id": 1,
    "quantity": 2,
    "unit_price": 8.9,
    "total_amount": 17.8,
    "sold_at": "2025-06-10 16:00:00"
  }
}
```

**Réponse 422** si stock insuffisant :

```json
{ "error": "Stock insuffisant. Disponible : 1, demandé : 2." }
```

---

#### `GET /api/stores/{storeId}/sales` — Historique des ventes d'un magasin (public)

_Pas de body._

**Réponse 200**

```json
[
  {
    "id": 1,
    "product_id": 3,
    "store_id": 1,
    "quantity": 2,
    "unit_price": 8.9,
    "total_amount": 17.8,
    "sold_at": "2025-06-10 16:00:00"
  }
]
```

---

#### `GET /api/stores/{storeId}/revenue` — Chiffre d'affaires total (public)

_Pas de body._

**Réponse 200**

```json
{
  "store_id": 1,
  "total_revenue": 1245.6
}
```

---

## 🗂 Architecture

```
src/
├── Domain/
│   ├── Entity/         # Entités métier : User, Store, Product, Sale, StockMovement
│   └── Service/        # Logique métier pure : UserService, StoreService, ProductService
├── Repository/
│   ├── Interfaces/     # Contrats : *RepositoryInterface
│   └── Persistence/    # Implémentations SQL : Sql*Repository (PDO)
├── Http/
│   ├── Controller/     # AuthController, StoreController, UserController, ProductController
│   └── Middleware/     # AuthMiddleware (vérification JWT + rôle)
└── Security/
    └── JwtHandler.php  # Génération et décodage des tokens JWT
```

**Stack :** PHP 8.2 · MySQL · FastRoute · PHP-DI · Firebase JWT · PHPUnit 10

---

## 🔑 Matrice des rôles

| Rôle            | Routes accessibles                                                                                                                                                |
| --------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Public          | `POST /login`, `POST /register`, `GET /stores`, `GET /stores/{id}/products`, `GET /stores/{id}/sales`, `GET /stores/{id}/revenue`, `GET /products/{id}/movements` |
| `ROLE_CLIENT`   | Identique au public (compte personnel)                                                                                                                            |
| `ROLE_EMPLOYEE` | + `POST /stores`, `POST /products`, `POST /products/{id}/stock/add`, `POST /products/{id}/sell`                                                                   |
| `ROLE_ADMIN`    | + `GET /users`, `POST /users/employee`                                                                                                                            |
