-- ============================================================
-- Migration v3 — Align with FNRI Food Composition Tables
-- Food and Nutrition Research Institute, DOST — Philippines
-- Reference: FNRI FCT 2013 / Philippine FCT 2020 Edition
--
-- Run: mysql -u root -p food_recall_db < db/migration_v3.sql
-- ============================================================

USE food_recall_db;

-- ── Step 1: Add standard FNRI FCT nutrient columns ──────────

ALTER TABLE fct_foods
  ADD COLUMN moisture_g    DECIMAL(7,2) DEFAULT NULL AFTER description,
  ADD COLUMN sodium_mg     DECIMAL(8,2) DEFAULT 0   AFTER iron_mg,
  ADD COLUMN phosphorus_mg DECIMAL(8,2) DEFAULT 0   AFTER sodium_mg,
  ADD COLUMN vitamin_c_mg  DECIMAL(8,2) DEFAULT 0   AFTER vitamin_a_mcg,
  ADD COLUMN thiamin_mg    DECIMAL(7,3) DEFAULT 0   AFTER vitamin_c_mg,
  ADD COLUMN riboflavin_mg DECIMAL(7,3) DEFAULT 0   AFTER thiamin_mg,
  ADD COLUMN niacin_mg     DECIMAL(7,2) DEFAULT 0   AFTER riboflavin_mg;

-- ── Step 2: Clear existing seed data ────────────────────────

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE fct_foods;
SET FOREIGN_KEY_CHECKS = 1;

-- ── Step 3: Re-seed with FNRI FCT-aligned values ────────────
-- Food codes follow FNRI FCT group numbering: GG-NNN
-- All nutrient values per 100g edible portion
-- Moisture values help verify energy calculation
-- Source: FNRI Food Composition Tables (2013/2020 editions)

INSERT INTO fct_foods
  (food_code, food_name, local_name, food_group,
   moisture_g, energy_kcal, protein_g, fat_g, carbs_g, fiber_g,
   calcium_mg, iron_mg, sodium_mg, phosphorus_mg,
   vitamin_a_mcg, vitamin_c_mg, thiamin_mg, riboflavin_mg, niacin_mg,
   measure_1_desc, measure_1_grams,
   measure_2_desc, measure_2_grams,
   measure_3_desc, measure_3_grams)
VALUES

-- ============================================================
-- GROUP 01 — CEREALS AND CEREAL PRODUCTS
-- ============================================================

('01-001','Rice, milled, cooked','Kanin (bigas puting luto)',
 'Cereals and Cereal Products',
 71.8, 130, 2.7, 0.3, 28.2, 0.4,
 4, 0.2, 0, 53,
 0, 0, 0.040, 0.020, 0.50,
 '1 cup', 186, '1 serving bowl', 280, '1 tablespoon', 12),

('01-002','Rice, brown, cooked','Pulang bigas, luto',
 'Cereals and Cereal Products',
 73.3, 111, 2.6, 0.9, 22.9, 1.8,
 10, 0.5, 0, 116,
 0, 0, 0.090, 0.030, 1.50,
 '1 cup', 195, '1 serving bowl', 290, NULL, NULL),

('01-003','Rice, glutinous, cooked','Malagkit, luto',
 'Cereals and Cereal Products',
 77.4, 97, 2.0, 0.2, 21.9, 0.3,
 3, 0.1, 0, 24,
 0, 0, 0.020, 0.010, 0.30,
 '1 cup', 240, '1 serving', 150, NULL, NULL),

('01-004','Bread, pan de sal','Pandesal',
 'Cereals and Cereal Products',
 33.4, 282, 8.9, 3.7, 54.0, 1.6,
 48, 2.0, 425, 67,
 0, 0, 0.270, 0.170, 2.30,
 '1 piece (small)', 30, '1 piece (large)', 50, '2 pieces', 60),

('01-005','Bread, white, sliced','Tinapay, puting hiniwa',
 'Cereals and Cereal Products',
 36.4, 265, 8.0, 2.5, 52.4, 1.9,
 80, 2.5, 490, 85,
 0, 0, 0.180, 0.140, 1.80,
 '1 slice', 25, '2 slices', 50, NULL, NULL),

('01-006','Noodles, instant, cooked','Pancit, instant, luto',
 'Cereals and Cereal Products',
 71.2, 138, 3.5, 4.5, 21.3, 0.5,
 10, 1.2, 400, 45,
 0, 0, 0.090, 0.040, 0.80,
 '1 cup cooked', 220, '1 pack cooked', 380, NULL, NULL),

('01-007','Oatmeal, cooked','Otmil, luto',
 'Cereals and Cereal Products',
 83.6, 71, 2.5, 1.5, 12.0, 1.7,
 10, 0.8, 49, 77,
 0, 0, 0.090, 0.040, 0.30,
 '1 cup', 234, '1/2 cup', 117, NULL, NULL),

('01-008','Corn, white, boiled','Mais, puting, luto',
 'Cereals and Cereal Products',
 73.7, 96, 3.4, 1.2, 21.3, 2.4,
 2, 0.5, 1, 89,
 10, 6.8, 0.170, 0.060, 1.70,
 '1 ear (small)', 90, '1 cup kernels', 154, NULL, NULL),

('01-009','Crackers, saltine','Galletas, saltine',
 'Cereals and Cereal Products',
 4.0, 421, 9.0, 9.3, 74.1, 2.6,
 35, 5.0, 1190, 115,
 0, 0, 0.190, 0.110, 2.20,
 '5 pieces', 15, '1 pack (30g)', 30, NULL, NULL),

('01-010','Champorado (rice and cocoa porridge)','Champorado',
 'Cereals and Cereal Products',
 64.0, 162, 3.8, 2.5, 32.0, 1.2,
 25, 1.5, 15, 60,
 2, 0, 0.040, 0.060, 0.60,
 '1 cup', 240, '1 bowl', 350, NULL, NULL),

-- ============================================================
-- GROUP 02 — STARCHY ROOTS, TUBERS AND OTHER STARCHY FOODS
-- ============================================================

('02-001','Sweet potato, yellow-orange, boiled','Kamote, dilaw, nilaga',
 'Starchy Roots, Tubers and Other Starchy Foods',
 75.8, 86, 1.6, 0.1, 20.1, 2.5,
 27, 0.5, 27, 32,
 150, 19.6, 0.080, 0.040, 0.60,
 '1 medium', 130, '1 cup cubed', 133, '1 small', 80),

