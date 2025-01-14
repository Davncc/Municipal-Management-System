<?php
require 'db.php';


error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle form submission for updating resident information
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];  // Fetch resident ID from the form
    $first_name = $_POST['first_name'] ?? null;
    $middle_name = $_POST['middle_name'] ?? null;
    $last_name = $_POST['last_name'] ?? null;
    $barangay_id = $_POST['barangay_id'] ?? null;
    $gender = $_POST['gender'] ?? null;
    $age = $_POST['age'] ?? null;
    $date_of_birth = $_POST['date_of_birth'] ?? null;

    // Validate inputs
    if (empty($first_name) || empty($last_name) || empty($barangay_id) || empty($gender) || empty($date_of_birth)) {
        echo "<script>alert('All fields are required!');</script>";
    } else {
        try {
            // Update the resident information in the database
            $stmt = $pdo->prepare("UPDATE residents SET first_name = ?, middle_name = ?, last_name = ?, barangay_id = ?, gender = ?, age = ?, date_of_birth = ? WHERE id = ?");
            $stmt->execute([$first_name, $middle_name, $last_name, $barangay_id, $gender, $age, $date_of_birth, $id]);

            echo "<script>alert('Resident updated successfully!'); window.location.href = 'add_resident.php';</script>";
        } catch (PDOException $e) {
            echo "Database Error: " . $e->getMessage();
        }
    }
}
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM residents WHERE id = ?");
    $stmt->execute([$id]);
    $resident = $stmt->fetch();

    if (!$resident) {
        echo "<script>alert('Resident not found!'); window.location.href = 'add_resident.php';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Resident</title>
    <style>
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
            font-size: 24px;
            margin-bottom: 40px;
            color: #ECF0F1;
        }

        .sidebar ul {
            list-style: none;
            padding-left: 0;
        }

        .sidebar ul li {
            margin-bottom: 20px;
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
            background-color: #34495E;
            color: white;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            flex-grow: 1;
        }

        h2 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #2C3E50;
        }

        .form-container, .list-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 16px;
            color: #2C3E50;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #45a049;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        .action-btn {
            display: inline-block;
            padding: 10px;
            border-radius: 5px;
            color: white;
            text-decoration: none;
            margin-right: 5px;
            width: 80px;
            height: 40px;
            text-align: center;
        }

        .edit-btn {
            background-color: #007bff;
        }

        .edit-btn:hover {
            background-color: #0056b3;
        }

        .delete-btn {
            background-color: #e74c3c;
        }

        .delete-btn:hover {
            background-color: #c0392b;
        }

        .hidden {
            display: none;
        }
    </style>
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body>


<div class="wrapper">
    <div class="sidebar">
        <h2>Dashboard</h2>
        <ul>
            <li><a href="dashboard.php">Home</a></li>
            <li><a href="add_barangay.php">Barangay Management</a></li>
            <li><a href="add_resident.php">Resident Management</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h2>Edit Resident</h2>
        <div class="form-container">
            <form action="edit_resident.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $resident['id']; ?>">

                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo $resident['first_name']; ?>" required>

                <label for="middle_name">Middle Name:</label>
                <input type="text" id="middle_name" name="middle_name" value="<?php echo $resident['middle_name']; ?>">

                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" value="<?php echo $resident['last_name']; ?>" required>

                <label for="age">Age:</label>
                <input type="number" id="age" name="age" value="<?php echo $resident['age']; ?>" required min="0">

                <label for="date_of_birth">Date of Birth:</label>
                <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo $resident['date_of_birth']; ?>"  required placeholder="YYYY-MM-DD">


                <label for="barangay_id">Barangay:</label>
                <select id="barangay_id" name="barangay_id" required>
                    <?php
                    // Populate barangay options
                    $stmt = $pdo->query("SELECT * FROM barangays");
                    $barangays = $stmt->fetchAll();
                    foreach ($barangays as $barangay) {
                        $selected = ($barangay['id'] == $resident['barangay_id']) ? 'selected' : '';
                        echo "<option value='".$barangay['id']."' $selected>".$barangay['name']."</option>";
                    }
                    ?>
                </select>

                <label for="gender">Gender:</label>
                <select id="gender" name="gender" required>
                    <option value="female" <?php if($resident['gender'] == 'female') echo 'selected'; ?>>Female</option>
                    <option value="male" <?php if($resident['gender'] == 'male') echo 'selected'; ?>>Male</option>
                </select>

                <button type="submit">Update Resident</button>
            </form>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    // Initialize Flatpickr for date_of_birth
    document.addEventListener("DOMContentLoaded", function () {
        flatpickr("#date_of_birth", {
            dateFormat: "Y-m-d"
        });
    });
    </script>

</body>
</html>