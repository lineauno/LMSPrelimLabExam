<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'librarian') {
  header("Location: login.php");
  exit;
}

$servername = "db"; 
$username   = "root";
$password   = "rootpassword"; 
$dbname     = "lms_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection Error: " . $conn->connect_error);
}

$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (!empty($_POST["title"]) && !empty($_POST["author"]) && !empty($_POST["publication_year"]) && !empty($_POST["isbn"])) {
    $title  = $conn->real_escape_string($_POST["title"]);
    $author = $conn->real_escape_string($_POST["author"]);
    $year   = intval($_POST["publication_year"]);
    $isbn   = $conn->real_escape_string($_POST["isbn"]);

    $insertQuery = "INSERT INTO books (title, author, publication_year, isbn) 
                    VALUES ('$title', '$author', '$year', '$isbn')";
    if ($conn->query($insertQuery) === TRUE) {
      $message = "<p style='color:green;'>Book added successfully!</p>";
    } else {
      $message = "<p style='color:red;'>Insertion failed: " . $conn->error . "</p>";
    }
  }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Book</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #fff;
      margin: 0;
      padding: 0;
      color: #333;
    }
    h2 {
      text-align: center;
      margin-top: 20px;
      color: #444;
    }
    .container {
      width: 80%;
      margin: 20px auto;
      background: #E0E0D8;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(172, 150, 150, 0.1);
      text-align: center;
    }
    input[type="text"],
    input[type="number"],
    input[type="submit"] {
      padding: 8px 12px;
      margin: 8px;
      border: 1px solid #cac8c8ff;
      border-radius: 5px;
      width: 60%;
    }
    input[type="submit"] {
      background: #007bff;
      color: white;
      border: none;
      cursor: pointer;
      transition: 0.3s;
    }
    input[type="submit"]:hover {
      background: #0056b3;
    }
    .message {
      margin-top: 15px;
      font-size: 16px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>ADD BOOK</h2>
    <?php if (!empty($message)) echo "<div class='message'>$message</div>"; ?>
    <form action="librarian.php" method="post">
      <input type="text" name="title" placeholder="Book Title" required><br>
      <input type="text" name="author" placeholder="Author" required><br>
      <input type="number" name="publication_year" placeholder="Year" required><br>
      <input type="text" name="isbn" placeholder="ISBN" required><br>
      <input type="submit" value="Save Book">
    </form>
  </div>
</body>
</html>
