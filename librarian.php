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

if (isset($_POST['delete_book']) && isset($_POST['book_id'])) {
    $book_id = $conn->real_escape_string($_POST['book_id']);
    
    $sql_delete = "DELETE FROM books WHERE book_id = ?";
    
    if ($stmt = $conn->prepare($sql_delete)) {
        $stmt->bind_param("i", $book_id);
        if ($stmt->execute()) {
            $success_message = "✅ Book ID **$book_id** successfully removed from the catalog.";
        } else {
            $error_message = "❌ Error deleting book: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = "❌ Error preparing delete statement: " . $conn->error;
    }
}

if (isset($_POST['update_book'])) {
    $book_id = $conn->real_escape_string($_POST['edit_book_id']);
    $title = $conn->real_escape_string($_POST['edit_title']);
    $author = $conn->real_escape_string($_POST['edit_author']);
    $quantity = (int)$_POST['edit_quantity'];
    
    $current_data = $conn->query("SELECT quantity, available_copies FROM books WHERE book_id = $book_id")->fetch_assoc();
    
    if ($current_data) {
        $quantity_diff = $quantity - $current_data['quantity'];
        $new_available_copies = $current_data['available_copies'] + $quantity_diff;
        if ($new_available_copies < 0) { $new_available_copies = 0; }
    } else {
        $error_message = "❌ Cannot update: Book ID not found.";
        $new_available_copies = $quantity; 
    }

    $sql_update = "UPDATE books SET title = ?, author = ?, quantity = ?, available_copies = ? WHERE book_id = ?";
    
    if ($stmt = $conn->prepare($sql_update)) {
        $stmt->bind_param("ssiii", $title, $author, $quantity, $new_available_copies, $book_id);
        if ($stmt->execute()) {
            $success_message = "✅ Book ID **$book_id** details updated successfully (Title: $title).";
        } else {
            $error_message = "❌ Error updating book: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = "❌ Error preparing update statement: " . $conn->error;
    }
    
    header("Location: librarian.php?success=" . urlencode($success_message));
    exit();
}