('02-002','Cassava, boiled','Kamoteng kahoy, nilaga',
 'Starchy Roots, Tubers and Other Starchy Foods',
 62.7, 143, 1.4, 0.3, 34.7, 1.8,
 33, 0.4, 14, 56,
 0, 20.6, 0.060, 0.030, 0.60,
 '1 cup', 206, '1 medium piece', 100, NULL, NULL),

('02-003','Potato, boiled','Patatas, nilaga',
 'Starchy Roots, Tubers and Other Starchy Foods',
 77.0, 87, 1.9, 0.1, 20.1, 1.8,
 5, 0.3, 5, 44,
 0, 9.1, 0.110, 0.030, 1.40,
 '1 medium', 150, '1 cup diced', 156, NULL, NULL),

('02-004','Taro, cooked','Gabi, luto',
 'Starchy Roots, Tubers and Other Starchy Foods',
 70.0, 112, 1.5, 0.2, 26.5, 4.1,
 18, 0.6, 18, 56,
 4, 4.5, 0.070, 0.040, 0.50,
 '1 cup sliced', 132, '1 medium corm', 100, NULL, NULL),

('02-005','Banana, saba, boiled','Saging na saba, nilaga',
 'Starchy Roots, Tubers and Other Starchy Foods',
 74.0, 97, 1.4, 0.3, 22.9, 2.0,
 7, 0.4, 1, 28,
 10, 9.0, 0.050, 0.040, 0.60,
 '1 medium', 118, '2 pieces', 236, NULL, NULL),

-- ============================================================
-- GROUP 04 — FATS AND OILS
-- ============================================================

('04-001','Coconut oil','Langis ng niyog',
 'Fats and Oils',
 0.0, 862, 0.0, 100.0, 0.0, 0.0,
 0, 0.0, 0, 0,
 0, 0, 0.000, 0.000, 0.00,
 '1 tablespoon', 13, '1/2 cup', 109, NULL, NULL),

('04-002','Vegetable oil, palm (cooking oil)','Mantika, palm',
 'Fats and Oils',
 0.0, 884, 0.0, 100.0, 0.0, 0.0,
 0, 0.0, 0, 0,
 0, 0, 0.000, 0.000, 0.00,
 '1 tablespoon', 13, NULL, NULL, NULL, NULL),

('04-003','Margarine','Margarina',
 'Fats and Oils',
 16.0, 714, 0.9, 80.7, 0.9, 0.0,
 24, 0.1, 943, 20,
 50, 0, 0.000, 0.000, 0.00,
 '1 tablespoon', 14, '1 teaspoon', 5, NULL, NULL),

('04-004','Butter','Mantekilya',
 'Fats and Oils',
 15.9, 717, 0.9, 81.1, 0.1, 0.0,
 24, 0.0, 11, 24,
 102, 0, 0.000, 0.030, 0.00,
 '1 tablespoon', 14, '1 teaspoon', 5, NULL, NULL),

-- ============================================================
-- GROUP 05 — FISH AND SHELLFISH
-- ============================================================

('05-001','Milkfish (bangus), broiled','Bangus, inihaw',
 'Fish and Shellfish',
 65.6, 193, 20.3, 12.1, 0.0, 0.0,
 66, 1.0, 75, 260,
 30, 0, 0.060, 0.120, 4.20,
 '1 medium fillet', 100, '1 whole small', 200, NULL, NULL),

('05-002','Milkfish (bangus), fried','Bangus, prito',
 'Fish and Shellfish',
 62.0, 220, 19.0, 15.5, 0.0, 0.0,
 55, 1.1, 80, 240,
 28, 0, 0.050, 0.100, 3.80,
 '1 medium fillet', 100, '1 small fillet', 70, NULL, NULL),

('05-003','Tilapia, fried','Tilapia, prito',
 'Fish and Shellfish',
 70.5, 128, 26.2, 2.7, 0.0, 0.0,
 50, 1.0, 56, 193,
 20, 0, 0.040, 0.090, 5.20,
 '1 medium fish', 100, '1 small fish', 70, NULL, NULL),

('05-004','Sardines, canned in tomato sauce','Sardinas, de lata, sa kamatis',
 'Fish and Shellfish',
 60.0, 208, 24.6, 11.5, 0.5, 0.0,
 382, 2.9, 400, 411,
 32, 0, 0.030, 0.250, 6.50,
 '1 can (155g)', 155, '4 pieces', 100, NULL, NULL),

('05-005','Dried fish (tuyo), fried','Tuyo, prito',
 'Fish and Shellfish',
 26.0, 285, 50.5, 8.2, 0.0, 0.0,
 480, 3.9, 2650, 1420,
 25, 0, 0.050, 0.260, 10.20,
 '2 pieces (small)', 20, '5 pieces', 50, NULL, NULL),

('05-006','Galunggong (round scad), fried','Galunggong, prito',
 'Fish and Shellfish',
 62.0, 212, 22.5, 13.0, 0.0, 0.0,
 90, 1.8, 65, 210,
 25, 0, 0.060, 0.110, 3.90,
 '1 medium fish', 80, '1 large fish', 120, NULL, NULL),

('05-007','Shrimp, boiled','Hipon, nilaga',
 'Fish and Shellfish',
 76.4, 99, 20.3, 1.7, 0.0, 0.0,
 45, 0.9, 148, 205,
 52, 0.9, 0.030, 0.040, 2.60,
 '10 medium pieces', 100, '1 cup', 145, NULL, NULL),

('05-008','Squid (pusit), cooked','Pusit, luto',
 'Fish and Shellfish',
 79.0, 92, 15.6, 1.4, 3.1, 0.0,
 32, 0.7, 260, 221,
 10, 0, 0.020, 0.060, 2.20,
 '1 cup rings', 120, NULL, NULL, NULL, NULL),

('05-009','Tuna, canned in water','Tuna, de lata sa tubig',
 'Fish and Shellfish',
 72.4, 116, 25.5, 1.0, 0.0, 0.0,
 10, 1.3, 247, 195,
 20, 0, 0.040, 0.100, 11.80,
 '1 can (180g)', 180, '3 tablespoons', 55, NULL, NULL),

('05-010','Mussels (tahong), cooked','Tahong, luto',
 'Fish and Shellfish',
 64.5, 172, 23.8, 4.5, 7.4, 0.0,
 32, 6.7, 286, 236,
 48, 8.0, 0.130, 0.220, 1.60,
 '8 pieces', 100, '1 cup', 140, NULL, NULL),

('05-011','Clam (halaan), cooked','Halaan, luto',
 'Fish and Shellfish',
 78.0, 74, 12.8, 1.0, 2.7, 0.0,
 46, 14.0, 340, 196,
 60, 0, 0.050, 0.130, 1.50,
 '10 pieces', 100, NULL, NULL, NULL, NULL),

