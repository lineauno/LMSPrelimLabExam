<?php
require_once 'database.php';
session_start();

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['borrow_id'], $_POST['book_id'])) {
    $borrow_id = intval($_POST['borrow_id']);
    $book_id   = intval($_POST['book_id']);

    $conn->query("UPDATE borrowed_books SET return_date = NOW() WHERE borrow_id = $borrow_id AND user_id = $user_id");

    $conn->query("UPDATE books SET available_copies = available_copies + 1 WHERE book_id = $book_id");

    $message = "Book returned successfully!";
}

$sql = "SELECT bb.borrow_id, b.book_id, b.title, b.author, b.publication_year
        FROM borrowed_books bb
        JOIN books b ON bb.book_id = b.book_id
        WHERE bb.user_id = $user_id AND bb.return_date IS NULL";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Return Books</title>
</head>
<body>

<a href="button.php"><button>Button 1</button></a>
<a href="button.php"><button>Button 2</button></a>
<a href="borrow.php"><button>Go to Borrow Books</button></a>
<a href="login.php?logout=1p"><button>Logout</button></a>

<hr>

<h1>Books You Borrowed</h1>

<?php if ($result && $result->num_rows > 0): ?>
    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>Title</th>
            <th>Author</th>
            <th>Year</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td><?php echo htmlspecialchars($row['author']); ?></td>
                <td><?php echo htmlspecialchars($row['publication_year']); ?></td>
                <td>
                    <form method="post" style="margin:0;">
                        <input type="hidden" name="borrow_id" value="<?php echo $row['borrow_id']; ?>">
                        <input type="hidden" name="book_id" value="<?php echo $row['book_id']; ?>">
                        <button type="submit">Return</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>You have not borrowed any books.</p>
<?php endif; ?>

<?php if (isset($message)) echo "<p>$message</p>"; ?>

</body>
</html>
