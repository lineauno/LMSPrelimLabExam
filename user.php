<?php
require_once 'database.php'; 
session_start();

if (!isset($_SESSION['role'])) {
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
    <title>User Catalog</title>
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
        <h2>📖 LIBRARY CATALOG</h2>
        <p>Welcome, **<?php echo htmlspecialchars($_SESSION['username']); ?>** (Standard User)</p>
        
        <hr>

        <?php 
        if ($success_message) {
            echo "<div class='message success'>$success_message</div>";
        }
        if ($error_message) {
            echo "<div class='message error'>$error_message</div>";
        }
        ?>

        <p>*(Montenegro's **Search** form goes here.)*</p>

        <h3>Available Books</h3>
        
        <p>*(The entire Book Catalog table will be inserted here by Montablan.)*</p>
        
        <p>*(Navalta's borrow/return goes here.)*</p>

    </div>
</body>
</html>