('05-012','Dried shrimp, small (hibi/alamang)','Alamang/Hibi',
 'Fish and Shellfish',
 23.3, 290, 51.0, 4.0, 0.0, 0.0,
 1760, 12.5, 2100, 600,
 30, 0, 0.050, 0.180, 6.00,
 '1 tablespoon', 15, '1/4 cup', 60, NULL, NULL),

-- ============================================================
-- GROUP 06 — MEAT AND POULTRY
-- ============================================================

('06-001','Chicken, broiled or fried','Manok, inihaw/prito',
 'Meat and Poultry',
 62.7, 215, 27.3, 11.1, 0.8, 0.0,
 14, 1.3, 86, 220,
 30, 0, 0.100, 0.180, 8.40,
 '1 drumstick', 95, '1 thigh', 110, '1 breast fillet', 130),

('06-002','Pork, lean, cooked','Baboy, karne, luto',
 'Meat and Poultry',
 59.3, 242, 27.0, 14.5, 0.0, 0.0,
 12, 1.6, 62, 220,
 0, 0, 0.700, 0.210, 4.40,
 '2 oz (56g)', 56, '1 cup chopped', 140, NULL, NULL),

('06-003','Beef, lean, cooked','Baka, karne, luto',
 'Meat and Poultry',
 61.3, 218, 28.0, 11.5, 0.0, 0.0,
 10, 2.7, 70, 198,
 0, 0, 0.070, 0.200, 4.80,
 '2 oz (56g)', 56, '1 cup chopped', 140, NULL, NULL),

('06-004','Pork liver, cooked','Atay ng baboy, luto',
 'Meat and Poultry',
 68.9, 165, 26.0, 4.9, 3.8, 0.0,
 10, 18.0, 74, 350,
 6583, 23.0, 0.220, 3.390, 14.80,
 '1 medium slice (56g)', 56, '1 cup chopped', 145, NULL, NULL),

('06-005','Hotdog, cooked','Hotdog, luto',
 'Meat and Poultry',
 56.0, 290, 11.0, 26.5, 2.7, 0.0,
 12, 1.0, 860, 95,
 0, 0, 0.110, 0.080, 1.50,
 '1 piece', 45, '2 pieces', 90, NULL, NULL),

('06-006','Longanisa, cooked','Longganisa, prito',
 'Meat and Poultry',
 51.0, 330, 15.3, 28.7, 3.2, 0.0,
 15, 1.2, 722, 130,
 10, 0, 0.180, 0.120, 2.30,
 '1 piece', 50, '2 pieces', 100, NULL, NULL),

('06-007','Corned beef, canned','Corned beef, de lata',
 'Meat and Poultry',
 62.8, 185, 24.8, 9.9, 0.5, 0.0,
 14, 2.7, 840, 165,
 0, 0, 0.030, 0.180, 4.10,
 '1 can (180g)', 180, '2 tablespoons', 40, NULL, NULL),

('06-008','Chicken liver, cooked','Atay ng manok, luto',
 'Meat and Poultry',
 68.1, 172, 24.5, 7.6, 0.9, 0.0,
 11, 11.0, 71, 296,
 4185, 17.0, 0.220, 1.770, 9.80,
 '1 cup', 145, '2 oz (56g)', 56, NULL, NULL),

-- ============================================================
-- GROUP 07 — EGGS
-- ============================================================

('07-001','Chicken egg, whole, boiled','Itlog ng manok, nilaga',
 'Eggs',
 73.7, 147, 12.6, 10.0, 0.7, 0.0,
 50, 1.8, 124, 178,
 190, 0, 0.060, 0.440, 0.10,
 '1 medium egg', 50, '1 large egg', 60, '2 medium eggs', 100),

('07-002','Duck egg, salted (itlog na maalat)','Itlog na maalat',
 'Eggs',
 66.3, 182, 13.0, 13.5, 2.2, 0.0,
 74, 2.0, 1253, 220,
 290, 0, 0.080, 0.290, 0.10,
 '1 medium egg', 70, NULL, NULL, NULL, NULL),

('07-003','Chicken egg, scrambled, with oil','Itlog, scrambled/pinirito',
 'Eggs',
 73.0, 149, 10.1, 11.5, 1.6, 0.0,
 47, 1.5, 150, 160,
 180, 0, 0.050, 0.380, 0.10,
 '1 egg cooked', 55, '2 eggs cooked', 110, NULL, NULL),

('07-004','Quail egg, boiled','Itlog ng pugo, nilaga',
 'Eggs',
 71.0, 158, 13.1, 11.1, 0.4, 0.0,
 64, 3.6, 141, 226,
 156, 0, 0.120, 0.690, 0.10,
 '5 pieces', 50, '10 pieces', 100, NULL, NULL),

-- ============================================================
-- GROUP 08 — MILK AND MILK PRODUCTS
-- ============================================================

('08-001','Milk, cow, fresh or pasteurized','Gatas ng baka, sariwa',
 'Milk and Milk Products',
 87.8, 63, 3.2, 3.6, 4.7, 0.0,
 117, 0.1, 49, 91,
 38, 1.0, 0.040, 0.160, 0.10,
 '1 cup (240ml)', 244, '1 glass (200ml)', 200, '1 tablespoon', 15),

('08-002','Evaporated milk, canned','Gatas, evaporated, de lata',
 'Milk and Milk Products',
 73.8, 134, 6.8, 7.5, 10.1, 0.0,
 261, 0.2, 115, 197,
 79, 1.0, 0.050, 0.340, 0.20,
 '2 tablespoons', 30, '1/2 cup', 122, '1 can (370g)', 370),

('08-003','Powdered whole milk','Gatas, pulbos, buong-taba',
 'Milk and Milk Products',
 2.5, 496, 26.3, 26.7, 38.4, 0.0,
 912, 0.7, 371, 719,
 300, 3.0, 0.250, 1.020, 0.60,
 '2 tablespoons (16g)', 16, '1/4 cup (32g)', 32, NULL, NULL),

('08-004','Infant formula, reconstituted (ready to drink)','Gatas ng sanggol, handa',
 'Milk and Milk Products',
 88.5, 67, 1.5, 3.6, 7.3, 0.0,
 53, 0.8, 24, 38,
 60, 7.0, 0.060, 0.100, 0.70,
 '1 cup prepared (240ml)', 244, '1 bottle (180ml)', 180, NULL, NULL),

