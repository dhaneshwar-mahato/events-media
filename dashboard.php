<?php
session_start();
include 'config/db.php';

// Check if user is logged in and is a provider
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'provider') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch provider's services with order count and availability status
$query = "
    SELECT s.id, s.title, s.price, s.availability, s.created_at, s.status, 
           c.category_name, 
           COUNT(o.service_id) AS total_orders, 
           COUNT(DISTINCT o.user_id) AS unique_clients
    FROM services s
    LEFT JOIN orders o ON s.id = o.service_id
    JOIN categories c ON s.category_id = c.id
    WHERE s.user_id = ?
    GROUP BY s.id, s.title, s.price, s.availability, s.created_at, s.status, c.category_name
    ORDER BY s.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$services = $result->fetch_all(MYSQLI_ASSOC); // Fetch all data once
// echo "<pre>";
// print_r($result->fetch_all(MYSQLI_ASSOC));
// echo "</pre>";
$stmt->close();

// Handle service availability toggle
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['toggle_service'])) {
    $service_id = $_POST['service_id'];
    $new_status = ($_POST['current_status'] == 'active') ? 'inactive' : 'active';

    $updateQuery = "UPDATE services SET status = ? WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("sii", $new_status, $service_id, $user_id);
    $stmt->execute();
    $stmt->close();

    // Refresh the page to reflect changes
    header("Location: dashboard.php");
    exit();

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provider Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css"> 
</head>
<body>

<?php include 'header.php'; ?>

<div class="dashboard-container">
    <h2>Your Services</h2>

    <a href="create_service.php" class="btn">+ Add New Service</a>

    <table class="dashboard-table">
    <thead>
        <tr>
            <th>#</th> <!-- Serial Number Column -->
            <th>Title</th>
            <th>Category</th>
            <th>Price</th>
            <th>Availability</th>
            <th>Orders</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($services)): // Check if there is data ?>
            <?php $serial = 1; // Initialize serial number ?>
            <?php foreach ($services as $service): ?>
                <tr>
                    <td><?= $serial++; ?></td> <!-- Display Serial Number -->
                    <td><?= htmlspecialchars($service['title']) ?></td>
                    <td><?= htmlspecialchars($service['category_name']) ?></td>
                    <td>₹<?= htmlspecialchars(number_format($service['price'], 2)) ?></td>
                    <td><?= htmlspecialchars($service['availability']) ?></td>
                    <td><?= htmlspecialchars($service['total_orders']) ?></td>
                    <td>
                        <form method="POST" class="status-form">
                            <input type="hidden" name="service_id" value="<?= $service['id'] ?>">
                            <input type="hidden" name="current_status" value="<?= $service['status'] ?>">
                            <button type="submit" name="toggle_service" class="status-btn <?= ($service['status'] == 'active') ? 'active' : 'inactive' ?>">
                                <?= ($service['status'] == 'active') ? 'Active' : 'Inactive' ?>
                            </button>
                        </form>
                    </td>
                    <td>
                        <a href="edit_service.php?id=<?= $service['id'] ?>" class="btn small-btn">Edit</a>
                        <a href="delete_service.php?id=<?= $service['id'] ?>" class="btn small-btn delete-btn">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="8">No services found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

</div>

<?php include 'footer.php'; ?>

<style>
    .status-btn {
        padding: 5px 10px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
    }
    .status-btn.active {
        background-color: #4CAF50;
        color: white;
    }
    .status-btn.inactive {
        background-color: #FF6347;
        color: white;
    }
    .small-btn {
        padding: 5px 10px;
        background-color: #007BFF;
        color: white;
        text-decoration: none;
        border-radius: 5px;
    }
    .delete-btn {
        background-color: red;
    }
</style>

</body>
</html>
