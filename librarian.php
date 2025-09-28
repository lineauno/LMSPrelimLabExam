<?php
require_once 'database.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'librarian') {
    header("Location: login.php");
    exit();
}

$success_message = '';
$error_message = '';
$edit_id = $_GET['edit_id'] ?? null;
$search_query = $_GET['search'] ?? '';

if (isset($_GET['success'])) {
    $success_message = htmlspecialchars($_GET['success']);
}

if (isset($_POST['add_book'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $author = $conn->real_escape_string($_POST['author']);
    $isbn = $conn->real_escape_string($_POST['isbn']);
    $year = (int)$_POST['publication_year'];
    $quantity = (int)$_POST['quantity'];
    
    $sql_insert = "INSERT INTO books (title, author, isbn, publication_year, quantity, available_copies) VALUES (?, ?, ?, ?, ?, ?)";
    
    if ($stmt = $conn->prepare($sql_insert)) {
        $stmt->bind_param("ssisii", $title, $author, $isbn, $year, $quantity, $quantity);
        if ($stmt->execute()) {
            $success_message = "✅ Book $title successfully added to the catalog.";
            header("Location: librarian.php?success=" . urlencode($success_message));
            exit();
        } else {
            $error_message = "❌ Error adding book: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = "❌ Error preparing insert statement: " . $conn->error;
    }
}