('08-005','Cheese, processed','Keso, processed',
 'Milk and Milk Products',
 46.0, 321, 19.8, 26.9, 2.5, 0.0,
 600, 0.7, 1340, 490,
 290, 0, 0.020, 0.280, 0.10,
 '1 slice (20g)', 20, '2 tablespoons grated', 20, NULL, NULL),

('08-006','Yogurt, plain','Yogurt, natural',
 'Milk and Milk Products',
 85.1, 59, 3.5, 3.3, 4.7, 0.0,
 110, 0.1, 36, 88,
 29, 0.5, 0.040, 0.160, 0.10,
 '1 cup', 245, '1 small cup', 150, NULL, NULL),

-- ============================================================
-- GROUP 09 — DRIED BEANS, NUTS AND SEEDS
-- ============================================================

('09-001','Mung beans, cooked','Monggo, luto',
 'Dried Beans, Nuts and Seeds',
 71.1, 105, 7.0, 0.4, 19.2, 7.6,
 27, 1.4, 2, 99,
 24, 1.0, 0.160, 0.060, 0.60,
 '1 cup', 202, '1/2 cup', 101, NULL, NULL),

('09-002','Peanuts, roasted, without skin','Mani, litong',
 'Dried Beans, Nuts and Seeds',
 6.5, 585, 25.8, 49.7, 16.1, 8.5,
 92, 2.3, 18, 376,
 0, 0, 0.870, 0.110, 14.30,
 '1 tablespoon', 15, '1 handful (25g)', 25, '1 cup', 146),

('09-003','Tofu, firm (tokwa)','Tokwa',
 'Dried Beans, Nuts and Seeds',
 84.6, 76, 8.1, 4.2, 1.9, 0.3,
 350, 1.6, 7, 97,
 0, 0, 0.090, 0.040, 0.20,
 '1/2 block (122g)', 122, '1 full block', 244, NULL, NULL),

('09-004','Soy milk, plain','Gatas ng soya',
 'Dried Beans, Nuts and Seeds',
 89.6, 43, 3.6, 2.0, 2.9, 0.6,
 9, 0.7, 19, 52,
 0, 0, 0.030, 0.030, 0.30,
 '1 cup (240ml)', 244, '1 glass', 240, NULL, NULL),

('09-005','Black-eyed peas, cooked','Paayap, luto',
 'Dried Beans, Nuts and Seeds',
 70.0, 116, 7.7, 0.5, 20.8, 6.5,
 24, 2.5, 4, 156,
 1, 0.2, 0.110, 0.050, 0.50,
 '1 cup', 172, NULL, NULL, NULL, NULL),

-- ============================================================
-- GROUP 10 — VEGETABLES
-- ============================================================

('10-001','Water spinach, cooked (kangkong)','Kangkong, luto',
 'Vegetables',
 92.8, 25, 3.0, 0.4, 3.6, 2.1,
 73, 1.7, 113, 50,
 250, 32.4, 0.090, 0.090, 0.90,
 '1 cup', 196, '1 serving', 100, NULL, NULL),

('10-002','Moringa leaves, raw (malunggay)','Dahon ng malunggay, sariwa',
 'Vegetables',
 78.7, 92, 9.4, 1.4, 8.3, 2.0,
 185, 4.0, 9, 112,
 378, 220.0, 0.210, 0.050, 2.20,
 '1 cup', 21, '1 tablespoon', 5, NULL, NULL),

('10-003','Bitter melon, cooked (ampalaya)','Ampalaya, luto',
 'Vegetables',
 94.0, 19, 1.0, 0.2, 3.7, 2.8,
 19, 0.3, 5, 31,
 72, 61.0, 0.090, 0.050, 0.40,
 '1 cup sliced', 124, '1/2 medium', 70, NULL, NULL),

('10-004','String beans, cooked (sitaw)','Sitaw, luto',
 'Vegetables',
 91.0, 35, 2.1, 0.3, 6.4, 2.9,
 42, 1.0, 4, 47,
 34, 19.3, 0.080, 0.110, 0.60,
 '1 cup', 125, '10 pieces', 60, NULL, NULL),

('10-005','Tomato, raw (kamatis)','Kamatis, sariwa',
 'Vegetables',
 94.5, 18, 0.9, 0.2, 3.9, 1.2,
 10, 0.3, 5, 27,
 42, 23.4, 0.060, 0.040, 0.60,
 '1 medium', 123, '1 cup chopped', 180, NULL, NULL),

('10-006','Eggplant, cooked (talong)','Talong, luto',
 'Vegetables',
 90.3, 35, 0.9, 0.2, 8.2, 2.5,
 7, 0.2, 2, 20,
 3, 1.3, 0.050, 0.040, 0.60,
 '1 cup cubed', 96, '1 medium', 82, NULL, NULL),

('10-007','Squash, cooked (kalabasa)','Kalabasa, luto',
 'Vegetables',
 92.9, 26, 1.0, 0.1, 6.5, 0.9,
 30, 0.6, 1, 30,
 530, 18.0, 0.030, 0.030, 0.40,
 '1 cup cubed', 116, NULL, NULL, NULL, NULL),

('10-008','Okra, cooked (okra)','Okra, luto',
 'Vegetables',
 92.7, 22, 1.9, 0.2, 4.5, 2.0,
 77, 0.4, 8, 63,
 36, 16.3, 0.090, 0.100, 0.90,
 '1 cup', 160, '8 pieces', 80, NULL, NULL),

('10-009','Cabbage, cooked (repolyo)','Repolyo, luto',
 'Vegetables',
 93.4, 23, 1.3, 0.3, 4.6, 2.0,
 65, 0.4, 13, 33,
 5, 36.6, 0.050, 0.030, 0.30,
 '1 cup', 150, NULL, NULL, NULL, NULL),

('10-010','Bok choy, cooked (pechay)','Petsay/Pechay, luto',
 'Vegetables',
 95.7, 13, 1.8, 0.2, 1.8, 1.0,
 105, 0.7, 65, 38,
 180, 45.0, 0.050, 0.070, 0.50,
 '1 cup', 170, NULL, NULL, NULL, NULL),

('10-011','Onion, raw (sibuyas)','Sibuyas, sariwa',
 'Vegetables',
 89.1, 40, 1.1, 0.1, 9.3, 1.7,
 23, 0.2, 4, 29,
 0, 7.4, 0.050, 0.020, 0.20,
 '1 medium', 110, '1 tablespoon chopped', 10, NULL, NULL),

