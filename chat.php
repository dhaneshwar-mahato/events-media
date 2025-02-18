<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to access this page.");
}

$user_id = $_SESSION['user_id'];
$receiver_id = $_GET['receiver_id'] ?? null;

if (!$receiver_id) {
    die("Invalid receiver.");
}

// Fetch receiver's name
$stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmt->bind_param("i", $receiver_id);
$stmt->execute();
$receiver_result = $stmt->get_result();
$receiver = $receiver_result->fetch_assoc();
$receiver_name = $receiver ? htmlspecialchars($receiver['name']) : "Unknown User";
$stmt->close();

// Fetch chat messages
$stmt = $conn->prepare("
    SELECT m.*, u.name AS sender_name 
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE (m.sender_id = ? AND m.receiver_id = ?) 
       OR (m.sender_id = ? AND m.receiver_id = ?) 
    ORDER BY m.created_at ASC
");
$stmt->bind_param("iiii", $user_id, $receiver_id, $receiver_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
$stmt->close();
// Mark messages as read when user views the chat
$updateStmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE receiver_id = ? AND sender_id = ?");
$updateStmt->bind_param("ii", $user_id, $receiver_id);
$updateStmt->execute();
$updateStmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with <?= $receiver_name ?></title>
    <link rel="stylesheet" href="assets/css/chat.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="chat-container">
    <h2>Chat with <?= $receiver_name ?></h2>
    
    <div class="chat-box">
    <?php foreach ($messages as $message): ?>
        <?php $is_sender = ($message['sender_id'] == $user_id); ?>
        <p class="<?= $is_sender ? 'message-sent' : 'message-received' ?>">
            <strong><?= htmlspecialchars($message['sender_name']) ?>:</strong> 
            <?= htmlspecialchars($message['message']) ?>
        </p>
    <?php endforeach; ?>
</div>

    <form action="send_message.php" method="POST">
        <input type="hidden" name="receiver_id" value="<?= $receiver_id ?>">
        <textarea name="message" required placeholder="Type your message..."></textarea>
        <button type="submit">Send</button>
    </form>
</div>

<?php include 'footer.php'; ?>

</body>
</html>
