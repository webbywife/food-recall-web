-- ============================================================
-- Migration v2 — Spec alignment: place, brand, added ingredients
-- Run: mysql -u root -p food_recall_db < db/migration_v2.sql
-- ============================================================

USE food_recall_db;

-- Place of consumption per food item
ALTER TABLE recall_food_items
  ADD COLUMN place_of_consumption VARCHAR(100) DEFAULT NULL AFTER meal_time;

-- ============================================================
-- Added Ingredients Table
-- Tracks cooking oil, sugar, condiments etc. added to food
-- Nutrients stored per ingredient item (already calculated)
-- ============================================================

CREATE TABLE IF NOT EXISTS `food_added_ingredients` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `food_item_id` INT NOT NULL,
  `ingredient_name` VARCHAR(200) NOT NULL,
  `fct_food_id` INT DEFAULT NULL,
  `amount_desc` VARCHAR(100),
  `amount_grams` DECIMAL(8,2) NOT NULL DEFAULT 0,
  `energy_kcal` DECIMAL(8,2) DEFAULT 0,
  `protein_g` DECIMAL(8,2) DEFAULT 0,
  `fat_g` DECIMAL(8,2) DEFAULT 0,
  `carbs_g` DECIMAL(8,2) DEFAULT 0,
  `fiber_g` DECIMAL(8,2) DEFAULT 0,
  `sort_order` INT DEFAULT 0,
  FOREIGN KEY (`food_item_id`) REFERENCES `recall_food_items`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`fct_food_id`) REFERENCES `fct_foods`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
