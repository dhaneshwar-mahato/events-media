<?php
include 'config/db.php'; 
include 'header.php';

// Fetch categories for dropdown
// Fetch categories from the categories table
$categoryQuery = "SELECT id, category_name FROM categories";
$categoryResult = $conn->query($categoryQuery);

// Get search inputs
$location = $_GET['location'] ?? '';
$category_id = $_GET['category'] ?? '';

// Fetch matching services
$query = "SELECT s.*, u.name AS provider_name, c.category_name 
          FROM services s 
          JOIN users u ON s.user_id = u.id 
          JOIN categories c ON s.category_id = c.id 
          WHERE s.location LIKE '%$location%'";

// Add category filter only if a category is selected
if (!empty($category_id)) {
    $query .= " AND s.category_id = $category_id";
}

$results = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/search_style.css"> <!-- Add your CSS file -->
</head>
<!-- Search Section -->
<section class="search-container">
    <h1>Search Event Services</h1>
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
</section>

<!-- Results -->
<section class="results">
    <h2>Available Services</h2>
    <div class="service-list">
        <?php if ($results->num_rows > 0): ?>
            <?php while ($service = $results->fetch_assoc()): ?>
                <div class="service-card">
                    <img src="uploads/<?= $service['image'] ?>" alt="<?= htmlspecialchars($service['title']) ?>">
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
