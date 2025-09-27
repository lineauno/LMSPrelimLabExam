<?php
session_start();
$conn = new mysqli("db", "root", "rootpassword", "lms_db");
if ($conn->connect_error) die("Connection failed");

$search = "";
$result = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $search = $conn->real_escape_string($_POST["search"]);
    $sql = "SELECT * FROM books 
            WHERE title LIKE '%$search%' 
            OR author LIKE '%$search%' 
            OR isbn LIKE '%$search%'";
    $result = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User - Search Books</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { color: #333; }
        input[type=text] { padding: 8px; width: 300px; }
        button { padding: 8px 16px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f4f4f4; }
    </style>
</head>
<body>
    <h2>Search Books</h2>
    <form method="post">
        <input type="text" name="search" placeholder="Enter title, author, or ISBN" value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>
    </form>

    <?php if ($result && $result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Title</th><th>Author</th><th>ISBN</th><th>Year</th><th>Available</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row["title"]) ?></td>
                    <td><?= htmlspecialchars($row["author"]) ?></td>
                    <td><?= htmlspecialchars($row["isbn"]) ?></td>
                    <td><?= htmlspecialchars($row["publication_year"]) ?></td>
                    <td><?= htmlspecialchars($row["available_copies"]) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php elseif ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
        <p>No books found.</p>
    <?php endif; ?>
</body>
</html>
```
