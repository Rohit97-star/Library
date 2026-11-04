-- Shelfwise Library Management System database
CREATE DATABASE IF NOT EXISTS library_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE library_management;

CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS books (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(160) NOT NULL,
    category VARCHAR(100) NOT NULL,
    isbn VARCHAR(30) NOT NULL UNIQUE,
    quantity INT UNSIGNED NOT NULL DEFAULT 0,
    available_quantity INT UNSIGNED NOT NULL DEFAULT 0,
    added_date DATE NOT NULL DEFAULT (CURRENT_DATE),
    CHECK (available_quantity <= quantity),
    INDEX idx_books_title (title),
    INDEX idx_books_author (author)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS students (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(140) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    phone VARCHAR(30) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS issued_books (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    book_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    issue_date DATE NOT NULL,
    return_date DATE NOT NULL,
    status ENUM('issued', 'returned') NOT NULL DEFAULT 'issued',
    returned_at DATETIME NULL,
    CONSTRAINT fk_issued_book FOREIGN KEY (book_id) REFERENCES books(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_issued_student FOREIGN KEY (student_id) REFERENCES students(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_issued_status (status),
    INDEX idx_issued_return_date (return_date)
) ENGINE=InnoDB;
