USE store_db;
INSERT INTO users (email, password, role) 
VALUES ('admin@test.com', '$2y$10$8.un9vGubIs9OkAnXv.9uOclN5UvHl.X0MvWp.fW9mN8m8eH6vB6h', 'ROLE_ADMIN')
ON DUPLICATE KEY UPDATE email=email;