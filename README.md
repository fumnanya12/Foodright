# 🍽️ FoodRight – Recipe & Nutrition Web App

## 📌 Overview
**FoodRight** is a PHP-based content management system (CMS) that allows users to explore, search, and manage recipes while promoting healthier eating habits.

Users can browse recipes, filter by categories, and view detailed cooking instructions. Administrators can securely manage recipe content through a protected interface.

---

## 🎯 Features

### 🔍 User Features
- Browse and view recipes
- Search recipes by keyword
- Filter recipes by category (e.g., Breakfast, Lunch, Dinner, Dessert)
- Sort recipes by title, created date, or updated date
- Pagination for easy navigation
- View detailed recipe pages (ingredients, instructions, cook time, servings)
- SEO-friendly URLs (`/id/slug/`)
- Responsive design for mobile and desktop

### 🔐 Admin Features
- Secure login/logout system (session-based authentication)
- Create, update, and delete recipes (CRUD operations)
- Upload and manage recipe images
- Edit recipe details:
  - Title
  - Description
  - Category
  - Cook time
  - Servings
  - Ingredients
  - Instructions

### ⚙️ Additional Features
- Form validation and input sanitization
- Error handling (404 pages, database errors)
- External API integration for importing recipes

---

## 🛠️ Technologies Used

- **HTML5** – Structure of web pages  
- **CSS3** – Styling and responsive design (Flexbox)  
- **PHP** – Server-side logic  
- **PDO (PHP Data Objects)** – Secure database interactions  
- **MySQL** – Relational database  
- **Apache (.htaccess)** – URL rewriting for SEO-friendly links  
- **XAMPP** – Local development environment  
- **JSON API** – External data integration  
- **Git & GitHub** – Version control  

---

## 🗂️ Project Structure





---

## 🧩 Database Schema (Simplified)

**Table: recipes**

| Column        | Type         | Description |
|--------------|-------------|------------|
| recipe_id    | INT (PK)     | Unique ID |
| user_id      | INT          | Admin user |
| title        | VARCHAR(255) | Recipe title |
| description  | TEXT         | Recipe summary |
| category     | VARCHAR(50)  | Recipe category |
| cook_time    | INT          | Cooking time |
| servings     | INT          | Number of servings |
| ingredients  | TEXT         | Ingredients list |
| instructions | TEXT         | Cooking steps |
| imagepath    | VARCHAR      | Image path |
| slug         | VARCHAR      | SEO-friendly URL |
| created_at   | DATETIME     | Created timestamp |
| updated_time | DATETIME     | Last update |

---

## 🚀 Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/foodright.git

2. Start Apache and MySQL from XAMPP

3. Create a database in phpMyAdmin:
- Name: `foodright` (or your custom name)

4. Import your SQL schema

5. Update database connection in `connect.php`:
    ````php
    define('DB_DSN','mysql:host=localhost;dbname=foodright;charset=utf8');
    define('DB_USER','root');
    define('DB_PASS','');`
6. Open in browser:


---

## 🔑 Admin Access

Admins are authenticated using PHP sessions:

    ```php
    if (!isset($_SESSION['username']['user_id']) || $_SESSION['username']['role'] !== 'admin') {
    header("Location: index.php");
    exit;
    }
## 📸 Image Handling

- Images stored in `/pictures/` directory  
- Default placeholder used if no image exists  
- Database may store `"No image"` as fallback  

---

## 🧪 Future Improvements

- User accounts (non-admin users)  
- Favorites / saved recipes  
- Calorie calculator  
- Nutrition API integration  
- Ratings and reviews  
- Cloud deployment  

---

## 📚 Learning Outcomes

This project demonstrates:

- Full-stack web development using PHP and MySQL  
- Secure database interactions with PDO  
- Session-based authentication  
- API integration  
- SEO-friendly routing  
- Responsive web design  

---

## 👨‍💻 Author

**OBI**  
Full Stack Web Development Student  
Red River Polytechnic  

---

## 📄 License

This project is for educational purposes.

