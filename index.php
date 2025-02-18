<?php
include 'config/db.php'; 
include 'header.php';

// Fetch categories for dropdown
$categoryQuery = "SELECT id, category_name FROM categories";
$categoryResult = $conn->query($categoryQuery);

// Get search inputs
$location = $_GET['location'] ?? '';
$category_id = $_GET['category'] ?? '';

// Fetch services based on search criteria
$query = $query = "SELECT s.*, u.name AS provider_name, c.category_name 
FROM services s 
JOIN users u ON s.user_id = u.id 
JOIN categories c ON s.category_id = c.id 
WHERE s.status = 'active'"; // Only fetch active services

// Add filters dynamically if values are provided
if (!empty($location)) {
    $query .= " AND s.location LIKE '%$location%'";
}
if (!empty($category_id)) {
    $query .= " AND s.category_id = $category_id";
}

// Limit the results if no search query is made
if (empty($location) && empty($category_id)) {
    $query .= " ORDER BY s.created_at DESC LIMIT 10";
}

$serviceResult = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Service Booking</title>
    <script src="assets/js/script.js" defer></script>
    <link rel="stylesheet" href="assets/css/style.css"> <!-- Add your CSS file -->
</head>
<body>

    <!-- Hero Section -->
    <header>
        <h1 style="color:#333;">Find and Book Event Services</h1>
        <p style="color:#333;">Search for photographers, videographers, caterers, and more.</p>
        
        <!-- Search Form -->
        <form method="GET" class="search-form">
            <input type="text" name="location" placeholder="Enter Location" value="<?= htmlspecialchars($location) ?>">
            <select name="category">
                <option value="">Select Category</option>
                <?php while ($row = $categoryResult->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>" <?= $category_id == $row['id'] ? "selected" : "" ?>>
                        <?= ucfirst($row['category_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit">Search</button>
        </form>
    </header>

    <!-- Search Results or Featured Services -->
    <section class="results">
        <h2><?= (!empty($location) || !empty($category_id)) ? "Search Results" : "Featured Services" ?></h2>
        <div class="service-list">
            <?php if ($serviceResult->num_rows > 0): ?>
                <?php while ($service = $serviceResult->fetch_assoc()): ?>
                    <div class="service-card">
                        <img src="uploads/<?= htmlspecialchars($service['image']) ?>" alt="<?= htmlspecialchars($service['title']) ?>">
                        <h3><?= htmlspecialchars($service['title']) ?></h3>
                        <p><strong>Provider:</strong> <?= htmlspecialchars($service['provider_name']) ?></p>
                        <p><strong>Location:</strong> <?= htmlspecialchars($service['location']) ?></p>
                        <p><strong>Price:</strong> ₹<?= htmlspecialchars($service['price']) ?></p>
                        <a href="service_detail.php?id=<?= $service['id'] ?>" class="btn">View Details</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No services found. Try a different search.</p>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'footer.php'; ?>
</body>
</html>