('10-012','Garlic, raw (bawang)','Bawang, sariwa',
 'Vegetables',
 58.6, 149, 6.4, 0.5, 33.1, 2.1,
 181, 1.7, 17, 153,
 0, 31.2, 0.200, 0.110, 0.70,
 '1 clove', 3, '1 tablespoon minced', 9, NULL, NULL),

('10-013','Carrot, raw (karot)','Karot, sariwa',
 'Vegetables',
 88.3, 41, 0.9, 0.2, 9.6, 2.8,
 33, 0.3, 69, 35,
 835, 5.9, 0.060, 0.060, 0.70,
 '1 medium', 61, '1 cup grated', 110, NULL, NULL),

('10-014','Sweet potato tops, cooked (talbos ng kamote)','Talbos ng kamote, luto',
 'Vegetables',
 87.8, 44, 4.2, 0.8, 6.5, 3.2,
 85, 2.1, 10, 52,
 560, 31.3, 0.050, 0.130, 0.90,
 '1 cup', 132, NULL, NULL, NULL, NULL),

('10-015','Pumpkin tendrils, cooked (talbos ng kalabasa)','Talbos ng kalabasa, luto',
 'Vegetables',
 93.5, 23, 2.5, 0.3, 3.2, 2.0,
 60, 1.5, 6, 47,
 200, 25.0, 0.060, 0.090, 0.60,
 '1 cup', 67, NULL, NULL, NULL, NULL),

-- ============================================================
-- GROUP 11 — FRUITS
-- ============================================================

('11-001','Banana, ripe (lakatan or latundan)','Saging, hinog (lakatan/latundan)',
 'Fruits',
 74.9, 89, 1.1, 0.3, 22.8, 2.6,
 5, 0.3, 1, 22,
 3, 8.7, 0.030, 0.060, 0.70,
 '1 medium', 118, '1 large', 136, '1 small', 80),

('11-002','Banana, saba, ripe, raw','Saging na saba, hinog, sariwa',
 'Fruits',
 73.1, 97, 1.4, 0.3, 22.9, 2.0,
 7, 0.4, 1, 26,
 10, 9.0, 0.050, 0.040, 0.60,
 '1 medium', 115, NULL, NULL, NULL, NULL),

('11-003','Mango, ripe (carabao variety)','Mangga, hinog (carabao)',
 'Fruits',
 81.4, 65, 0.5, 0.4, 17.0, 1.8,
 10, 0.2, 2, 11,
 38, 41.0, 0.060, 0.060, 0.60,
 '1 medium (without seed)', 200, '1 cup sliced', 165, '1 small', 150),

('11-004','Papaya, ripe','Papaya, hinog',
 'Fruits',
 88.1, 43, 0.5, 0.1, 10.8, 1.7,
 20, 0.1, 8, 15,
 47, 61.8, 0.030, 0.040, 0.30,
 '1 cup cubed', 140, '1/8 medium', 100, NULL, NULL),

('11-005','Watermelon (pakwan)','Pakwan',
 'Fruits',
 91.5, 30, 0.6, 0.2, 7.6, 0.4,
 7, 0.2, 1, 11,
 28, 8.1, 0.030, 0.020, 0.20,
 '1 cup cubed', 154, '1 wedge', 280, NULL, NULL),

('11-006','Pineapple (pinya)','Pinya',
 'Fruits',
 86.0, 50, 0.5, 0.1, 13.1, 1.4,
 13, 0.3, 1, 8,
 3, 47.8, 0.090, 0.030, 0.50,
 '1 cup chunks', 165, '1 slice', 84, NULL, NULL),

('11-007','Calamansi, squeezed juice','Kalamansi, katas',
 'Fruits',
 91.5, 25, 0.6, 0.3, 5.8, 0.8,
 16, 0.2, 1, 12,
 5, 43.7, 0.040, 0.020, 0.20,
 '1 piece', 15, '5 pieces', 75, NULL, NULL),

('11-008','Avocado (abokado)','Abokado',
 'Fruits',
 72.3, 160, 2.0, 14.7, 8.5, 6.7,
 12, 0.6, 7, 52,
 7, 10.0, 0.070, 0.130, 1.70,
 '1/2 medium', 100, '1 cup mashed', 230, NULL, NULL),

('11-009','Guava (bayabas)','Bayabas',
 'Fruits',
 80.8, 68, 2.6, 1.0, 14.3, 5.4,
 18, 0.3, 3, 49,
 31, 228.3, 0.050, 0.050, 1.10,
 '1 medium', 90, '1 cup', 165, NULL, NULL),

('11-010','Orange or dalandan','Dalandan/Kahel',
 'Fruits',
 86.3, 47, 0.9, 0.1, 11.7, 2.4,
 40, 0.1, 0, 17,
 11, 53.2, 0.090, 0.040, 0.20,
 '1 medium', 180, '1 small', 130, NULL, NULL),

('11-011','Coconut meat, fresh (buko)','Buko, sariwa',
 'Fruits',
 46.9, 354, 3.3, 33.5, 15.2, 9.0,
 14, 2.4, 20, 113,
 0, 3.3, 0.070, 0.020, 0.50,
 '1 cup shredded', 80, '1/4 young coconut', 45, NULL, NULL),

-- ============================================================
-- GROUP 12 — NON-ALCOHOLIC BEVERAGES
-- ============================================================

('12-001','Coffee, brewed, black','Kape, itim, brewed',
 'Non-Alcoholic Beverages',
 99.4, 1, 0.3, 0.0, 0.0, 0.0,
 2, 0.0, 2, 3,
 0, 0, 0.000, 0.010, 0.50,
 '1 cup (240ml)', 240, NULL, NULL, NULL, NULL),

('12-002','Coffee, 3-in-1 mix, prepared','Kape, 3-in-1, handa',
 'Non-Alcoholic Beverages',
 85.0, 61, 0.8, 1.5, 11.4, 0.0,
 8, 0.1, 44, 15,
 0, 0, 0.000, 0.020, 0.30,
 '1 sachet prepared (200ml)', 200, NULL, NULL, NULL, NULL),

('12-003','Softdrink, cola type','Soft drink, cola',
 'Non-Alcoholic Beverages',
 89.4, 41, 0.0, 0.0, 10.6, 0.0,
 2, 0.1, 8, 20,
 0, 0, 0.000, 0.000, 0.00,
 '1 glass (240ml)', 240, '1 can (330ml)', 330, '1 small bottle (237ml)', 237),

('12-004','Fruit juice, orange (100%)','Juice ng kahel, 100%',
 'Non-Alcoholic Beverages',
 88.7, 45, 0.7, 0.2, 10.4, 0.2,
 11, 0.2, 1, 17,
 4, 50.0, 0.090, 0.030, 0.40,
 '1 glass (240ml)', 240, NULL, NULL, NULL, NULL),

