<?php
session_start();

// Example: Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Landing Page</title>
</head>
<body>
    <a href="button.php"><button>Button 1</button></a>
    <a href="button.php"><button>Button 2</button></a>
    <a href="borrow.php"><button>Go to Borrow Books</button></a>
    <a href="login.php?logout=1p"><button>Logout</button></a>
</body>
</html>