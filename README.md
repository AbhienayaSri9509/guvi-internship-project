# GUVI Internship Project – User Authentication & Profile System

This project is built as part of the **GUVI Internship Task**.  
It provides a simple authentication system with **Signup → Login → Profile (Update)** flow.  

---

## 🚀 Features
- **Signup Page** – Register new users with Name, Email, and Password.
- **Login Page** – Authenticate users with AJAX requests.
- **Profile Page** – View and update user details (Age, DOB, Contact).
- **Responsive UI** – Built with Bootstrap + custom CSS.
- **Security**:
  - Passwords hashed using `password_hash`.
  - MySQL prepared statements (no raw SQL).
  - Session maintained in **browser LocalStorage**.
  - **Redis** used for backend session storage.
  - Optional user data backup in **MongoDB**.

---

## 🛠️ Tech Stack
- **Frontend:** HTML, CSS, JavaScript (jQuery + AJAX), Bootstrap
- **Backend:** PHP 8+
- **Database:** MySQL (with PDO)
- **Session Storage:** Redis
- **Optional:** MongoDB for storing user profile copies

---

## 📂 Project Structure
project-root/
│
├── css/
│ └── style.css
│
├── js/
│ ├── signup.js
│ ├── login.js
│ └── profile.js
│
├── php/
│ ├── db.php # DB + Redis + Mongo connection
│ ├── signup.php # User registration
│ ├── login.php # User login + session creation
│ ├── get_profile.php # Fetch profile details
│ └── update_profile.php # Update profile details
│
├── signup.html
├── login.html
├── profile.html
└── README.md

yaml
Copy code

---

## ⚙️ Setup Instructions

1. **Clone repository**
   ```bash
   git clone https://github.com/YOUR-USERNAME/guvi-internship-project.git
   cd guvi-internship-project
Setup Database

Create MySQL DB:

sql
Copy code
CREATE DATABASE guvi_intern;
USE guvi_intern;
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  age INT NULL,
  dob DATE NULL,
  contact VARCHAR(30) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
Configure PHP Database Connection

Open php/db.php and update:

php
Copy code
'user' => 'root',
'pass' => '',   // your MySQL password
Run XAMPP services

Start Apache and MySQL from XAMPP.

Start Redis (redis-server) and MongoDB if available.

Access Project

http://localhost/project-root/signup.html

![WhatsApp Image 2025-10-01 at 08 28 46_a0b1b13b](https://github.com/user-attachments/assets/4d312b3e-7622-4c7b-af4b-d2e1e4c6673b)


![WhatsApp Image 2025-09-30 at 17 27 14_42d36100](https://github.com/user-attachments/assets/4e71bf6d-d9ce-4dd2-8c0d-2010e22b22bc)

![WhatsApp Image 2025-09-30 at 09 18 21_9541f503](https://github.com/user-attachments/assets/dd51802a-6760-476d-8baa-980e069d078d)




🔑 Login Flow
Register via signup.html.

Login via login.html.

On successful login:

A session token is returned from backend.

Token stored in localStorage.

Redirected to profile.html.

Profile page loads user details with AJAX and allows updates.


👨‍💻 Author
ABHIENAYA SRI
📧abhienayasris@gmail.com
🔗 https://github.com/AbhienayaSri9509
linkedin : https://www.linkedin.com/in/abhienaya-sri-572020254/