('12-005','Coconut water (buko juice)','Tubig ng buko',
 'Non-Alcoholic Beverages',
 95.5, 19, 0.7, 0.2, 3.7, 1.1,
 24, 0.3, 105, 20,
 0, 2.4, 0.030, 0.060, 0.10,
 '1 cup (240ml)', 240, '1 young coconut', 250, NULL, NULL),

('12-006','Chocolate drink (Milo or cocoa), prepared','Tsokolate/Milo, handa',
 'Non-Alcoholic Beverages',
 82.5, 72, 3.3, 2.0, 11.0, 1.5,
 110, 2.5, 57, 97,
 25, 2.5, 0.060, 0.140, 1.10,
 '1 cup (240ml)', 240, NULL, NULL, NULL, NULL),

('12-007','Tea, brewed (plain)','Tsaa, brewed',
 'Non-Alcoholic Beverages',
 99.5, 1, 0.0, 0.0, 0.3, 0.0,
 0, 0.0, 3, 2,
 0, 0, 0.000, 0.010, 0.00,
 '1 cup (237ml)', 237, NULL, NULL, NULL, NULL),

('12-008','Water, plain (drinking water)','Tubig, inumin',
 'Non-Alcoholic Beverages',
 100.0, 0, 0.0, 0.0, 0.0, 0.0,
 0, 0.0, 5, 0,
 0, 0, 0.000, 0.000, 0.00,
 '1 glass (240ml)', 240, '1 cup', 237, '1 bottle (500ml)', 500),

('12-009','Fruit juice drink, artificial flavor (Tang, Zesto)','Juice drink, artipisyal',
 'Non-Alcoholic Beverages',
 88.0, 48, 0.1, 0.1, 12.0, 0.0,
 5, 0.1, 8, 2,
 0, 30.0, 0.000, 0.000, 0.00,
 '1 glass (240ml)', 240, NULL, NULL, NULL, NULL),

('12-010','Sugarcane juice, fresh (tubong)','Tubong, sariwang katas',
 'Non-Alcoholic Beverages',
 89.6, 39, 0.2, 0.1, 9.7, 0.0,
 10, 0.3, 1, 7,
 0, 0, 0.000, 0.010, 0.10,
 '1 glass (240ml)', 240, NULL, NULL, NULL, NULL),

-- ============================================================
-- GROUP 13 — ALCOHOLIC BEVERAGES
-- ============================================================

('13-001','Beer, lager type','Beer, lager',
 'Alcoholic Beverages',
 91.9, 43, 0.5, 0.0, 3.6, 0.0,
 4, 0.0, 14, 14,
 0, 0, 0.010, 0.030, 0.50,
 '1 bottle (330ml)', 330, '1 can (330ml)', 330, '1 glass (240ml)', 240),

('13-002','Wine, red or white','Alak na ubas (pula o puti)',
 'Alcoholic Beverages',
 86.5, 85, 0.1, 0.0, 2.7, 0.0,
 8, 0.5, 5, 10,
 0, 0, 0.000, 0.010, 0.10,
 '1 glass (120ml)', 120, NULL, NULL, NULL, NULL),

('13-003','Distilled spirits (gin, rum, brandy, 40% alc)','Gin/Rum/Brandy',
 'Alcoholic Beverages',
 67.1, 231, 0.0, 0.0, 0.0, 0.0,
 0, 0.0, 1, 0,
 0, 0, 0.000, 0.000, 0.00,
 '1 shot (30ml)', 30, NULL, NULL, NULL, NULL),

('13-004','Tuba (coconut toddy, fermented)','Tuba',
 'Alcoholic Beverages',
 88.0, 50, 0.2, 0.0, 9.0, 0.0,
 10, 0.5, 14, 10,
 0, 0, 0.010, 0.040, 0.20,
 '1 glass (240ml)', 240, NULL, NULL, NULL, NULL),

('13-005','Lambanog (coconut vodka, distilled)','Lambanog',
 'Alcoholic Beverages',
 63.5, 238, 0.0, 0.0, 0.0, 0.0,
 0, 0.0, 1, 0,
 0, 0, 0.000, 0.000, 0.00,
 '1 shot (30ml)', 30, NULL, NULL, NULL, NULL),

-- ============================================================
-- GROUP 14 — CONDIMENTS AND RELATED PRODUCTS
-- ============================================================

('14-001','Soy sauce (toyo)','Toyo',
 'Condiments and Related Products',
 72.4, 53, 8.1, 0.1, 4.9, 0.8,
 18, 2.4, 5493, 130,
 0, 0, 0.030, 0.140, 0.90,
 '1 tablespoon', 16, NULL, NULL, NULL, NULL),

('14-002','Fish sauce (patis)','Patis',
 'Condiments and Related Products',
 75.9, 35, 5.1, 0.0, 3.6, 0.0,
 33, 1.9, 6220, 63,
 0, 0, 0.010, 0.060, 1.90,
 '1 tablespoon', 18, NULL, NULL, NULL, NULL),

('14-003','Vinegar (suka, sugarcane or coconut)','Suka',
 'Condiments and Related Products',
 94.0, 21, 0.0, 0.0, 0.9, 0.0,
 6, 0.0, 2, 8,
 0, 0, 0.000, 0.000, 0.00,
 '1 tablespoon', 16, NULL, NULL, NULL, NULL),

('14-004','Banana ketchup (banana catsup)','Banana catsup',
 'Condiments and Related Products',
 68.5, 100, 0.5, 0.1, 24.9, 0.5,
 5, 0.3, 780, 18,
 0, 0, 0.020, 0.030, 0.30,
 '1 tablespoon', 15, NULL, NULL, NULL, NULL),

('14-005','Shrimp paste (bagoong alamang)','Bagoong alamang',
 'Condiments and Related Products',
 59.9, 155, 20.5, 5.5, 6.5, 0.0,
 210, 4.5, 3820, 250,
 30, 0, 0.020, 0.100, 2.00,
 '1 tablespoon', 20, '1 teaspoon', 7, NULL, NULL),

('14-006','Sugar, white, granulated (asukal puti)','Asukal, puti',
 'Condiments and Related Products',
 0.5, 387, 0.0, 0.0, 100.0, 0.0,
 1, 0.0, 1, 0,
 0, 0, 0.000, 0.000, 0.00,
 '1 teaspoon', 4, '1 tablespoon', 12, '1 cup', 200),

