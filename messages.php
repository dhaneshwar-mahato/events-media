<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all unique users the logged-in user has chatted with & count unread messages
$query = "
    SELECT 
        u.id AS user_id, 
        u.name AS user_name, 
        (SELECT COUNT(*) FROM messages 
         WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0) AS unread_count
    FROM users u
    JOIN messages m ON (m.sender_id = u.id OR m.receiver_id = u.id)
    WHERE (m.sender_id = ? OR m.receiver_id = ?)
    AND u.id != ?
    GROUP BY u.id
    ORDER BY (SELECT MAX(created_at) FROM messages WHERE sender_id = u.id OR receiver_id = u.id) DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <link rel="stylesheet" href="assets/css/message.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="messages-container">
    <h2>Your Messages</h2>
    <?php if (!empty($users)): ?>
        <ul class="message-list">
            <?php foreach ($users as $row): ?>
                <li>
                    <a href="chat.php?sender_id=<?= $user_id ?>&receiver_id=<?= $row['user_id'] ?>">
                        <span><?= htmlspecialchars($row['user_name']) ?></span>
                        <?php if ($row['unread_count'] > 0): ?>
                            <span class="unread-badge"><?= $row['unread_count'] ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No messages yet.</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>

</body>
</html>
