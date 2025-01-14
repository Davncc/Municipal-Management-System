<?php
session_start();
require 'db.php';


error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle the update form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_barangay_id'])) {
    $update_id = $_POST['update_barangay_id'];
    $update_name = $_POST['name'];

    $update_stmt = $pdo->prepare("UPDATE barangays SET name = ? WHERE id = ?");
    if ($update_stmt->execute([$update_name, $update_id])) {
        header("Location: dashboard.php?status=barangay_updated");
        exit;
    } else {
        header("Location: dashboard.php?status=error");
        exit;
    }
}

// Load barangay data for editing
$barangay_to_edit = null;
if (isset($_GET['id'])) {
    $edit_id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM barangays WHERE id = ?");
    $stmt->execute([$edit_id]);
    $barangay_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Barangay</title>
    <style>
        /* General reset and body styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
        }

        /* Wrapper to layout the sidebar and main content */
        .wrapper {
            display: flex;
        }

        /* Sidebar styles */
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

        /* Main content area */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            flex-grow: 1;
        }

        h1 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #2C3E50;
        }

        /* Form container styles */
        .form-container {
            max-width: 500px;
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

        input {
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 200px;
            }
        }

        @media (max-width: 480px) {
            .wrapper {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Dashboard</h2>
        <ul>
            <li><a href="dashboard.php">Home</a></li>
            <li><a href="add_barangay.php">Add Barangay</a></li>
            <li><a href="add_resident.php">Add Resident</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1>Edit Barangay</h1>
        
        <div class="form-container">
            <?php if ($barangay_to_edit): ?>
                <form method="post">
                    <input type="hidden" name="update_barangay_id" value="<?php echo $barangay_to_edit['id']; ?>">
                    <label for="name">Barangay Name:</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($barangay_to_edit['name']); ?>" required>
                    <button type="submit">Update Barangay</button>
                </form>
            <?php else: ?>
                <p>No barangay found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
