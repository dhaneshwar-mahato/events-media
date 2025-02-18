<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'provider') {
    header("Location: index.php");
    exit();
}

$errors = [];
$success = "";

// Fetch categories from the database
$categoryQuery = "SELECT * FROM categories";
$categoryResult = $conn->query($categoryQuery);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $title = trim($_POST['title']);
    $category_id = trim($_POST['category']); // Now storing category_id
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $availability = trim($_POST['availability']);
    $location = trim($_POST['location']);
    $created_at = date('Y-m-d H:i:s');

// Image upload handling
$image_name = basename($_FILES['image']['name']);
$image_tmp = $_FILES['image']['tmp_name'];
$image_folder = __DIR__ . "/uploads/" . $image_name; // Absolute path

if (move_uploaded_file($image_tmp, $image_folder)) {
    $image = $image_name;
} else {
    $errors[] = "Failed to upload image. Check folder permissions.";
}

    // Insert into database
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO services (user_id, title, category_id, description, price, availability, image, location, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isissssss", $user_id, $title, $category_id, $description, $price, $availability, $image, $location, $created_at);

        if ($stmt->execute()) {
            $success = "Service added successfully!";
        } else {
            $errors[] = "Failed to add service.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Service</title>
    <link rel="stylesheet" href="assets/css/create_service_style.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="form-container">
    <h2>Create a Service</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success">
            <p><?= htmlspecialchars($success) ?></p>
        </div>
    <?php endif; ?>

    <form action="create_service.php" method="POST" enctype="multipart/form-data">
        <label>Title:</label>
        <input type="text" name="title" required>

        <label>Category:</label>
        <select name="category" required>
            <option value="">Select Category</option>
            <?php while ($row = $categoryResult->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['category_name']) ?></option>
            <?php endwhile; ?>
        </select>

        <label>Description:</label>
        <textarea name="description" rows="4" required></textarea>

        <label>Price (in Rs.):</label>
        <input type="number" name="price" min="1" required>

        <label>Availability:</label>
        <select name="availability" required>
            <option value="Available">Available</option>
            <option value="Booked">Booked</option>
        </select>

        <label>Location:</label>
        <input type="text" name="location" required>

        <label>Upload Image:</label>
        <input type="file" name="image" accept="image/*" required>

        <button type="submit">Submit</button>
    </form>
</div>

<?php include 'footer.php'; ?>

</body>
</html>
