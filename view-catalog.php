<?php
require_once 'database.php';
session_start();

$sql = "SELECT book_id, title, author, isbn, publication_year, quantity, available_copies 
        FROM books ORDER BY title ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Books - Library Management System</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f9f9f9; margin: 20px; }
        h2 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #5cb85c; color: white; }
        tr:hover { background-color: #f1f1f1; }
        .back-link { display: block; text-align: center; margin-top: 20px; }
        .status { font-weight: bold; }
        .available { color: green; }
        .unavailable { color: red; }
    </style>
</head>
<body>

    <h2>📚 Browse Book Catalog</h2>
    
    <?php if ($result && $result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>ISBN</th>
                    <th>Year</th>
                    <th>Total Copies</th>
                    <th>Available</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['author']); ?></td>
                        <td><?php echo htmlspecialchars($row['isbn']); ?></td>
                        <td><?php echo htmlspecialchars($row['publication_year']); ?></td>
                        <td><?php echo (int)$row['quantity']; ?></td>
                        <td><?php echo (int)$row['available_copies']; ?></td>
                        <td class="status <?php echo ($row['available_copies'] > 0) ? 'available' : 'unavailable'; ?>">
                            <?php echo ($row['available_copies'] > 0) ? 'Available ✅' : 'Unavailable ❌'; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    <?php else: ?>
        <p style="text-align: center;">No books found in the catalog.</p>
    <?php endif; ?>

    <div class="back-link">
        <?php if (isset($_SESSION['role'])): ?>
            <a href="<?php echo ($_SESSION['role'] === 'librarian') ? 'librarian.php' : 'user.php'; ?>">⬅ Back to Dashboard</a>
        <?php else: ?>
            <a href="login.php">⬅ Back to Login</a>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>