('14-007','Salt, table (asin)','Asin, table salt',
 'Condiments and Related Products',
 0.0, 0, 0.0, 0.0, 0.0, 0.0,
 24, 0.3, 38758, 0,
 0, 0, 0.000, 0.000, 0.00,
 '1 teaspoon', 6, NULL, NULL, NULL, NULL),

-- ============================================================
-- GROUP 15 — MIXED DISHES AND FAST FOODS
-- (Calculated from typical Philippine recipes; FNRI 2013)
-- ============================================================

('15-001','Adobo, chicken','Adobong manok',
 'Mixed Dishes and Fast Foods',
 NULL, 220, 18.0, 14.0, 4.0, 0.3,
 20, 1.5, 410, 180,
 25, 0.5, 0.080, 0.160, 6.20,
 '1 cup', 240, '1 serving', 200, NULL, NULL),

('15-002','Adobo, pork','Adobong baboy',
 'Mixed Dishes and Fast Foods',
 NULL, 315, 16.5, 25.0, 4.5, 0.2,
 15, 1.2, 480, 155,
 0, 0.3, 0.560, 0.180, 3.80,
 '1 cup', 240, '1 serving', 200, NULL, NULL),

('15-003','Sinigang, pork with vegetables','Sinigang na baboy',
 'Mixed Dishes and Fast Foods',
 NULL, 95, 8.0, 6.0, 2.5, 0.8,
 20, 1.0, 320, 120,
 10, 8.5, 0.230, 0.130, 2.10,
 '1 cup', 240, '1 bowl', 350, NULL, NULL),

('15-004','Tinola, chicken with chayote and moringa','Tinolang manok',
 'Mixed Dishes and Fast Foods',
 NULL, 75, 9.0, 3.5, 2.0, 0.5,
 25, 0.8, 220, 130,
 45, 12.0, 0.070, 0.150, 3.90,
 '1 cup', 240, '1 bowl', 350, NULL, NULL),

('15-005','Arroz caldo (rice and chicken porridge)','Arroz caldo',
 'Mixed Dishes and Fast Foods',
 NULL, 85, 5.0, 3.0, 11.0, 0.3,
 20, 0.5, 180, 75,
 10, 0.5, 0.050, 0.080, 1.80,
 '1 cup', 240, '1 bowl', 350, NULL, NULL),

('15-006','Lugaw (plain rice porridge)','Lugaw',
 'Mixed Dishes and Fast Foods',
 NULL, 60, 2.0, 0.5, 12.5, 0.3,
 10, 0.2, 50, 35,
 0, 0, 0.030, 0.020, 0.40,
 '1 cup', 240, '1 bowl', 350, NULL, NULL),

('15-007','Pancit canton (stir-fried wheat noodles)','Pansit canton',
 'Mixed Dishes and Fast Foods',
 NULL, 152, 6.0, 5.0, 22.0, 1.5,
 25, 1.2, 380, 80,
 10, 2.5, 0.080, 0.070, 1.20,
 '1 cup', 200, '1 serving', 250, NULL, NULL),

('15-008','Pancit bihon (stir-fried rice noodles)','Pansit bihon',
 'Mixed Dishes and Fast Foods',
 NULL, 141, 5.5, 3.5, 23.5, 0.8,
 20, 1.0, 350, 65,
 8, 2.0, 0.070, 0.060, 1.00,
 '1 cup', 200, '1 serving', 250, NULL, NULL),

('15-009','Menudo (pork with liver and vegetables)','Menudo',
 'Mixed Dishes and Fast Foods',
 NULL, 178, 12.0, 11.0, 8.5, 1.0,
 30, 2.0, 390, 145,
 85, 5.0, 0.200, 0.250, 3.50,
 '1 cup', 240, NULL, NULL, NULL, NULL),

('15-010','Kare-kare (oxtail and vegetables in peanut sauce)','Kare-kare',
 'Mixed Dishes and Fast Foods',
 NULL, 180, 12.0, 12.0, 8.0, 2.0,
 35, 1.8, 250, 145,
 20, 3.0, 0.080, 0.130, 3.40,
 '1 cup', 240, NULL, NULL, NULL, NULL),

('15-011','Nilaga, pork (boiled pork with vegetables)','Nilagang baboy',
 'Mixed Dishes and Fast Foods',
 NULL, 120, 10.0, 7.5, 3.5, 1.5,
 25, 1.0, 180, 110,
 5, 5.0, 0.330, 0.140, 2.60,
 '1 cup', 240, '1 bowl', 350, NULL, NULL),

('15-012','Pinakbet (mixed vegetables with shrimp paste)','Pinakbet/Pakbet',
 'Mixed Dishes and Fast Foods',
 NULL, 95, 5.5, 6.0, 6.5, 2.5,
 40, 1.2, 520, 80,
 120, 18.0, 0.070, 0.090, 0.80,
 '1 cup', 200, NULL, NULL, NULL, NULL),

('15-013','Ginisang monggo (sautéed mung beans)','Ginisang monggo',
 'Mixed Dishes and Fast Foods',
 NULL, 118, 8.0, 3.5, 15.5, 4.5,
 40, 1.8, 290, 120,
 30, 5.0, 0.130, 0.080, 0.80,
 '1 cup', 245, NULL, NULL, NULL, NULL),

('15-014','Sinangag (garlic fried rice)','Sinangag',
 'Mixed Dishes and Fast Foods',
 NULL, 178, 3.5, 5.5, 29.0, 0.4,
 8, 0.3, 90, 55,
 0, 0.3, 0.040, 0.030, 0.60,
 '1 cup', 180, NULL, NULL, NULL, NULL),

('15-015','Caldereta (beef or chicken in tomato sauce)','Kaldereta',
 'Mixed Dishes and Fast Foods',
 NULL, 195, 13.0, 13.0, 7.5, 1.5,
 25, 1.5, 380, 150,
 60, 5.5, 0.100, 0.170, 3.50,
 '1 cup', 240, NULL, NULL, NULL, NULL),

('15-016','Mechado / Bistek Tagalog','Mechado/Bistek',
 'Mixed Dishes and Fast Foods',
 NULL, 185, 14.0, 12.0, 6.0, 1.0,
 20, 2.0, 420, 145,
 30, 3.0, 0.080, 0.180, 3.80,
 '1 cup', 240, NULL, NULL, NULL, NULL),

('15-017','Bulalo (beef marrow bone soup)','Bulalo',
 'Mixed Dishes and Fast Foods',
 NULL, 145, 15.5, 8.5, 2.0, 0.5,
 35, 1.5, 190, 145,
 10, 3.0, 0.050, 0.150, 3.20,
 '1 cup', 250, '1 bowl', 400, NULL, NULL),

