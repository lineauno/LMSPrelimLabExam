<?php
$conn = new mysqli("db", "root", "rootpassword", "lms_db");
if ($conn->connect_error) die("Connection failed");

$results = null;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $search = $conn->real_escape_string($_POST["search"]);
    $sql = "SELECT * FROM books 
            WHERE title LIKE '%$search%' 
               OR author LIKE '%$search%' 
               OR isbn LIKE '%$search%'";
    $results = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Search Books</title>
</head>
<body>
    <h2>Search a Book</h2>
    <form method="post">
        <input type="text" name="search" placeholder="Enter title, author, or ISBN" required>
        <button type="submit">Search</button>
    </form>
    <?php if ($results && $results->num_rows > 0): ?>
        <h3>Results:</h3>
        <table border="1" cellpadding="5">
            <tr>
                <th>Title</th><th>Author</th><th>ISBN</th>
                <th>Year</th><th>Quantity</th><th>Available</th>
            </tr>
            <?php while ($row = $results->fetch_assoc()): ?>
                <tr>
                    <td><?= $row["title"] ?></td>
                    <td><?= $row["author"] ?></td>
                    <td><?= $row["isbn"] ?></td>
                    <td><?= $row["publication_year"] ?></td>
                    <td><?= $row["quantity"] ?></td>
                    <td><?= $row["available_copies"] ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php elseif ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
        <p>No books found.</p>
    <?php endif; ?>
</body>
</html>
