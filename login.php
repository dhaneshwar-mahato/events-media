<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/db.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $errors[] = "Both fields are required.";
    } else {
        // Check if user exists
        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $name, $hashed_password, $role);
            $stmt->fetch();

            // Verify password
            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = $role;

                header("Location: index.php"); // Redirect to home page
                exit();
            } else {
                $errors[] = "Invalid email or password.";
            }
        } else {
            $errors[] = "No account found with this email.";
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
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/login.css"> <!-- Link to CSS file -->
</head>
<body>

<?php include 'header.php'; ?> <!-- Include Header -->

<div class="container">
    <h2>Login</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?= $error ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="register.php">Register here</a></p>
</div>

<?php include 'footer.php'; ?> <!-- Include Footer -->

</body>
</html>
