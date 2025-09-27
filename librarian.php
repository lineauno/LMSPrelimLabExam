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
$search = '';

if (isset($_GET['success'])) {
    $success_message = htmlspecialchars($_GET['success']);
}

if (isset($_POST['delete_book']) && isset($_POST['book_id'])) {
    $book_id = $conn->real_escape_string($_POST['book_id']);
    $sql_delete = "DELETE FROM books WHERE book_id = ?";
    if ($stmt = $conn->prepare($sql_delete)) {
        $stmt->bind_param("i", $book_id);
        if ($stmt->execute()) {
            $success_message = "✅ Book ID $book_id successfully removed.";
        } else {
            $error_message = "❌ Error deleting book: " . $stmt->error;
        }
        $stmt->close();
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
        if ($new_available_copies < 0) $new_available_copies = 0;
    } else {
        $error_message = "❌ Cannot update: Book ID not found.";
        $new_available_copies = $quantity;
    }

    $sql_update = "UPDATE books SET title = ?, author = ?, quantity = ?, available_copies = ? WHERE book_id = ?";
    if ($stmt = $conn->prepare($sql_update)) {
        $stmt->bind_param("ssiii", $title, $author, $quantity, $new_available_copies, $book_id);
        if ($stmt->execute()) {
            $success_message = "✅ Book ID $book_id updated successfully.";
        } else {
            $error_message = "❌ Error updating book: " . $stmt->error;
        }
        $stmt->close();
    }
    header("Location: librarian.php?success=" . urlencode($success_message));
    exit();
}

if (isset($_POST['search'])) {
    $search = $conn->real_escape_string($_POST['search']);
}

$sql = "SELECT * FROM books";
if ($search) {
    $sql .= " WHERE title LIKE '%$search%' OR author LIKE '%$search%' OR isbn LIKE '%$search%'";
}
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Librarian - Manage Books</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 1100px; margin: 40px auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; }
        form.search { text-align: center; margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 10px; }
        th { background: #5cb85c; color: #fff; }
        .actions { display: flex; gap: 5px; }
        button, a { padding: 5px 10px; border: none; border-radius: 4px; text-decoration: none; cursor: pointer; }
        .delete { background: #d9534f; color: #fff; }
        .edit { background: #0275d8; color: #fff; }
    </style>
</head>
<body>
    <div class="container">
        <h2>📚 Librarian Dashboard</h2>

        <?php if ($success_message): ?>
            <p style="color:green;"><?= $success_message ?></p>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <p style="color:red;"><?= $error_message ?></p>
        <?php endif; ?>

        <form method="post" class="search">
            <input type="text" name="search" placeholder="Search books by title, author, or ISBN" value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
        </form>

        <table>
            <tr>
                <th>ID</th><th>Title</th><th>Author</th><th>ISBN</th><th>Quantity</th><th>Available</th><th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['book_id'] ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= htmlspecialchars($row['author']) ?></td>
                    <td><?= htmlspecialchars($row['isbn']) ?></td>
                    <td><?= $row['quantity'] ?></td>
                    <td><?= $row['available_copies'] ?></td>
                    <td class="actions">
                        <form method="post" action="librarian.php" style="display:inline;">
                            <input type="hidden" name="book_id" value="<?= $row['book_id'] ?>">
                            <button type="submit" name="delete_book" class="delete" onclick="return confirm('Delete this book?')">Delete</button>
                        </form>
                        <a href="edit_book.php?id=<?= $row['book_id'] ?>" class="edit">Edit</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
