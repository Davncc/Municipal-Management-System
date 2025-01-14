<?php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'] ?? null;
    $middle_name = $_POST['middle_name'] ?? null;
    $last_name = $_POST['last_name'] ?? null;
    $barangay_id = $_POST['barangay_id'] ?? null;
    $gender = $_POST['gender'] ?? null;
    $age = $_POST['age'] ?? null;
    $date_of_birth = $_POST['date_of_birth'] ?? null;



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
    if (empty($first_name) || empty($last_name) || empty($barangay_id) || empty($gender) || empty($date_of_birth)) {
        echo "<script>alert('All fields are required!');</script>";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO residents (first_name, middle_name, last_name, barangay_id, gender, age, date_of_birth) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$first_name, $middle_name, $last_name, $barangay_id, $gender, $age, $date_of_birth]);

            // Show success message without redirecting
            echo "<script>alert('Resident added successfully!');</script>";
        } catch (PDOException $e) {
            echo "<script>alert('Database Error: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
}

// Fetch residents
$stmt = $pdo->query("SELECT residents.*, barangays.name AS barangay_name FROM residents 
                     JOIN barangays ON residents.barangay_id = barangays.id");
$residents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Management</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background-color: #f0f0f0; }
        .wrapper { display: flex; }
        .sidebar { width: 250px; background-color: #2C3E50; color: white; padding: 20px; position: fixed; top: 0; bottom: 0; left: 0; box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1); }
        .sidebar h2 { text-align: center; font-size: 28px; margin-bottom: 40px; color: #ECF0F1; }
        .sidebar ul { list-style: none; padding-left: 0; }
        .sidebar ul li { margin-bottom: 25px; }
        .sidebar ul li a { text-decoration: none; color: #BDC3C7; font-size: 18px; display: block; padding: 10px; border-radius: 5px; transition: background-color 0.3s, color 0.3s; }
        .sidebar ul li a:hover { background-color: rgb(66, 203, 194); color: white; transform: scale(1.05); }
        .main-content { margin-left: 250px; padding: 20px; flex-grow: 1; }
        h2, h3 { font-size: 28px; margin-bottom: 20px; color: #2C3E50; text-align:center; }
        .form-container, .list-container { max-width: 800px; margin: 20px auto; padding: 20px; background-color: white; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); border-radius: 8px; }
        label { display: block; margin-bottom: 8px; font-size: 16px; color: #2C3E50; }
        input, select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; }
        button { width: 100%; padding: 10px; background-color: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s; }
        button:hover { background-color: #45a049; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f4f4f4; }
        .barangay-row:hover { background-color: rgb(139, 148, 139); color: white; cursor: pointer; }
        .selected { background-color:rgb(146, 209, 165); color: black; }
        .action-buttons { display: none; margin-top: 20px; text-align: center; }
        .action-buttons button { width: 48%; }
    </style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body>

<div class="wrapper">
    <div class="sidebar">
        <h2>Dashboard</h2>
        <ul>
            <li><a href="user_dashboard.php">Home</a></li>
            <li><a href="user_add_resident.php">Resident Form</a></li>
            <li><a href="user_upload.php">Image Upload</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h2>Resident Management</h2>
        <div class="form-container">
        <form action="user_add_resident.php" method="POST">
    <label for="first_name">First Name:</label>
    <input type="text" id="first_name" name="first_name" required>

    <label for="middle_name">Middle Name:</label>
    <input type="text" id="middle_name" name="middle_name" required>

    <label for="last_name">Last Name:</label>
    <input type="text" id="last_name" name="last_name" required>

    <label for="age">Age:</label>
    <input type="number" id="age" name="age" required min="0">

    <label for="date_of_birth">Date of Birth:</label>
    <input type="date" id="date_of_birth" name="date_of_birth" required placeholder="YYYY-MM-DD">

    <label for="barangay_id">Barangay:</label>
    <select id="barangay_id" name="barangay_id" required>
        <option value="">--Select--</option>
        <?php
        $stmt = $pdo->query("SELECT * FROM barangays");
        $barangays = $stmt->fetchAll();
        foreach ($barangays as $barangay) {
            echo "<option value='" . $barangay['id'] . "'>" . $barangay['name'] . "</option>";
        }
        ?>
    </select>

    <label for="gender">Gender:</label>
    <select id="gender" name="gender" required>
        <option value="">--Select--</option>
        <option value="female">Female</option>
        <option value="male">Male</option>
    </select>

    
    <button type="submit">Add Resident</button>
</form>

        </div>

       
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    // Initialize Flatpickr for date_of_birth
    document.addEventListener("DOMContentLoaded", function () {
        flatpickr("#date_of_birth", {
            dateFormat: "Y-m-d"
        });
    });
    
    let selectedResidentId = null;

    function highlightRow(residentId) {
        const rows = document.querySelectorAll('.barangay-row');
        rows.forEach(row => row.classList.remove('selected'));
        const selectedRow = document.querySelector(`.barangay-row[data-id='${residentId}']`);
        selectedRow.classList.add('selected');
        selectedResidentId = residentId;
        document.getElementById('action-buttons').style.display = 'block';
    }

    function editResident() {
        if (selectedResidentId) {
            window.location.href = `edit_resident.php?id=${selectedResidentId}`;
        } else {
            alert("Please select a resident to edit.");
        }
    }

    function deleteResident() {
        if (selectedResidentId && confirm("Are you sure you want to delete this resident?")) {
            window.location.href = `delete_resident.php?id=${selectedResidentId}`;
        }
    }
</script>

</body>
</html>
