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
    
    // SQL Query: DELETE a book based on its ID
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


$books = [];
$sql_select_books = "SELECT book_id, title, author, isbn, publication_year, quantity, available_copies FROM books";
$where_clauses = [];
$params = [];
$param_types = '';

if (!empty($search_query)) {
    $where_clauses[] = "(title LIKE ? OR author LIKE ? OR isbn LIKE ?)";
    $like_query = "%" . $search_query . "%";
    $params[] = $like_query;
    $params[] = $like_query;
    $params[] = $like_query;
    $param_types .= 'sss';
}

if (!empty($where_clauses)) {
    $sql_select_books .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql_select_books .= " ORDER BY book_id ASC";


if (!empty($params)) {
    if ($stmt = $conn->prepare($sql_select_books)) {
        $stmt->bind_param($param_types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    }
} else {
    $result = $conn->query($sql_select_books);
}

if (isset($result) && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librarian Dashboard - Book Management</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 1200px; margin: 40px auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 15px rgba(0, 0, 0, 0.1); }
        h2 { text-align: center; color: #333; border-bottom: 2px solid #5cb85c; padding-bottom: 10px; }
        h3 { color: #5cb85c; margin-top: 25px; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 4px; font-weight: bold; }
        .success { background-color: #dff0d8; color: #3c763d; border: 1px solid #d6e9c6; border-left: 5px solid #5cb85c; }
        .error { background-color: #f2dede; color: #a94442; border: 1px solid #ebccd1; border-left: 5px solid #d9534f; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #5cb85c; color: white; }
        .action-form { display: inline-block; margin: 0; padding: 0;}
        .action-button { 
            padding: 6px 10px; border: none; border-radius: 4px; cursor: pointer; color: white; 
            font-size: 14px; text-decoration: none; display: inline-block; text-align: center;
        }
        .edit { background-color: #f0ad4e; }
        .delete { background-color: #d9534f; }
        .save { background-color: #5cb85c; }
        .cancel { background-color: #999; }
        .logout { float: right; padding: 5px 10px; background-color: #333; color: white; border-radius: 4px; text-decoration: none; }
        .edit-input { width: 90%; padding: 5px; border: 1px solid #ccc; border-radius: 3px; box-sizing: border-box; }
        td.actions { min-width: 200px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="login.php?logout=true" class="logout">Logout</a>
        <h2>📚 LIBRARIAN DASHBOARD</h2>
        
        <?php 
        if ($success_message) { echo "<div class='message success'>$success_message</div>"; }
        if ($error_message) { echo "<div class='message error'>$error_message</div>"; }
        ?>

        <h3>➕ Add New Book</h3>
        <form method="POST" action="librarian.php" style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
            <input type="text" name="title" placeholder="Title" required style="flex-grow: 1;">
            <input type="text" name="author" placeholder="Author" required style="flex-grow: 1;">
            <input type="text" name="isbn" placeholder="ISBN" required style="flex-grow: 1;">
            <input type="number" name="publication_year" placeholder="Year" required min="1000" max="<?php echo date("Y"); ?>" style="width: 100px;">
            <input type="number" name="quantity" placeholder="Quantity" required min="1" style="width: 100px;">
            <button type="submit" name="add_book" class="action-button save" style="flex-grow: 0; background-color: #337ab7;">Add Book</button>
        </form>

        <h3>🔍 Book Catalog Management</h3>
        <form method="GET" action="librarian.php" style="margin-bottom: 15px;">
            <input type="text" name="search" placeholder="Search by Title, Author, or ISBN..." value="<?php echo htmlspecialchars($search_query); ?>" style="width: 300px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            <button type="submit" class="action-button edit" style="background-color: #5bc0de;">Search</button>
            <a href="librarian.php" class="action-button cancel">Clear Search</a>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>ISBN</th>
                    <th>Year</th>
                    <th>Qty (Total)</th>
                    <th>Available</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($books)): ?>
                    <tr><td colspan="8">No books found in the catalog.</td></tr>
                <?php else: ?>
                    <?php foreach ($books as $book): ?>
                        
                        <?php if ($edit_id == $book['book_id']): ?>
                            <form method="POST" action="librarian.php">
                                <input type="hidden" name="edit_book_id" value="<?php echo htmlspecialchars($book['book_id']); ?>">
                                <tr>
                                    <td><?php echo htmlspecialchars($book['book_id']); ?></td>
                                    <td><input type="text" name="edit_title" value="<?php echo htmlspecialchars($book['title']); ?>" required class="edit-input"></td>
                                    <td><input type="text" name="edit_author" value="<?php echo htmlspecialchars($book['author']); ?>" required class="edit-input"></td>
                                    <td><?php echo htmlspecialchars($book['isbn']); ?></td> <td><?php echo htmlspecialchars($book['publication_year']); ?></td>
                                    <td><input type="number" name="edit_quantity" value="<?php echo htmlspecialchars($book['quantity']); ?>" required min="0" class="edit-input"></td>
                                    <td><?php echo htmlspecialchars($book['available_copies']); ?></td>
                                    <td class="actions">
                                        <button type="submit" name="update_book" class="action-button save">💾 Save</button>
                                        <a href="librarian.php" class="action-button cancel">Cancel</a>
                                    </td>
                                </tr>
                            </form>
                        <?php else: ?>
                            <tr>
                                <td><?php echo htmlspecialchars($book['book_id']); ?></td>
                                <td><?php echo htmlspecialchars($book['title']); ?></td>
                                <td><?php echo htmlspecialchars($book['author']); ?></td>
                                <td><?php echo htmlspecialchars($book['isbn']); ?></td> <td><?php echo htmlspecialchars($book['publication_year']); ?></td>
                                <td><?php echo htmlspecialchars($book['quantity']); ?></td>
                                <td><?php echo htmlspecialchars($book['available_copies']); ?></td>
                                <td class="actions">
                                    <a href="librarian.php?edit_id=<?php echo htmlspecialchars($book['book_id']); ?>" class="action-button edit">✏️ Edit</a>
                                    <form class="action-form" method="POST" action="librarian.php" onsubmit="return confirm('WARNING: Permanently remove: <?php echo htmlspecialchars($book['title']); ?>?');">
                                        <input type="hidden" name="book_id" value="<?php echo htmlspecialchars($book['book_id']); ?>">
                                        <button type="submit" name="delete_book" class="action-button delete">🗑️ Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>