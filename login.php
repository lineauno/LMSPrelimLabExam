<?php
require_once 'database.php';
session_start();
//log out
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'librarian') {
        header("Location: librarian.php");
    } else {
        header("Location: user.php");
    }
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_username = trim($_POST['username'] ?? '');
    $input_password = $_POST['password'] ?? '';

    if (empty($input_username) || empty($input_password)) {
        $login_error = "Please enter both username and password.";
    } else {
        $stmt = $conn->prepare("SELECT user_id, password_hash, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $input_username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $hashed_password = $user['password_hash'];

            if (password_verify($input_password, $hashed_password)) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $input_username;
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'librarian') {
                    header("Location: librarian.php");
                } else { 
                    header("Location: user.php");
                }
                exit();
            } else {
                $login_error = "Invalid username or password.";
            }
        } else {
            $login_error = "Invalid username or password.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System Login</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-container { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); width: 300px; }
        h2 { text-align: center; color: #333; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background-color: #5cb85c; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #4cae4c; }
        .error { color: red; text-align: center; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Simple Library System Login</h2>
        
        <?php if (isset($login_error)): ?>
            <p class="error">⚠️ <?php echo htmlspecialchars($login_error); ?></p>
        <?php endif; ?>
        
        <p style="text-align: center; font-size: 0.9em; color: #666;">
            **Test Credentials:**<br>
            Librarian: <code>librarian_user / admin123</code><br>
            User: <code>standard_user / user123</code>
        </p>

        <form method="post" action="login.php">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit">Log In</button>
        </form>
    </div>
</body>
</html>

<?php
$conn->close();
?>