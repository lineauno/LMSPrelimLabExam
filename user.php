<?php
require_once 'database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'librarian') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';
$search_query = $_GET['search'] ?? ''; 

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['return_action'])) {
    $borrow_id = intval($_POST['borrow_id']);
    $book_id   = intval($_POST['book_id']);

    $conn->begin_transaction();

    try {
        $stmt_return = $conn->prepare("UPDATE borrowed_books SET return_date = NOW() WHERE borrow_id = ? AND user_id = ? AND return_date IS NULL");
        $stmt_return->bind_param("ii", $borrow_id, $user_id);
        
        if (!$stmt_return->execute() || $stmt_return->affected_rows === 0) {
            throw new Exception("Error marking book as returned or record not found.");
        }
        $stmt_return->close();

        $stmt_update_book = $conn->prepare("UPDATE books SET available_copies = available_copies + 1 WHERE book_id = ?");
        $stmt_update_book->bind_param("i", $book_id);
        
        if (!$stmt_update_book->execute()) {
            throw new Exception("Error updating book copies.");
        }
        $stmt_update_book->close();

        $conn->commit();
        $message = "✅ Book returned successfully!";
        header("Location: user.php?msg=" . urlencode($message));
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $error = "❌ Return Error: " . $e->getMessage();
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['borrow_action'])) {
    $book_id = intval($_POST['book_id']);

    $conn->begin_transaction();

    try {
        $stmt_update = $conn->prepare("UPDATE books SET available_copies = available_copies - 1 WHERE book_id = ? AND available_copies > 0");
        $stmt_update->bind_param("i", $book_id);
        
        if (!$stmt_update->execute() || $stmt_update->affected_rows === 0) {
            throw new Exception("Book is no longer available or not found.");
        }
        $stmt_update->close();
        
        $stmt_insert = $conn->prepare("INSERT INTO borrowed_books (user_id, book_id, borrow_date) VALUES (?, ?, NOW())");
        $stmt_insert->bind_param("ii", $user_id, $book_id);
        
        if (!$stmt_insert->execute()) {
            throw new Exception("Error recording borrow.");
        }
        $stmt_insert->close();

        $conn->commit();
        $message = "✅ Book borrowed successfully!";
        header("Location: user.php?msg=" . urlencode($message));
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $error = "❌ Borrow Error: " . $e->getMessage();
    }
}

if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
}


$sql_available = "SELECT book_id, title, author, publication_year, available_copies 
                  FROM books 
                  WHERE available_copies > 0";
$params = [];
$param_types = '';

if (!empty($search_query)) {
    $safe_search = "%" . $search_query . "%";
    $sql_available .= " AND (title LIKE ? OR author LIKE ?)";
    $params[] = $safe_search;
    $params[] = $safe_search;
    $param_types .= 'ss';
}

$sql_available .= " ORDER BY title ASC";

if (!empty($params)) {
    if ($stmt_available = $conn->prepare($sql_available)) {
        $stmt_available->bind_param($param_types, ...$params);
        $stmt_available->execute();
        $result_available = $stmt_available->get_result();
        $stmt_available->close();
    } else {
        $error = "❌ Error preparing search query: " . $conn->error;
        $result_available = false; 
    }
} else {
    $result_available = $conn->query($sql_available);
}


$books_borrowed = [];
$sql_borrowed = "SELECT bb.borrow_id, b.book_id, b.title, b.author, b.publication_year
                 FROM borrowed_books bb
                 JOIN books b ON bb.book_id = b.book_id
                 WHERE bb.user_id = ? AND bb.return_date IS NULL";

if ($stmt_borrowed = $conn->prepare($sql_borrowed)) {
    $stmt_borrowed->bind_param("i", $user_id);
    $stmt_borrowed->execute();
    $result_borrowed = $stmt_borrowed->get_result();
    while ($row = $result_borrowed->fetch_assoc()) {
        $books_borrowed[] = $row;
    }
    $stmt_borrowed->close();
} else {
    $error = "❌ Error preparing borrowed books query: " . $conn->error;
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
    <style>
        :root {
            --green-primary: #28a745;
            --green-dark: #1e7e34;
        }

        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        h2 { color: #333; border-bottom: 2px solid #ccc; padding-bottom: 10px; }
        
        .centered-title { text-align: center; }

        .nav-links a { 
            text-decoration: none; 
            padding: 8px 12px; 
            border-radius: 4px; 
            background-color: var(--green-primary); 
            color: white; 
            transition: background-color 0.3s;
        }
        .nav-links a:hover { 
            background-color: var(--green-dark); 
        }
        .nav-links {
            text-align: right; 
            margin-bottom: 15px;
        }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: var(--green-primary); color: white; }

        .action-button {
            background-color: var(--green-primary); 
            color: white; 
            border: none; 
            padding: 5px 10px; 
            border-radius: 3px; 
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .action-button.search-button {
            background-color: #007bff;
        }
        .action-button:hover {
            background-color: var(--green-dark);
        }
        .action-button.search-button:hover {
            background-color: #0056b3;
        }

        .search-container { margin-bottom: 20px; }
        .search-container input[type="text"] {
            padding: 8px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .message { padding: 10px; margin-top: 15px; border-radius: 4px; font-weight: bold; text-align: center; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="container">
    <div class="nav-links">
        <a href="login.php?logout=true">Logout</a>
    </div>

    <h2 class="centered-title">📚 USER DASHBOARD</h2>
    
    <?php if ($message): ?>
        <p class="message success"><?php echo $message; ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="message error"><?php echo $error; ?></p>
    <?php endif; ?>

    <div class="search-container">
        <form method="GET" action="user.php">
            <input type="text" name="search" placeholder="Search by Title or Author..." 
                   value="<?php echo htmlspecialchars($search_query); ?>"
                   aria-label="Search Book Catalog">
            <button type="submit" class="action-button search-button">🔍 Search</button>
            <?php if (!empty($search_query)): ?>
                <a href="user.php" class="action-button" style="background-color: #999;">Clear Search</a>
            <?php endif; ?>
        </form>
    </div>
    <h2 id="borrow">Available Books</h2>

    <?php 
    if (isset($result_available) && $result_available->num_rows > 0): 
    ?>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Year</th>
                    <th>Available Copies</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result_available->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['author']); ?></td>
                        <td><?php echo htmlspecialchars($row['publication_year']); ?></td>
                        <td><?php echo $row['available_copies']; ?></td>
                        <td>
                            <form method="post" action="user.php" style="margin:0;">
                                <input type="hidden" name="book_id" value="<?php echo $row['book_id']; ?>">
                                <button type="submit" name="borrow_action" class="action-button">Borrow</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align: center;">
            <?php echo !empty($search_query) ? "No books found matching **\"" . htmlspecialchars($search_query) . "\"**." : "No books currently available to borrow."; ?>
        </p>
    <?php endif; ?>

    <hr>

    <h2 id="return">Books You Borrowed</h2>

    <?php if (!empty($books_borrowed)): ?>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Year</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books_borrowed as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['author']); ?></td>
                        <td><?php echo htmlspecialchars($row['publication_year']); ?></td>
                        <td>
                            <form method="post" action="user.php" style="margin:0;">
                                <input type="hidden" name="borrow_id" value="<?php echo $row['borrow_id']; ?>">
                                <input type="hidden" name="book_id" value="<?php echo $row['book_id']; ?>">
                                <button type="submit" name="return_action" class="action-button">Return</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align: center;">You have no books currently checked out.</p>
    <?php endif; ?>
</div>

</body>
</html>