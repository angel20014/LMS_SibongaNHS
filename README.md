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


