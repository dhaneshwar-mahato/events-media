<?php
session_start();
include 'config/db.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: login.php");
    exit();
}

// Fetch User Details
$stmt = $conn->prepare("SELECT name, email, phone, location FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $phone, $address);
$stmt->fetch();
$stmt->close();

// Update Profile
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_name = trim($_POST['name']);
    $new_phone = trim($_POST['phone']);
    $new_address = trim($_POST['address']);

    if (!empty($new_name)) {
        $updateStmt = $conn->prepare("UPDATE users SET name=?, phone=?, location=? WHERE id=?");
        $updateStmt->bind_param("sssi", $new_name, $new_phone, $new_address, $user_id);
        if ($updateStmt->execute()) {
            $_SESSION['user_name'] = $new_name;
            header("Location: profile.php?success=Profile updated successfully!");
            exit();
        }
        $updateStmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <link rel="stylesheet" href="assets/css/profile.css">
</head>
<body>
    <div class="profile-container">
        <h2>👤 Your Profile</h2>

        <?php if (isset($_GET['success'])): ?>
            <p class="success-message"><?= htmlspecialchars($_GET['success']) ?></p>
        <?php endif; ?>

        <div class="profile-pic">
            <img src="uploads/<?= $profile_image ?: 'default.png' ?>" id="profilePreview">
        </div>

        <form method="POST">
            <label>Name:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>

            <label>Email:</label>
            <input type="email" value="<?= htmlspecialchars($email) ?>" disabled>

            <label>Phone:</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($phone) ?>">

            <label>Address:</label>
            <input type="text" name="address" value="<?= htmlspecialchars($address) ?>">

            <button type="submit">Update Profile</button>
        </form>
    </div>

</body>
</html>
