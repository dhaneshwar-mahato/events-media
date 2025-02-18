<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start session only if not already started
}

$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['user_name'] ?? null;
$user_role = $_SESSION['user_role'] ?? null;

$unread_count = 0;

if ($user_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS unread_count FROM messages WHERE receiver_id = ? AND is_read = 0");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $unread_count = $data['unread_count'];
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Service Booking</title>
    <link rel="stylesheet" href="assets/css/style.css"> <!-- Link CSS -->
    
    <style>
        nav h2 a{
            color: white;
            text-decoration: none;
        }
        /* Basic styles */
nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 20px;
    background: #333;
    color: white;
}

nav h2 a {
    color: white;
    text-decoration: none;
}

.menu-toggle {
    font-size: 24px;
    cursor: pointer;
    display: none; /* Hidden by default */
}

/* Navigation list */
nav ul {
    list-style: none;
    display: flex;
    gap: 20px;
}

nav ul li {
    display: inline;
}

nav ul li a {
    color: white;
    text-decoration: none;
}

/* Hide menu on mobile initially */
@media (max-width: 768px) {
    .menu-toggle {
        display: block; /* Show toggle button on mobile */
    }

    nav ul {
        display: none;
        flex-direction: column;
        background: #333;
        position: absolute;
        top: 50px;
        left: 0;
        width: 100%;
        padding: 10px 0;
        text-align: center;
    }

    nav ul.active {
        display: flex; /* Show menu when active */
    }

    nav ul li {
        padding: 10px 0;
    }
}
/* message btn */
.messages-btn {
    background: #007bff;
    color: white !important;
    padding: 8px 15px;
    border-radius: 5px;
    text-decoration: none;
    transition: 0.3s;
}

.messages-btn:hover {
    background: #0056b3;
}

.messages {
        position: relative;
    }
    .unread-badge {
        background: red;
        color: white;
        font-size: 12px;
        padding: 3px 7px;
        border-radius: 50%;
        position: absolute;
        top: -5px;
        right: -10px;
    }
    </style>

</head>
<body>

<!-- Navigation Bar -->
<nav>
    <h2><a href="index.php">Event Services</a></h2>
    <div class="menu-toggle">☰</div>
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="search.php">Search Services</a></li>

        <?php if ($user_id): ?>
            <?php if ($user_role === 'provider'): ?>
                <li><a href="create_service.php">Create Service</a></li>
                <li><a href="dashboard.php">Dashboard</a></li> 
            <?php endif; ?>
            
            <li class="messages">
                <a href="messages.php">Messages 
                    <?php if ($unread_count > 0): ?>
                        <span class="unread-badge"><?= $unread_count ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <li class="user-profile">
                <a href="profile.php">👤 <?= htmlspecialchars($user_name) ?></a>
                <a href="logout.php" class="logout-btn">Logout</a>
            </li>
        <?php else: ?>
            <li><a href="register.php">Register</a></li>
            <li><a href="login.php">Login</a></li>
        <?php endif; ?>
    </ul>
</nav>

<script src="assets/js/script.js" defer></script> <!-- Link JavaScript -->
</body>
</html>