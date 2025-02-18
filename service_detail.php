<?php
session_start();
include 'config/db.php';

$service_id = $_GET['id'] ?? null;

if (!$service_id) {
    die("Service not found.");
}

// Fetch service details
$stmt = $conn->prepare("
    SELECT s.*, u.id AS provider_id, u.name AS provider_name, c.category_name 
    FROM services s
    JOIN users u ON s.user_id = u.id
    JOIN categories c ON s.category_id = c.id
    WHERE s.id = ?
");
$stmt->bind_param("i", $service_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Service not found.");
}

$service = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($service['title']) ?> - Service Details</title>
    <link rel="stylesheet" href="assets/css/style.css"> <!-- Add your CSS -->
</head>
<body>

<?php include 'header.php'; ?>

<div class="service-detail-container">
    <div class="service-detail">
        <img src="uploads/<?= htmlspecialchars($service['image']) ?>" alt="<?= htmlspecialchars($service['title']) ?>">
        <h2><?= htmlspecialchars($service['title']) ?></h2>
        <p><strong>Category:</strong> <?= htmlspecialchars($service['category_name']) ?></p>
        <p><strong>Provider:</strong> <?= htmlspecialchars($service['provider_name']) ?></p>
        <p><strong>Location:</strong> <?= htmlspecialchars($service['location']) ?></p>
        <p><strong>Price:</strong> $<?= htmlspecialchars($service['price']) ?></p>
        <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($service['description'])) ?></p>
        <p><strong>Availability:</strong> <?= htmlspecialchars($service['availability']) ?></p>

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="booking.php?service_id=<?= $service['id'] ?>" class="btn">Book Now</a>
            <a href="chat.php?receiver_id=<?= $service['provider_id'] ?>" class="btn chat-btn">Chat with Provider</a>
        <?php else: ?>
            <a href="login.php" class="btn login-required">Book Now</a>
            <a href="login.php" class="btn login-required chat-btn">Chat with Provider</a>
            <p class="login-message">You need to <a href="login.php">log in</a> or <a href="register.php">register</a> to book or chat.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>

</body>
</html>
