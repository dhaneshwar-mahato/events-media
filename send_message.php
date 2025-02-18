<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in.");
}

$sender_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'] ?? null;
$message = trim($_POST['message']);

if (!$receiver_id || empty($message)) {
    die("Invalid input.");
}

// Insert message into database
$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $sender_id, $receiver_id, $message);
$stmt->execute();
$stmt->close();

// Redirect back to chat
header("Location: chat.php?receiver_id=" . $receiver_id);
exit;
