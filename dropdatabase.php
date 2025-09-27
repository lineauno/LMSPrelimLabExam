<?php
$servername = "db";
$username = "root";
$password = "rootpassword";
$dbname = "lms_db";

$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("Database Connection failed: " . $conn->connect_error);
}

$sql_drop_db = "DROP DATABASE IF EXISTS $dbname";

if ($conn->query($sql_drop_db) === TRUE) {
    echo "✅ Database '$dbname' has been dropped successfully.";
} else {
    echo "❌ Error dropping database: " . $conn->error;
}

$conn->close();
?>
