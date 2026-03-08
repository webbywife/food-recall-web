-- ============================================================
-- Philippine 24-Hour Food Recall Web Application
-- Intake24 Style | FNRI Food Composition Table
-- NNS 2024 / FNRI-DOST
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+08:00";

CREATE DATABASE IF NOT EXISTS `food_recall_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `food_recall_db`;

-- ============================================================
-- CORE TABLES
-- ============================================================

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `role` ENUM('interviewer','supervisor') DEFAULT 'interviewer',
  `assignment_area` VARCHAR(100),
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `households` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `hh_id` VARCHAR(20) UNIQUE NOT NULL,
  `region` VARCHAR(50),
  `province` VARCHAR(50),
  `municipality` VARCHAR(100),
  `barangay` VARCHAR(100),
  `address` TEXT,
  `assigned_interviewer_id` INT,
  `status` ENUM('pending','in_progress','completed') DEFAULT 'pending',
  `day2_eligible` TINYINT(1) DEFAULT 0,
  `day2_scheduled_date` DATE DEFAULT NULL,
  `day2_completed` TINYINT(1) DEFAULT 0,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`assigned_interviewer_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `respondents` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `household_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `age` INT,
  `sex` ENUM('male','female'),
  `type` ENUM('wra','child_0_5','other') DEFAULT 'other',
  `recall_status` ENUM('pending','in_progress','completed') DEFAULT 'pending',
  FOREIGN KEY (`household_id`) REFERENCES `households`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `recall_sessions` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `respondent_id` INT NOT NULL,
  `household_id` INT NOT NULL,
  `interviewer_id` INT NOT NULL,
  `recall_date` DATE NOT NULL,
  `is_day2` TINYINT(1) DEFAULT 0,
  `current_pass` INT DEFAULT 1,
  `status` ENUM('quick_list','forgotten_foods','time_occasion','detail_cycle','review','completed') DEFAULT 'quick_list',
  `notes` TEXT,
  `completed_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`respondent_id`) REFERENCES `respondents`(`id`),
  FOREIGN KEY (`household_id`) REFERENCES `households`(`id`),
  FOREIGN KEY (`interviewer_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- FNRI FOOD COMPOSITION TABLE
-- ============================================================

CREATE TABLE IF NOT EXISTS `fct_foods` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `food_code` VARCHAR(20),
  `food_name` VARCHAR(200) NOT NULL,
  `local_name` VARCHAR(200),
  `food_group` VARCHAR(100),
  `description` TEXT,
  -- Nutrients per 100g edible portion
  `energy_kcal` DECIMAL(8,2) DEFAULT 0,
  `protein_g` DECIMAL(8,2) DEFAULT 0,
  `fat_g` DECIMAL(8,2) DEFAULT 0,
  `carbs_g` DECIMAL(8,2) DEFAULT 0,
  `fiber_g` DECIMAL(8,2) DEFAULT 0,
  `calcium_mg` DECIMAL(8,2) DEFAULT 0,
  `iron_mg` DECIMAL(8,2) DEFAULT 0,
  `vitamin_a_mcg` DECIMAL(8,2) DEFAULT 0,
  -- Household measures
  `measure_1_desc` VARCHAR(100),
  `measure_1_grams` DECIMAL(8,2),
  `measure_2_desc` VARCHAR(100),
  `measure_2_grams` DECIMAL(8,2),
  `measure_3_desc` VARCHAR(100),
  `measure_3_grams` DECIMAL(8,2),
  `is_active` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `recall_food_items` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `session_id` INT NOT NULL,
  `sequence_no` INT DEFAULT 1,
  `quick_list_name` VARCHAR(200),
  `meal_occasion` ENUM('early_morning','breakfast','morning_snack','lunch','afternoon_snack','dinner','evening_snack','overnight','other'),
  `meal_time` TIME,
  `fct_food_id` INT,
  `food_name` VARCHAR(200),
  `brand_name` VARCHAR(100),
  `cooking_method` VARCHAR(100),
  `amount_grams` DECIMAL(8,2),
  `household_measure_desc` VARCHAR(100),
  `household_measure_amount` DECIMAL(8,2),
  `energy_kcal` DECIMAL(8,2),
  `protein_g` DECIMAL(8,2),
  `fat_g` DECIMAL(8,2),
  `carbs_g` DECIMAL(8,2),
  `fiber_g` DECIMAL(8,2),
  `source` ENUM('quick_list','forgotten_food_probe','review_add') DEFAULT 'quick_list',
  `is_deleted` TINYINT(1) DEFAULT 0,
  `added_pass` INT DEFAULT 1,
  FOREIGN KEY (`session_id`) REFERENCES `recall_sessions`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`fct_food_id`) REFERENCES `fct_foods`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `quota` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `interviewer_id` INT NOT NULL UNIQUE,
  `target_hh` INT DEFAULT 0,
  `completed_hh` INT DEFAULT 0,
  `target_wra` INT DEFAULT 0,
  `completed_wra` INT DEFAULT 0,
  `target_children` INT DEFAULT 0,
  `completed_children` INT DEFAULT 0,
  `period_start` DATE,
  `period_end` DATE,
  FOREIGN KEY (`interviewer_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SEED: DEMO USERS
-- Passwords: supervisor01 / interviewer01 / interviewer02 = "password123"
-- ============================================================

INSERT INTO `users` (username, password_hash, full_name, role, assignment_area) VALUES
('supervisor01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Maria Santos', 'supervisor', 'National Capital Region'),
('interviewer01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jose Reyes', 'interviewer', 'NCR - Manila'),
('interviewer02', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ana Cruz', 'interviewer', 'NCR - Quezon City');

-- ============================================================
-- SEED: SAMPLE HOUSEHOLDS
-- ============================================================

INSERT INTO `households` (hh_id, region, province, municipality, barangay, address, assigned_interviewer_id) VALUES
('NCR-MNL-001', 'NCR', 'Metro Manila', 'Manila', 'Paco', '123 Rizal Ave, Paco, Manila', 2),
('NCR-MNL-002', 'NCR', 'Metro Manila', 'Manila', 'Sampaloc', '45 Espana Blvd, Sampaloc, Manila', 2),
('NCR-MNL-003', 'NCR', 'Metro Manila', 'Manila', 'Tondo', '78 Del Pan St, Tondo, Manila', 2),
('NCR-QC-001', 'NCR', 'Metro Manila', 'Quezon City', 'Batasan Hills', '10 Batasan Rd, QC', 3),
('NCR-QC-002', 'NCR', 'Metro Manila', 'Quezon City', 'Commonwealth', '55 Commonwealth Ave, QC', 3);

INSERT INTO `respondents` (household_id, name, age, sex, type) VALUES
(1, 'Lina Dela Cruz', 32, 'female', 'wra'),
(1, 'Andres Dela Cruz Jr.', 3, 'male', 'child_0_5'),
(2, 'Rosa Manalo', 27, 'female', 'wra'),
(3, 'Carla Santos', 22, 'female', 'wra'),
(3, 'Miguel Santos', 1, 'male', 'child_0_5'),
(4, 'Elena Bautista', 35, 'female', 'wra'),
(5, 'Josie Ramos', 42, 'female', 'wra');

INSERT INTO `quota` (interviewer_id, target_hh, target_wra, target_children, period_start, period_end) VALUES
(2, 20, 15, 10, '2024-01-15', '2024-02-15'),
(3, 20, 15, 10, '2024-01-15', '2024-02-15');

-- ============================================================
-- SEED: FNRI FOOD COMPOSITION TABLE (2020 ed.)
-- Values per 100g edible portion
-- ============================================================

INSERT INTO `fct_foods` (food_code, food_name, local_name, food_group, energy_kcal, protein_g, fat_g, carbs_g, fiber_g, calcium_mg, iron_mg, vitamin_a_mcg, measure_1_desc, measure_1_grams, measure_2_desc, measure_2_grams, measure_3_desc, measure_3_grams) VALUES

-- CEREALS AND CEREAL PRODUCTS
('C001','Rice, milled, cooked','Kanin','Cereals and Cereal Products',130,2.7,0.3,28.2,0.4,4,0.2,0,'1 cup',186,'1 serving bowl',280,'1 tablespoon',12),
('C002','Rice, brown, cooked','Pulang bigas, luto','Cereals and Cereal Products',111,2.6,0.9,22.9,1.8,10,0.5,0,'1 cup',195,'1 serving bowl',290,NULL,NULL),
('C003','Rice, glutinous, cooked','Malagkit na bigas, luto','Cereals and Cereal Products',97,2.0,0.2,21.9,0.3,3,0.1,0,'1 cup',240,'1 serving',150,NULL,NULL),
('C004','Bread, pandesal','Pandesal','Cereals and Cereal Products',282,8.9,3.7,54.0,1.6,48,2.0,0,'1 piece',30,'2 pieces',60,'1 large piece',50),
('C005','Bread, white sliced','Tinapay, puting hiniwa','Cereals and Cereal Products',265,8.0,2.5,52.4,1.9,80,2.5,0,'1 slice',25,'2 slices',50,NULL,NULL),
('C006','Instant noodles, cooked','Instant na pansit','Cereals and Cereal Products',138,3.5,4.5,21.3,0.5,10,1.2,0,'1 cup',220,'1 pack cooked',380,NULL,NULL),
('C007','Oatmeal, cooked','Otmil','Cereals and Cereal Products',71,2.5,1.5,12.0,1.7,10,0.8,0,'1 cup',234,'1/2 cup',117,NULL,NULL),
('C008','Cornmeal porridge, cooked','Lugaw mais','Cereals and Cereal Products',74,1.7,0.5,15.9,1.5,7,0.5,10,'1 cup',240,NULL,NULL,NULL,NULL),
('C009','Crackers, saltine','Galletas','Cereals and Cereal Products',421,9.0,9.3,74.1,2.6,35,5.0,0,'5 pieces',15,'1 pack (30g)',30,NULL,NULL),
('C010','Champorado (chocolate rice porridge)','Champorado','Cereals and Cereal Products',162,3.8,2.5,32.0,1.2,25,1.5,2,'1 cup',240,'1 bowl',350,NULL,NULL),

-- STARCHY ROOTS AND TUBERS
('T001','Sweet potato, boiled','Kamote, nilaga','Starchy Roots and Tubers',86,1.6,0.1,20.1,2.5,27,0.5,150,'1 medium',130,'1 cup cubed',133,'1 small',80),
('T002','Cassava, boiled','Kamoteng kahoy, nilaga','Starchy Roots and Tubers',143,1.4,0.3,34.7,1.8,33,0.4,0,'1 cup',206,'1 medium piece',100,NULL,NULL),
('T003','Potato, boiled','Patatas, nilaga','Starchy Roots and Tubers',87,1.9,0.1,20.1,1.8,5,0.3,0,'1 medium',150,'1 cup diced',156,NULL,NULL),
('T004','Taro, cooked','Gabi, luto','Starchy Roots and Tubers',112,1.5,0.2,26.5,4.1,18,0.6,4,'1 cup sliced',132,'1 medium corm',100,NULL,NULL),
('T005','Banana (saba), boiled','Saging na saba, nilaga','Starchy Roots and Tubers',97,1.4,0.3,22.9,2.0,7,0.4,10,'1 medium',118,'2 pieces',236,NULL,NULL),

-- FATS AND OILS
('F001','Coconut oil','Mantika ng niyog','Fats and Oils',862,0.0,100.0,0.0,0.0,0,0.0,0,'1 tablespoon',13,'1/2 cup',109,NULL,NULL),
('F002','Vegetable cooking oil','Mantika','Fats and Oils',884,0.0,100.0,0.0,0.0,0,0.0,0,'1 tablespoon',13,NULL,NULL,NULL,NULL),
('F003','Margarine','Margarina','Fats and Oils',714,0.9,80.7,0.9,0.0,24,0.1,50,'1 tablespoon',14,NULL,NULL,NULL,NULL),
('F004','Butter','Mantekilya','Fats and Oils',717,0.9,81.1,0.1,0.0,24,0.0,102,'1 tablespoon',14,NULL,NULL,NULL,NULL),

-- FISH AND SHELLFISH
('FS001','Bangus (milkfish), broiled/grilled','Bangus, inihaw','Fish and Shellfish',193,20.3,12.1,0.0,0.0,66,1.0,30,'1 medium fillet',100,'1 whole small',200,NULL,NULL),
('FS002','Tilapia, fried','Tilapia, prito','Fish and Shellfish',128,26.2,2.7,0.0,0.0,50,1.0,20,'1 medium',100,'1 small',70,NULL,NULL),
('FS003','Sardines, canned in tomato sauce','Sardinas sa kamatis, de lata','Fish and Shellfish',208,24.6,11.5,0.5,0.0,382,2.9,32,'1 can (155g)',155,'4 pieces',100,NULL,NULL),
('FS004','Dried fish (tuyo), fried','Tuyo, prito','Fish and Shellfish',285,50.5,8.2,0.0,0.0,480,3.9,25,'2 pieces',20,'5 pieces',50,NULL,NULL),
('FS005','Galunggong (round scad), fried','Galunggong, prito','Fish and Shellfish',212,22.5,13.0,0.0,0.0,90,1.8,25,'1 medium',80,'1 large',120,NULL,NULL),
('FS006','Shrimp, boiled','Hipon, nilaga','Fish and Shellfish',99,20.3,1.7,0.0,0.0,45,0.9,52,'10 medium pieces',100,'1 cup',145,NULL,NULL),
('FS007','Squid, cooked','Pusit, luto','Fish and Shellfish',92,15.6,1.4,3.1,0.0,32,0.7,10,'1 cup rings',120,NULL,NULL,NULL,NULL),
('FS008','Tuna, canned in water','Tuna, de lata','Fish and Shellfish',116,25.5,1.0,0.0,0.0,10,1.3,20,'1 can (180g)',180,'3 tablespoons',55,NULL,NULL),
('FS009','Mussels, cooked','Tahong, luto','Fish and Shellfish',172,23.8,4.5,7.4,0.0,32,6.7,48,'8 pieces',100,'1 cup',140,NULL,NULL),
('FS010','Clam, cooked','Halaan, luto','Fish and Shellfish',74,12.8,1.0,2.7,0.0,46,14.0,60,'10 pieces',100,NULL,NULL,NULL,NULL),
('FS011','Dried shrimp (hibi/alamang)','Alamang','Fish and Shellfish',290,51.0,4.0,0.0,0.0,1760,12.5,30,'1 tablespoon',15,'1/4 cup',60,NULL,NULL),
('FS012','Bangus, fried','Bangus, prito','Fish and Shellfish',220,19.0,15.5,0.0,0.0,55,1.1,28,'1 medium fillet',100,'1 small',70,NULL,NULL),

-- MEAT AND POULTRY
('M001','Chicken, broiled/fried','Manok, inihaw/prito','Meat and Poultry',215,27.3,11.1,0.8,0.0,14,1.3,30,'1 drumstick',95,'1 thigh',110,'1 breast',130),
('M002','Pork, lean, cooked','Baboy, pato, luto','Meat and Poultry',242,27.0,14.5,0.0,0.0,12,1.6,0,'2 oz (56g)',56,'1 cup chopped',140,NULL,NULL),
('M003','Beef, lean, cooked','Baka, pato, luto','Meat and Poultry',218,28.0,11.5,0.0,0.0,10,2.7,0,'2 oz (56g)',56,'1 cup chopped',140,NULL,NULL),
('M004','Pork liver, cooked','Atay ng baboy, luto','Meat and Poultry',165,26.0,4.9,3.8,0.0,10,18.0,6583,'2 oz (56g)',56,'1 slice',60,NULL,NULL),
('M005','Hotdog, cooked','Hotdog, luto','Meat and Poultry',290,11.0,26.5,2.7,0.0,12,1.0,0,'1 piece',45,'2 pieces',90,NULL,NULL),
('M006','Longanisa, cooked','Longganisa, prito','Meat and Poultry',330,15.3,28.7,3.2,0.0,15,1.2,10,'1 piece',50,'2 pieces',100,NULL,NULL),
('M007','Corned beef, canned','Corned beef, de lata','Meat and Poultry',185,24.8,9.9,0.5,0.0,14,2.7,0,'1 can (180g)',180,'2 tablespoons',40,NULL,NULL),
('M008','Chicken liver, cooked','Atay ng manok, luto','Meat and Poultry',172,24.5,7.6,0.9,0.0,11,11.0,4185,'2 oz (56g)',56,'1 cup',145,NULL,NULL),

-- EGGS
('E001','Chicken egg, whole, boiled','Itlog ng manok, nilaga','Eggs',147,12.6,10.0,0.7,0.0,50,1.8,190,'1 medium egg',50,'1 large egg',60,'2 medium eggs',100),
('E002','Salted duck egg','Itlog na maalat','Eggs',182,13.0,13.5,2.2,0.0,74,2.0,290,'1 medium egg',70,NULL,NULL,NULL,NULL),
('E003','Scrambled egg with oil','Itlog, pinirito','Eggs',149,10.1,11.5,1.6,0.0,47,1.5,180,'1 medium egg cooked',55,'2 eggs cooked',110,NULL,NULL),
('E004','Quail egg, boiled','Itlog ng pugo, nilaga','Eggs',158,13.1,11.1,0.4,0.0,64,3.6,156,'5 pieces',50,'10 pieces',100,NULL,NULL),

-- MILK AND MILK PRODUCTS
('ML001','Full cream milk, fresh/pasteurized','Gatas ng baka','Milk and Milk Products',63,3.2,3.6,4.7,0.0,117,0.1,38,'1 cup (240ml)',244,'1 glass (200ml)',200,'1 tablespoon',15),
('ML002','Evaporated milk, canned','Evap milk','Milk and Milk Products',134,6.8,7.5,10.1,0.0,261,0.2,79,'2 tablespoons',30,'1/2 cup',122,NULL,NULL),
('ML003','Powdered whole milk','Gatas, pulbos','Milk and Milk Products',496,26.3,26.7,38.4,0.0,912,0.7,300,'2 tablespoons (16g)',16,'1/4 cup (32g)',32,NULL,NULL),
('ML004','Infant formula, reconstituted','Gatas ng sanggol','Milk and Milk Products',67,1.5,3.6,7.3,0.0,53,0.8,60,'1 cup prepared (240ml)',244,'1 bottle (180ml)',180,NULL,NULL),
('ML005','Processed cheese','Keso','Milk and Milk Products',321,19.8,26.9,2.5,0.0,600,0.7,290,'1 slice (20g)',20,'2 tablespoons grated',20,NULL,NULL),
('ML006','Yogurt, plain','Yogurt','Milk and Milk Products',59,3.5,3.3,4.7,0.0,110,0.1,29,'1 cup',245,'1 small cup',150,NULL,NULL),

-- DRIED BEANS, NUTS, AND SEEDS
('L001','Mung beans, cooked','Monggo, luto','Dried Beans, Nuts, and Seeds',105,7.0,0.4,19.2,7.6,27,1.4,24,'1 cup',202,'1/2 cup',101,NULL,NULL),
('L002','Peanuts, roasted','Mani, litong','Dried Beans, Nuts, and Seeds',585,25.8,49.7,16.1,8.5,92,2.3,0,'1 tablespoon',15,'1 handful (25g)',25,'1 cup',146),
('L003','Tofu (firm), raw','Tokwa','Dried Beans, Nuts, and Seeds',76,8.1,4.2,1.9,0.3,350,1.6,0,'1/2 block (122g)',122,'1 block',244,NULL,NULL),
('L004','Soy milk','Gatas ng toyo/soya','Dried Beans, Nuts, and Seeds',43,3.6,2.0,2.9,0.6,9,0.7,0,'1 cup (240ml)',244,'1 glass',240,NULL,NULL),
('L005','Black-eyed peas, cooked','Paayap, luto','Dried Beans, Nuts, and Seeds',116,7.7,0.5,20.8,6.5,24,2.5,1,'1 cup',172,NULL,NULL,NULL,NULL),

-- VEGETABLES
('V001','Kangkong (water spinach), cooked','Kangkong, luto','Vegetables',25,3.0,0.4,3.6,2.1,73,1.7,250,'1 cup',196,'1 serving',100,NULL,NULL),
('V002','Malunggay (moringa) leaves, raw','Dahon ng malunggay','Vegetables',92,9.4,1.4,8.3,2.0,185,4.0,378,'1 cup',21,'1 tablespoon',5,NULL,NULL),
('V003','Ampalaya (bitter melon), cooked','Ampalaya, luto','Vegetables',19,1.0,0.2,3.7,2.8,19,0.3,72,'1 cup sliced',124,'1/2 medium',70,NULL,NULL),
('V004','Sitaw (long string beans), cooked','Sitaw, luto','Vegetables',35,2.1,0.3,6.4,2.9,42,1.0,34,'1 cup',125,'10 pieces',60,NULL,NULL),
('V005','Tomato, raw','Kamatis','Vegetables',18,0.9,0.2,3.9,1.2,10,0.3,42,'1 medium',123,'1 cup chopped',180,NULL,NULL),
('V006','Eggplant, cooked','Talong, luto','Vegetables',35,0.9,0.2,8.2,2.5,7,0.2,3,'1 cup cubed',96,'1 medium',82,NULL,NULL),
('V007','Squash (kalabasa), cooked','Kalabasa, luto','Vegetables',26,1.0,0.1,6.5,0.9,30,0.6,530,'1 cup cubed',116,NULL,NULL,NULL,NULL),
('V008','Okra, cooked','Okra, luto','Vegetables',22,1.9,0.2,4.5,2.0,77,0.4,36,'1 cup',160,'8 pieces',80,NULL,NULL),
('V009','Cabbage, cooked','Repolyo, luto','Vegetables',23,1.3,0.3,4.6,2.0,65,0.4,5,'1 cup',150,NULL,NULL,NULL,NULL),
('V010','Pechay (bok choy), cooked','Petsay, luto','Vegetables',13,1.8,0.2,1.8,1.0,105,0.7,180,'1 cup',170,NULL,NULL,NULL,NULL),
('V011','Onion, raw','Sibuyas','Vegetables',40,1.1,0.1,9.3,1.7,23,0.2,0,'1 medium',110,'1 tablespoon chopped',10,NULL,NULL),
('V012','Garlic, raw','Bawang','Vegetables',149,6.4,0.5,33.1,2.1,181,1.7,0,'1 clove',3,'1 tablespoon minced',9,NULL,NULL),
('V013','Carrot, raw','Karot','Vegetables',41,0.9,0.2,9.6,2.8,33,0.3,835,'1 medium',61,'1 cup grated',110,NULL,NULL),
('V014','Camote tops (talbos ng kamote), cooked','Talbos ng kamote, luto','Vegetables',44,4.2,0.8,6.5,3.2,85,2.1,560,'1 cup',132,NULL,NULL,NULL,NULL),
('V015','Pumpkin tendrils (talbos ng kalabasa)','Talbos ng kalabasa','Vegetables',23,2.5,0.3,3.2,2.0,60,1.5,200,'1 cup',67,NULL,NULL,NULL,NULL),

-- FRUITS
('FR001','Banana, ripe (lakatan/latundan)','Saging, hinog','Fruits',89,1.1,0.3,22.8,2.6,5,0.3,3,'1 medium',118,'1 large',136,'1 small',80),
('FR002','Banana (saba), ripe, raw','Saging na saba, hinog','Fruits',97,1.4,0.3,22.9,2.0,7,0.4,10,'1 medium',115,NULL,NULL,NULL,NULL),
('FR003','Mango, ripe','Mangga, hinog','Fruits',65,0.5,0.4,17.0,1.8,10,0.2,38,'1 medium',200,'1 cup sliced',165,'1 small',150),
('FR004','Papaya, ripe','Papaya, hinog','Fruits',43,0.5,0.1,10.8,1.7,20,0.1,47,'1 cup cubed',140,'1/8 medium',100,NULL,NULL),
('FR005','Watermelon','Pakwan','Fruits',30,0.6,0.2,7.6,0.4,7,0.2,28,'1 cup cubed',154,'1 wedge',280,NULL,NULL),
('FR006','Pineapple','Pinya','Fruits',50,0.5,0.1,13.1,1.4,13,0.3,3,'1 cup chunks',165,'1 slice',84,NULL,NULL),
('FR007','Calamansi juice (squeezed)','Kalamansi','Fruits',25,0.6,0.3,5.8,0.8,16,0.2,5,'1 piece',15,'5 pieces',75,NULL,NULL),
('FR008','Avocado','Abokado','Fruits',160,2.0,14.7,8.5,6.7,12,0.6,7,'1/2 medium',100,'1 cup mashed',230,NULL,NULL),
('FR009','Guava (bayabas)','Bayabas','Fruits',68,2.6,1.0,14.3,5.4,18,0.3,31,'1 medium',90,'1 cup',165,NULL,NULL),
('FR010','Orange/dalandan','Dalandan/Kahel','Fruits',47,0.9,0.1,11.7,2.4,40,0.1,11,'1 medium',180,'1 small',130,NULL,NULL),
('FR011','Coconut meat, fresh (buko)','Buko/Niyog','Fruits',354,3.3,33.5,15.2,9.0,14,2.4,0,'1 cup shredded',80,'1/4 young coconut',45,NULL,NULL),

-- NON-ALCOHOLIC BEVERAGES
('B001','Coffee, brewed (black)','Kape, itim','Non-Alcoholic Beverages',1,0.3,0.0,0.0,0.0,2,0.0,0,'1 cup (240ml)',240,NULL,NULL,NULL,NULL),
('B002','3-in-1 coffee mix, prepared','Kape, 3-in-1','Non-Alcoholic Beverages',61,0.8,1.5,11.4,0.0,8,0.1,0,'1 sachet prepared (200ml)',200,NULL,NULL,NULL,NULL),
('B003','Softdrink, cola','Soft drink/Soda','Non-Alcoholic Beverages',41,0.0,0.0,10.6,0.0,2,0.1,0,'1 glass (240ml)',240,'1 can (330ml)',330,'1 small bottle (237ml)',237),
('B004','Fruit juice, orange','Juice ng kahel','Non-Alcoholic Beverages',45,0.7,0.2,10.4,0.2,11,0.2,4,'1 glass (240ml)',240,NULL,NULL,NULL,NULL),
('B005','Coconut water (buko juice)','Tubig ng buko','Non-Alcoholic Beverages',19,0.7,0.2,3.7,1.1,24,0.3,0,'1 cup (240ml)',240,'1 young coconut',250,NULL,NULL),
('B006','Chocolate drink (Milo/cocoa), prepared','Tsokolate/Milo','Non-Alcoholic Beverages',72,3.3,2.0,11.0,1.5,110,2.5,25,'1 cup (240ml)',240,NULL,NULL,NULL,NULL),
('B007','Tea, brewed','Tsaa','Non-Alcoholic Beverages',1,0.0,0.0,0.3,0.0,0,0.0,0,'1 cup (237ml)',237,NULL,NULL,NULL,NULL),
('B008','Water, plain','Tubig','Non-Alcoholic Beverages',0,0.0,0.0,0.0,0.0,0,0.0,0,'1 glass (240ml)',240,'1 cup',237,'1 bottle (500ml)',500),
('B009','Fruit juice drink (Tang/artificial)','Juice drink','Non-Alcoholic Beverages',48,0.1,0.1,12.0,0.0,5,0.1,0,'1 glass (240ml)',240,NULL,NULL,NULL,NULL),
('B010','Sugarcane juice, fresh','Tubong','Non-Alcoholic Beverages',39,0.2,0.1,9.7,0.0,10,0.3,0,'1 glass (240ml)',240,NULL,NULL,NULL,NULL),

-- ALCOHOLIC BEVERAGES
('A001','Beer (lager)','Beer','Alcoholic Beverages',43,0.5,0.0,3.6,0.0,4,0.0,0,'1 bottle (330ml)',330,'1 can (330ml)',330,'1 glass',240),
('A002','Wine, red/white','Alak na ubas','Alcoholic Beverages',85,0.1,0.0,2.7,0.0,8,0.5,0,'1 glass (120ml)',120,NULL,NULL,NULL,NULL),
('A003','Gin/Rum/Brandy','Ginebra/Rum','Alcoholic Beverages',231,0.0,0.0,0.0,0.0,0,0.0,0,'1 shot (30ml)',30,NULL,NULL,NULL,NULL),
('A004','Tuba (coconut wine)','Tuba','Alcoholic Beverages',50,0.2,0.0,9.0,0.0,10,0.5,0,'1 glass (240ml)',240,NULL,NULL,NULL,NULL),
('A005','Lambanog (coconut vodka)','Lambanog','Alcoholic Beverages',238,0.0,0.0,0.0,0.0,0,0.0,0,'1 shot (30ml)',30,NULL,NULL,NULL,NULL),

-- COMPOSITE FILIPINO DISHES
('CD001','Adobo, chicken','Adobong manok','Composite Filipino Dishes',220,18.0,14.0,4.0,0.3,20,1.5,25,'1 cup (240g)',240,'1 serving',200,NULL,NULL),
('CD002','Adobo, pork','Adobong baboy','Composite Filipino Dishes',315,16.5,25.0,4.5,0.2,15,1.2,0,'1 cup (240g)',240,'1 serving',200,NULL,NULL),
('CD003','Sinigang, pork with vegetables','Sinigang na baboy','Composite Filipino Dishes',95,8.0,6.0,2.5,0.8,20,1.0,10,'1 cup',240,'1 bowl',350,NULL,NULL),
('CD004','Tinola, chicken','Tinolang manok','Composite Filipino Dishes',75,9.0,3.5,2.0,0.5,25,0.8,45,'1 cup',240,'1 bowl',350,NULL,NULL),
('CD005','Arroz caldo','Arroz caldo','Composite Filipino Dishes',85,5.0,3.0,11.0,0.3,20,0.5,10,'1 cup',240,'1 bowl',350,NULL,NULL),
('CD006','Lugaw (plain rice porridge)','Lugaw','Composite Filipino Dishes',60,2.0,0.5,12.5,0.3,10,0.2,0,'1 cup',240,'1 bowl',350,NULL,NULL),
('CD007','Pancit canton (stir-fried noodles)','Pansit canton','Composite Filipino Dishes',152,6.0,5.0,22.0,1.5,25,1.2,10,'1 cup',200,'1 serving',250,NULL,NULL),
('CD008','Pancit bihon','Pansit bihon','Composite Filipino Dishes',141,5.5,3.5,23.5,0.8,20,1.0,8,'1 cup',200,'1 serving',250,NULL,NULL),
('CD009','Menudo','Menudo','Composite Filipino Dishes',178,12.0,11.0,8.5,1.0,30,2.0,85,'1 cup',240,NULL,NULL,NULL,NULL),
('CD010','Kare-kare','Kare-kare','Composite Filipino Dishes',180,12.0,12.0,8.0,2.0,35,1.8,20,'1 cup',240,NULL,NULL,NULL,NULL),
('CD011','Nilaga, pork','Nilagang baboy','Composite Filipino Dishes',120,10.0,7.5,3.5,1.5,25,1.0,5,'1 cup',240,'1 bowl',350,NULL,NULL),
('CD012','Pinakbet','Pinakbet/Pakbet','Composite Filipino Dishes',95,5.5,6.0,6.5,2.5,40,1.2,120,'1 cup',200,NULL,NULL,NULL,NULL),
('CD013','Monggo guisado (sauteed mung beans)','Ginisang monggo','Composite Filipino Dishes',118,8.0,3.5,15.5,4.5,40,1.8,30,'1 cup',245,NULL,NULL,NULL,NULL),
('CD014','Sinangag (garlic fried rice)','Sinangag','Composite Filipino Dishes',178,3.5,5.5,29.0,0.4,8,0.3,0,'1 cup',180,NULL,NULL,NULL,NULL),
('CD015','Fried rice','Pritong kanin','Composite Filipino Dishes',200,4.0,7.0,30.5,0.5,10,0.5,5,'1 cup',186,NULL,NULL,NULL,NULL),
('CD016','Caldereta','Kaldereta','Composite Filipino Dishes',195,13.0,13.0,7.5,1.5,25,1.5,60,'1 cup',240,NULL,NULL,NULL,NULL),
('CD017','Mechado / Bistek Tagalog','Mechado/Bistek','Composite Filipino Dishes',185,14.0,12.0,6.0,1.0,20,2.0,30,'1 cup',240,NULL,NULL,NULL,NULL),
('CD018','Bulalo (beef marrow soup)','Bulalo','Composite Filipino Dishes',145,15.5,8.5,2.0,0.5,35,1.5,10,'1 cup',250,'1 bowl',400,NULL,NULL),
('CD019','Ginisang pechay with pork','Ginisang petsay','Composite Filipino Dishes',85,7.5,5.0,3.0,1.5,80,1.0,150,'1 cup',200,NULL,NULL,NULL,NULL),
('CD020','Dinuguan (pork blood stew)','Dinuguan','Composite Filipino Dishes',185,15.0,13.0,3.0,0.5,30,7.5,0,'1 cup',240,NULL,NULL,NULL,NULL),
('CD021','Afritada','Afritada','Composite Filipino Dishes',188,14.5,12.5,6.5,1.2,25,1.5,50,'1 cup',240,NULL,NULL,NULL,NULL),
('CD022','Pochero / Cocido','Pochero','Composite Filipino Dishes',175,14.0,10.5,8.0,2.0,35,1.8,25,'1 cup',250,'1 bowl',400,NULL,NULL),
('CD023','Laing (taro in coconut milk)','Laing','Composite Filipino Dishes',245,6.5,20.5,10.5,4.0,85,1.8,60,'1 cup',200,NULL,NULL,NULL,NULL),

-- SNACKS AND FAST FOOD
('SF001','French fries, fast food','Pritong patatas/French fries','Snacks and Fast Food',312,3.4,15.0,41.1,3.8,10,0.9,0,'1 medium order (100g)',100,'1 large order (154g)',154,NULL,NULL),
('SF002','Burger','Hamburger','Snacks and Fast Food',295,17.0,14.0,24.0,1.5,80,2.5,5,'1 regular (150g)',150,'1 small (100g)',100,NULL,NULL),
('SF003','Fried chicken, fast food','Fried chicken','Snacks and Fast Food',245,21.5,14.5,7.5,0.3,12,1.0,15,'1 piece (100g)',100,NULL,NULL,NULL,NULL),
('SF004','Pizza, cheese','Pizza','Snacks and Fast Food',266,11.4,10.4,32.5,2.3,200,1.5,50,'1 slice (80g)',80,'2 slices',160,NULL,NULL),
('SF005','Siomai, pork (steamed)','Siomai','Snacks and Fast Food',158,10.0,8.5,11.5,0.8,20,1.2,5,'4 pieces',120,'1 piece',30,NULL,NULL),
('SF006','Siopao, asado filling','Siopao','Snacks and Fast Food',250,8.5,6.5,40.5,1.2,25,1.5,5,'1 medium (80g)',80,'1 large (120g)',120,NULL,NULL),
('SF007','Banana cue (fried banana on stick)','Banana cue','Snacks and Fast Food',197,1.4,3.5,43.0,2.5,8,0.5,10,'1 piece (80g)',80,'2 pieces',160,NULL,NULL),
('SF008','Chips/junk food (potato chips)','Chichirya/chips','Snacks and Fast Food',536,7.0,35.0,52.0,4.8,14,0.8,0,'1 small bag (30g)',30,'1 handful (20g)',20,NULL,NULL),
('SF009','Puto (steamed rice cake)','Puto','Snacks and Fast Food',185,3.5,2.0,38.5,0.5,15,0.5,0,'2 pieces (70g)',70,'1 piece',35,NULL,NULL),
('SF010','Bibingka','Bibingka','Snacks and Fast Food',230,4.5,8.0,36.5,0.8,60,0.8,30,'1 piece (100g)',100,NULL,NULL,NULL,NULL),
('SF011','Biko (sticky rice cake)','Biko','Snacks and Fast Food',285,3.0,5.5,57.5,0.5,15,0.8,0,'1 piece (80g)',80,'1 cup',160,NULL,NULL),
('SF012','Halo-halo','Halo-halo','Snacks and Fast Food',145,3.5,4.0,25.0,0.8,90,0.5,20,'1 glass (300g)',300,NULL,NULL,NULL,NULL),
('SF013','Ice cream','Sorbetes/Ice cream','Snacks and Fast Food',207,3.5,11.0,24.0,0.5,128,0.1,95,'1 scoop (67g)',67,'1 cup',132,NULL,NULL),
('SF014','Kwek-kwek (deep fried quail egg)','Kwek-kwek','Snacks and Fast Food',235,10.5,15.0,16.5,0.5,35,2.5,60,'5 pieces (100g)',100,'1 piece',20,NULL,NULL),
('SF015','Fishball, fried','Fishball','Snacks and Fast Food',175,12.5,9.5,8.5,0.3,25,1.2,10,'5 pieces (75g)',75,'10 pieces',150,NULL,NULL),
('SF016','Balut (developing duck egg)','Balut','Snacks and Fast Food',188,13.7,14.2,0.9,0.0,46,3.0,295,'1 piece (75g)',75,NULL,NULL,NULL,NULL),
('SF017','Leche flan','Leche flan','Snacks and Fast Food',218,6.5,9.5,27.5,0.0,80,0.8,120,'1 piece/ramekin (100g)',100,NULL,NULL,NULL,NULL),
('SF018','Turon (banana spring roll)','Turon','Snacks and Fast Food',220,2.5,7.5,37.5,2.0,12,0.7,8,'1 piece (90g)',90,NULL,NULL,NULL,NULL),

-- CONDIMENTS AND SAUCES
('CO001','Soy sauce','Toyo','Condiments and Sauces',53,8.1,0.1,4.9,0.8,18,2.4,0,'1 tablespoon',16,NULL,NULL,NULL,NULL),
('CO002','Fish sauce','Patis','Condiments and Sauces',35,5.1,0.0,3.6,0.0,33,1.9,0,'1 tablespoon',18,NULL,NULL,NULL,NULL),
('CO003','Vinegar','Suka','Condiments and Sauces',21,0.0,0.0,0.9,0.0,6,0.0,0,'1 tablespoon',16,NULL,NULL,NULL,NULL),
('CO004','Banana ketchup','Banana catsup','Condiments and Sauces',100,0.5,0.1,24.9,0.5,5,0.3,0,'1 tablespoon',15,NULL,NULL,NULL,NULL),
('CO005','Shrimp paste (bagoong alamang)','Bagoong alamang','Condiments and Sauces',155,20.5,5.5,6.5,0.0,210,4.5,30,'1 tablespoon',20,NULL,NULL,NULL,NULL),
('CO006','Sugar, white','Asukal','Condiments and Sauces',387,0.0,0.0,100.0,0.0,1,0.0,0,'1 teaspoon',4,'1 tablespoon',12,NULL,NULL),
('CO007','Salt','Asin','Condiments and Sauces',0,0.0,0.0,0.0,0.0,24,0.3,0,'1 teaspoon',6,NULL,NULL,NULL,NULL);
