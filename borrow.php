<?php
require_once 'database.php';
session_start();

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_id'])) {
    $book_id = intval($_POST['book_id']);

    $sql_update = "UPDATE books 
                SET available_copies = available_copies - 1 
                WHERE book_id = $book_id AND available_copies > 0";

    if ($conn->query($sql_update) === TRUE) {
        $sql_insert = "INSERT INTO borrowed_books (user_id, book_id) 
                    VALUES ($user_id, $book_id)";
        if ($conn->query($sql_insert) === TRUE) {
            $message = "Book borrowed successfully!";
        } else {
            $message = "Error recording borrow: " . $conn->error;
        }
    } else {
        $message = "Error borrowing book: " . $conn->error;
    }
}

$sql = "SELECT book_id, title, author, publication_year, available_copies 
        FROM books 
        WHERE is_Available = TRUE AND available_copies > 0";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Borrow Books</title>
</head>
<body>

<a href="button.php"><button>Button 1</button></a>
<a href="button.php"><button>Button 2</button></a>
<a href="breturn.php"><button>Return Books</button></a>
<a href="login.php?logout=1"><button>Logout</button></a>

<hr>

<h1>Available Books</h1>

<?php if ($result && $result->num_rows > 0): ?>
    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>Title</th>
            <th>Author</th>
            <th>Year</th>
            <th>Available Copies</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td><?php echo htmlspecialchars($row['author']); ?></td>
                <td><?php echo htmlspecialchars($row['publication_year']); ?></td>
                <td><?php echo $row['available_copies']; ?></td>
                <td>
                    <form method="post" style="margin:0;">
                        <input type="hidden" name="book_id" value="<?php echo $row['book_id']; ?>">
                        <button type="submit">Borrow</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>No books available to borrow.</p>
<?php endif; ?>

<?php if (isset($message)) echo "<p>$message</p>"; ?>

</body>
</html>
