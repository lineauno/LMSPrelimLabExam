<?php

$servername = "db";
$username = "root"; 
$password = "rootpassword"; 
$dbname = "lms_db"; 

$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("Database Connection failed: " . $conn->connect_error);
}

$sql_create_db = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql_create_db) === TRUE) {
} else {
    die("Error creating database: " . $conn->error);
}

$conn->select_db($dbname);

$sql_create_books_table = "
CREATE TABLE IF NOT EXISTS books (
    book_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(100) NOT NULL,
    isbn VARCHAR(20) UNIQUE,
    publication_year YEAR,
    quantity INT NOT NULL DEFAULT 1,
    available_copies INT NOT NULL DEFAULT 1, 
    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);";

if (!$conn->query($sql_create_books_table)) {
    echo "Error creating books table: " . $conn->error;
}

$sql_create_users_table = "
CREATE TABLE IF NOT EXISTS users (
    user_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL, 
    role ENUM('librarian', 'user') NOT NULL, 
    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);";

if (!$conn->query($sql_create_users_table)) {
    echo "Error creating users table: " . $conn->error;
}


$books_to_insert = [
    ["Half a Soul", "Olivia Atwater", "978-0358253907", 2020, 5],
    ["The Ballad of Never After", "Stephanie Garber", "978-1250266023", 2022, 3],
    ["Cinder", "Marissa Meyer", "978-1250007817", 2012, 7],
    ["If You Could See the Sun", "Ann Liang", "978-1335425624", 2022, 4],
    ["Divine Rivals", "Rebecca Ross", "978-1250803302", 2023, 6],
];

foreach ($books_to_insert as $book) {
    $title = $conn->real_escape_string($book[0]);
    $author = $conn->real_escape_string($book[1]);
    $isbn = $conn->real_escape_string($book[2]);
    $year = $book[3];
    $quantity = $book[4];


    $sql_insert_book = "
    INSERT INTO books (title, author, isbn, publication_year, quantity, available_copies)
    SELECT tmp.title, tmp.author, tmp.isbn, tmp.publication_year, tmp.quantity, tmp.available_copies
    FROM (
        SELECT 
            '$title' AS title, 
            '$author' AS author, 
            '$isbn' AS isbn, 
            '$year' AS publication_year, 
            $quantity AS quantity, 
            $quantity AS available_copies
    ) AS tmp
    WHERE NOT EXISTS (
        SELECT isbn FROM books WHERE isbn = '$isbn'
    ) LIMIT 1;
    ";
    
    $conn->query($sql_insert_book);
}

$librarian_password = password_hash("admin123", PASSWORD_DEFAULT);
$user_password = password_hash("user123", PASSWORD_DEFAULT);

$initial_users = [
    ["librarian_user", $librarian_password, 'librarian'],
    ["standard_user", $user_password, 'user'],
];

foreach ($initial_users as $user) {
    $username = $conn->real_escape_string($user[0]);
    $hashed_password = $conn->real_escape_string($user[1]);
    $role = $conn->real_escape_string($user[2]);

    $sql_insert_user = "
    INSERT INTO users (username, password_hash, role)
    SELECT * FROM (SELECT '$username', '$hashed_password', '$role') AS tmp
    WHERE NOT EXISTS (
        SELECT username FROM users WHERE username = '$username'
    ) LIMIT 1;
    ";
    $conn->query($sql_insert_user);
}

?>