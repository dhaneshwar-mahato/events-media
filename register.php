<?php
include 'config/db.php';
include 'header.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = $_POST['user_type']; // 'client' or 'provider'

    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($user_type)) {
        $errors[] = "All fields are required.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    // Check if email already exists
    $emailCheck = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $emailCheck->bind_param("s", $email);
    $emailCheck->execute();
    $emailCheck->store_result();

    if ($emailCheck->num_rows > 0) {
        $errors[] = "Email is already registered.";
    }
    $emailCheck->close();

    // If no errors, register user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $hashed_password, $user_type);
        
        if ($stmt->execute()) {
            $stmt->close();
            header("Location: login.php?success=registered"); // Redirect to login page
            exit();
        } else {
            $errors[] = "Registration failed. Try again.";
        }
    
        $stmt->close(); // Close statement after execution
    }    
}
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/register.css"> <!-- Add your CSS file -->
</head>
<!-- Registration Form -->
<section class="register">
    <h2>Register</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="register.php" method="POST">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>

        <label>Select Account Type:</label>
        <select name="user_type" required>
            <option value="client">Client</option>
            <option value="provider">Service Provider</option>
        </select>

        <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Login here</a></p>
</section>

<?php include 'footer.php'; ?>
