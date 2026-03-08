# Philippine 24-Hour Food Recall System
**Intake24-style · FNRI Food Composition Table · NNS 2024 / FNRI-DOST**

---

## Stack
- **Backend**: PHP 8.0+ (no framework)
- **Database**: MySQL 8.0+ / MariaDB 10.5+
- **Frontend**: Vanilla JS + CSS (no build tools)

## Setup

### 1. Database

```bash
mysql -u root -p < db/schema.sql
```

This creates the `food_recall_db` database, all tables, demo users, sample households, and the FNRI FCT seed data.

### 2. Web Server

Point a virtual host (Apache/Nginx) or use PHP's built-in server:

```bash
cd /path/to/food-recall-web
php -S localhost:8080
```

Then open: **http://localhost:8080**

### 3. Configure DB credentials

Edit `includes/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'food_recall_db');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
```

---

## Demo Accounts

| Username | Password | Role |
|---|---|---|
| `supervisor01` | `password123` | Supervisor |
| `interviewer01` | `password123` | Interviewer |
| `interviewer02` | `password123` | Interviewer |

---

## Features

### Interviewer Portal
- **Household list** — assigned households with status and progress
- **Respondent selection** — WRA and children 0–5 per household
- **5-Pass Interview**:
  1. **Quick List** — fast entry of all foods consumed in 24 hours
  2. **Forgotten Foods** — 10 Philippine-specific probe categories (includes breastmilk for children)
  3. **Time & Occasion** — assign meal occasion and time to each food
  4. **Detail Cycle** — FNRI FCT search with autocomplete + portion sizes + live nutrient calculation
  5. **Review** — full meal summary with total energy, protein, fat, carbs; submit

### FCT Food Search (Intake24 style)
- Search by English or Filipino food name
- Filter by food group
- Select household measure (cup, piece, tablespoon) with gram equivalents
- Live nutrient calculation per amount consumed

### Supervisor Portal
- **Dashboard stats** — total HH, completed, Day 2 pending
- **Quota management** — set targets per interviewer (HH, WRA, children)
- **Progress bars** per interviewer
- **Day 2 tracking** — 20% probability auto-assigned, 3–10 day offset, overdue flag

---

## FNRI FCT Data
~130 Philippine foods across 14 food groups:
- Cereals and Cereal Products
- Starchy Roots and Tubers
- Fats and Oils
- Fish and Shellfish
- Meat and Poultry
- Eggs
- Milk and Milk Products
- Dried Beans, Nuts, and Seeds
- Vegetables
- Fruits
- Non-Alcoholic Beverages
- Alcoholic Beverages
- Composite Filipino Dishes (adobo, sinigang, tinola, pancit, etc.)
- Snacks and Fast Food
- Condiments and Sauces

Values per 100g edible portion: Energy (kcal), Protein, Fat, Carbohydrates, Fiber, Calcium, Iron, Vitamin A.

---

## File Structure

```
food-recall-web/
├── index.php             # Login
├── interviewer.php       # Interviewer portal
├── supervisor.php        # Supervisor dashboard
├── logout.php
├── api/
│   ├── auth.php          # Login/logout
│   ├── households.php    # Household CRUD
│   ├── recall.php        # Recall session + food items
│   ├── fct.php           # FNRI FCT search
│   └── quota.php         # Quota management
├── includes/
│   ├── config.php
│   ├── db.php
│   └── auth.php
├── assets/
│   ├── css/app.css
│   └── js/
│       ├── app.js
│       ├── interview.js  # 5-pass engine
│       └── supervisor.js
└── db/
    └── schema.sql        # Full schema + FNRI FCT seed
```
