# LMS_SibongaNHS

The **SNHS Library Management System** is a web-based application designed to streamline the daily operations of the SNHS Library.  
It helps librarians, students, and teachers manage books, track borrowings and returns, monitor overdue items, and generate usage reports.  
This system reduces manual recording, minimizes lost book cases, and improves access to library resources.

## 👥 User Roles
- **Admin** – Manages the entire system, users, book inventory, and generates reports  
- **Students** – Can borrow and return books, view borrowing history, and receive overdue notifications  
- **Teachers** – Can borrow and return books, monitor their borrowing history, and request specific books  

## ✨ Features
- 🔑 Role-based authentication (Admin, Students, and Teachers)  
- 📚 Book inventory management (add, update, categorize, and monitor stock)  
- 📖 Borrowing and returning system with automated logs  
- ⚠️ **Overdue Book Notifications** (alerts students/teachers and librarians of overdue books)  
- 💵 Fine calculation for overdue or lost items  
- 📊 Reports generation (borrowed books, popular titles, overdue accounts)  
- ⚡ Responsive and user-friendly interface  

## 🛠️ Technologies Used
- **Laravel (PHP Framework)** – For backend logic and MVC structure  
- **MySQL** – Database management  
- **HTML, CSS, JavaScript** – Frontend  
- **Bootstrap / TailwindCSS** – Styling  
- **Blade Templates** – For Laravel views  

## 📂 Repository Structure
snhs-library-management-system/
│
├── app/ # Laravel app logic (Controllers, Models)
├── resources/views/ # Blade templates (Frontend UI)
├── public/ # Public assets (CSS, JS, Images)
├── database/ # Database migrations and seeders
├── routes/ # Web routes
└── README.md # Project documentation


# 📥 How to Download, Install, and Use

Follow these steps to set up and run the SNHS Library Management System on your computer:

## 1️⃣ Download / Clone the Repository
- Click the green **Code** button on this repository  
- Choose **Download ZIP** and extract it, OR clone using Git:
  ```
  git clone https://github.com/your-username/snhs-library-management-system.git
2️⃣ Install Requirements
Make sure you have the following installed:

XAMPP (for PHP & MySQL)

Composer (for Laravel dependencies)

Node.js (for frontend dependencies, optional)

3️⃣ Set Up the Project
Open the project folder in your terminal/command prompt

Install Laravel dependencies:
composer install
Copy the example environment file:

cp .env.example .env
Generate the application key:


php artisan key:generate
4️⃣ Database Configuration
Start XAMPP and make sure MySQL and Apache are running

Create a database (example: snhs_library) in phpMyAdmin

Update the .env file with your database info:

makefile
Copy code
DB_DATABASE=snhs_library
DB_USERNAME=root
DB_PASSWORD=
Run migrations to set up tables:

php artisan migrate
5️⃣ Run the Project
Start the Laravel development server:


php artisan serve
Open your browser and visit:


http://127.0.0.1:8000
6️⃣ Default Login Accounts
Use these credentials to access the system after installation:

Admin → admin@gmail.com / password123

Student → student@gmail.com / password123

Teacher → teacher@gmail.com / password123

7️⃣ Usage
Admin can add/manage books, users, and generate reports

Students can borrow/return books and view their records

Teachers can borrow/return books and request resources