('15-018','Dinuguan (pork blood stew)','Dinuguan',
 'Mixed Dishes and Fast Foods',
 NULL, 185, 15.0, 13.0, 3.0, 0.5,
 30, 7.5, 350, 165,
 0, 0.3, 0.200, 0.260, 3.90,
 '1 cup', 240, NULL, NULL, NULL, NULL),

('15-019','Afritada (chicken or pork in tomato sauce)','Afritada',
 'Mixed Dishes and Fast Foods',
 NULL, 188, 14.5, 12.5, 6.5, 1.2,
 25, 1.5, 390, 145,
 50, 7.0, 0.110, 0.160, 3.80,
 '1 cup', 240, NULL, NULL, NULL, NULL),

('15-020','Laing (taro leaves in coconut milk)','Laing',
 'Mixed Dishes and Fast Foods',
 NULL, 245, 6.5, 20.5, 10.5, 4.0,
 85, 1.8, 240, 90,
 60, 4.5, 0.050, 0.070, 0.70,
 '1 cup', 200, NULL, NULL, NULL, NULL),

('15-021','French fries, fast food','Pritong patatas, fast food',
 'Mixed Dishes and Fast Foods',
 NULL, 312, 3.4, 15.0, 41.1, 3.8,
 10, 0.9, 270, 90,
 0, 7.0, 0.090, 0.040, 1.90,
 '1 medium order (100g)', 100, '1 large order (154g)', 154, NULL, NULL),

('15-022','Burger, beef patty','Hamburger',
 'Mixed Dishes and Fast Foods',
 NULL, 295, 17.0, 14.0, 24.0, 1.5,
 80, 2.5, 580, 160,
 5, 0.5, 0.210, 0.220, 4.50,
 '1 regular', 150, '1 small', 100, NULL, NULL),

('15-023','Fried chicken, fast food','Fried chicken, fast food',
 'Mixed Dishes and Fast Foods',
 NULL, 245, 21.5, 14.5, 7.5, 0.3,
 12, 1.0, 540, 195,
 15, 0, 0.090, 0.170, 7.20,
 '1 piece', 100, NULL, NULL, NULL, NULL),

('15-024','Pizza, cheese','Pizza, keso',
 'Mixed Dishes and Fast Foods',
 NULL, 266, 11.4, 10.4, 32.5, 2.3,
 200, 1.5, 540, 200,
 50, 1.5, 0.180, 0.220, 2.80,
 '1 slice', 80, '2 slices', 160, NULL, NULL),

('15-025','Siomai, pork, steamed','Siomai, baboy, niluto sa singaw',
 'Mixed Dishes and Fast Foods',
 NULL, 158, 10.0, 8.5, 11.5, 0.8,
 20, 1.2, 390, 120,
 5, 0.5, 0.090, 0.100, 1.80,
 '4 pieces', 120, '1 piece', 30, NULL, NULL),

('15-026','Siopao, asado filling','Siopao, asado',
 'Mixed Dishes and Fast Foods',
 NULL, 250, 8.5, 6.5, 40.5, 1.2,
 25, 1.5, 310, 90,
 5, 0.5, 0.140, 0.120, 2.00,
 '1 medium', 80, '1 large', 120, NULL, NULL),

('15-027','Banana cue (fried banana on stick)','Banana cue',
 'Mixed Dishes and Fast Foods',
 NULL, 197, 1.4, 3.5, 43.0, 2.5,
 8, 0.5, 2, 28,
 10, 9.5, 0.050, 0.040, 0.60,
 '1 piece', 80, '2 pieces', 160, NULL, NULL),

('15-028','Fishball, fried','Fishball, prito',
 'Mixed Dishes and Fast Foods',
 NULL, 175, 12.5, 9.5, 8.5, 0.3,
 25, 1.2, 420, 140,
 10, 0, 0.040, 0.060, 1.80,
 '5 pieces', 75, '10 pieces', 150, NULL, NULL),

('15-029','Puto (steamed rice cake)','Puto',
 'Mixed Dishes and Fast Foods',
 NULL, 185, 3.5, 2.0, 38.5, 0.5,
 15, 0.5, 95, 40,
 0, 0, 0.020, 0.020, 0.20,
 '2 pieces', 70, '1 piece', 35, NULL, NULL),

('15-030','Bibingka (baked rice cake)','Bibingka',
 'Mixed Dishes and Fast Foods',
 NULL, 230, 4.5, 8.0, 36.5, 0.8,
 60, 0.8, 140, 70,
 30, 0, 0.020, 0.060, 0.30,
 '1 piece', 100, NULL, NULL, NULL, NULL),

('15-031','Biko (sticky rice cake with coconut milk)','Biko',
 'Mixed Dishes and Fast Foods',
 NULL, 285, 3.0, 5.5, 57.5, 0.5,
 15, 0.8, 15, 45,
 0, 0, 0.030, 0.020, 0.30,
 '1 piece', 80, '1 cup', 160, NULL, NULL),

('15-032','Balut (developing duck egg, boiled)','Balut',
 'Mixed Dishes and Fast Foods',
 NULL, 188, 13.7, 14.2, 0.9, 0.0,
 46, 3.0, 145, 210,
 295, 0, 0.140, 0.510, 0.10,
 '1 piece (75g)', 75, NULL, NULL, NULL, NULL),

('15-033','Halo-halo (mixed shaved ice dessert)','Halo-halo',
 'Mixed Dishes and Fast Foods',
 NULL, 145, 3.5, 4.0, 25.0, 0.8,
 90, 0.5, 45, 65,
 20, 3.0, 0.040, 0.100, 0.20,
 '1 glass (300g)', 300, NULL, NULL, NULL, NULL),

('15-034','Kwek-kwek (deep-fried quail egg in orange batter)','Kwek-kwek',
 'Mixed Dishes and Fast Foods',
 NULL, 235, 10.5, 15.0, 16.5, 0.5,
 35, 2.5, 380, 180,
 60, 0, 0.080, 0.380, 0.60,
 '5 pieces', 100, '1 piece', 20, NULL, NULL),

('15-035','Chichirya / potato chips','Chichirya/chips',
 'Mixed Dishes and Fast Foods',
 NULL, 536, 7.0, 35.0, 52.0, 4.8,
 14, 0.8, 560, 130,
 0, 14.0, 0.130, 0.050, 2.80,
 '1 small bag (30g)', 30, '1 handful', 20, NULL, NULL);
