CREATE DATABASE IF NOT EXISTS prolease
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE prolease;

-- Таблица клиентов / пользователей сайта
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  phone VARCHAR(50) DEFAULT NULL,
  inn VARCHAR(12) DEFAULT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Таблица договоров
CREATE TABLE IF NOT EXISTS applications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT DEFAULT NULL,
  asset_price DECIMAL(12,2) NOT NULL,
  advance_payment DECIMAL(12,2) DEFAULT 0,
  term_months INT NOT NULL DEFAULT 12,
  annual_rate DECIMAL(5,2) NOT NULL DEFAULT 15,
  buyout_percent DECIMAL(5,2) DEFAULT 10,
  monthly_payment DECIMAL(12,2) NOT NULL,
  total_paid DECIMAL(12,2) NOT NULL,
  status ENUM('pending','active','closed') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Таблица товаров (оборудование)
CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  category VARCHAR(100) DEFAULT NULL,
  price DECIMAL(12,2) NOT NULL,
  status ENUM('available','leased','unavailable') DEFAULT 'available',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Демо: пользователи (пароль: demo123)
INSERT INTO users (name, email, phone, inn, password_hash) VALUES
  ('ООО «СтройМаш»',    'stroy@example.com',   '+7 (495) 111-22-33', '7701234567', '$2y$10$YF1J4qKjG1aRkX5zQ9xK3eR2vL8mN0pO1qR2sT3uV4wX5yZ6aB7cD'),
  ('ИП Петров И.А.',    'petrov@example.com',  '+7 (495) 222-33-44', '7702345678', '$2y$10$YF1J4qKjG1aRkX5zQ9xK3eR2vL8mN0pO1qR2sT3uV4wX5yZ6aB7cD'),
  ('ЗАО «ТехноПром»',   'techno@example.com',  '+7 (495) 333-44-55', '7703456789', '$2y$10$YF1J4qKjG1aRkX5zQ9xK3eR2vL8mN0pO1qR2sT3uV4wX5yZ6aB7cD'),
  ('ООО «АгроСервис»',  'agro@example.com',    '+7 (495) 444-55-66', '7704567890', '$2y$10$YF1J4qKjG1aRkX5zQ9xK3eR2vL8mN0pO1qR2sT3uV4wX5yZ6aB7cD'),
  ('ПАО «СтальИнвест»', 'steel@example.com',   '+7 (495) 555-66-77', '7705678901', '$2y$10$YF1J4qKjG1aRkX5zQ9xK3eR2vL8mN0pO1qR2sT3uV4wX5yZ6aB7cD');

-- Демо: товары
INSERT INTO products (name, category, price, status) VALUES
  ('Экскаватор Komatsu PC200',  'Строительная техника',  8500000, 'available'),
  ('Фронтальный погрузчик SDLG', 'Строительная техника',  3200000, 'leased'),
  ('Навесное оборудование John Deere', 'Сельхозтехника',  1800000, 'available'),
  ('Компрессор Atlas Copco',      'Оборудование',         950000,  'leased'),
  ('Сварочный аппарат Lincoln',   'Оборудование',         420000,  'available'),
  ('Генератор SDMO 200 кВт',      'Энергетика',           1350000, 'unavailable');

-- Демо: договоры
INSERT INTO applications (user_id, asset_price, advance_payment, term_months, annual_rate, buyout_percent, monthly_payment, total_paid, status) VALUES
  (1, 2500000, 250000, 12, 18, 10, 85000, 1020000, 'active'),
  (2, 1800000, 180000, 12, 16, 10, 62000, 744000,  'active'),
  (3, 3200000, 320000, 24, 20, 10, 110000, 660000, 'active'),
  (4, 950000,  95000,  12, 15, 10, 34000,  408000, 'closed'),
  (5, 4100000, 410000, 6,  14, 10, 142000, 852000, 'active');
