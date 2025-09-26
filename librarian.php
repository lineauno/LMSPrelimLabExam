<?php
require_once 'database.php'; 
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'librarian') {
    header("Location: login.php");
    exit();
}

$success_message = '';
$error_message = '';

if (isset($_GET['success'])) {
    $success_message = htmlspecialchars($_GET['success']);
}

if (isset($conn)) {
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librarian Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 1200px; margin: 40px auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 15px rgba(0, 0, 0, 0.1); }
        h2 { text-align: center; color: #333; border-bottom: 2px solid #5cb85c; padding-bottom: 10px; }
        h3 { color: #5cb85c; margin-top: 25px; }
        .logout { float: right; padding: 5px 10px; background-color: #333; color: white; border-radius: 4px; text-decoration: none; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #5cb85c; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <a href="logout.php" class="logout">Logout</a>
        <h2>📚 LIBRARIAN DASHBOARD</h2>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> (Librarian)</p>
        
        <hr>
        
        <?php 
        if ($success_message) {
            echo "<div class='message success'>$success_message</div>";
        }
        if ($error_message) {
            echo "<div class='message error'>$error_message</div>";
        }
        ?>

        <p> Dito niyo insert yung code niyo sa similar file niyong ganto then delete this line na lang.</p>

        <h3>Current Book Catalog</h3>
        <p> Dito yung sa view catalog then delete this line na lang.</>
            

    </div>
</body>
</html>