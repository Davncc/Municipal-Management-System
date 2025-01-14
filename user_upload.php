<?php
require 'db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch all images from the database for viewing
$stmt = $pdo->prepare("SELECT id, file_path, uploaded_at FROM images ORDER BY uploaded_at DESC");
$stmt->execute();
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submissions for image upload
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_FILES['image'])) {
        $target_dir = "uploads/";

        // Ensure the uploads directory exists
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = basename($_FILES['image']['name']);
        $unique_file_name = time() . "_" . $file_name;
        $target_file = $target_dir . $unique_file_name;
        $uploadOk = 1;

        // Check if file is an image
        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            echo "File is not an image.";
            $uploadOk = 0;
        }

        // Check file size (limit to 5MB)
        if ($_FILES['image']['size'] > 5000000) {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow only certain file formats
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        // Upload file and save to database
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $stmt = $pdo->prepare("INSERT INTO images (file_path, uploaded_at) VALUES (?, NOW())");
                $stmt->execute([$target_file]);
                echo "<script>alert('Image uploaded successfully!'); window.location.href = 'user_upload.php';</script>";
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    }
}

// Handle delete request
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("SELECT file_path FROM images WHERE id = ?");
    $stmt->execute([$id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        if (file_exists($file['file_path'])) {
            unlink($file['file_path']);
        }
        $stmt = $pdo->prepare("DELETE FROM images WHERE id = ?");
        $stmt->execute([$id]);
        echo "<script>alert('Image deleted successfully!'); window.location.href = 'upload.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        /* General styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
        }

        .wrapper {
            display: flex;
        }

        .sidebar {
            width: 250px;
            background-color: #2C3E50;
            color: white;
            padding: 20px;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar h2 {
            text-align: center;
            font-size: 28px;
            margin-bottom: 40px;
            color: #ECF0F1;
        }

        .sidebar ul {
            list-style: none;
            padding-left: 0;
        }

        .sidebar ul li {
            margin-bottom: 25px;
        }

        .sidebar ul li a {
            text-decoration: none;
            color: #BDC3C7;
            font-size: 18px;
            display: block;
            padding: 10px;
            border-radius: 5px;
            transition: background-color 0.3s, color 0.3s;
        }

        .sidebar ul li a:hover {
            background-color: rgb(59, 208, 198);
            color: white;
        }

        .content {
            margin-left: 250px;
            padding: 20px;
            overflow: hidden;
        }

        h2 {
            text-align: center;
            color: #2C3E50;
        }
        h3{
            text-align:center;
        }

        .form-container {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    text-align: center; /* Centers all elements inside the form */
}

        .form-container input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .form-container input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        
        }

        table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: center;
        }

        th {
            background-color:rgb(139, 148, 139);
            color: white;
        }

        td img {
            width: 150px;
            height: auto;
            border-radius: 5px;
        }

        td button {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;

        }

        td button:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
<div class="sidebar">
    <h2>Dashboard</h2>
    <ul>
        <li><a href="user_dashboard.php">Home</a></li>
        <li><a href="user_add_resident.php">Resident Form</a></li>
        <li><a href="user_upload.php">Image Upload</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="content">
    <h2>Upload and View Images</h2>

    <!-- Image Upload Form -->
    <div class="form-container">
        <form action="user_upload.php" method="POST" enctype="multipart/form-data">
            <label for="image">Choose an image to upload:</label>
            <input type="file" name="image" id="image" accept="image/*" required>
            <input type="submit" name="submit" value="Upload Image">
        </form>
    </div>
    </div>
</body>
</html>
