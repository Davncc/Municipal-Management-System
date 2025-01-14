<?php
session_start();
require 'db.php';

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php'); // Redirect to login page if not logged in as user
    exit();
}

$genderQuery = $pdo->query("SELECT gender, COUNT(*) AS count FROM residents GROUP BY gender");
$genderData = $genderQuery->fetchAll();
// Fetch barangay list and residents count
$barangayQuery = $pdo->query("SELECT barangays.name, COUNT(residents.id) AS total_residents 
                              FROM barangays 
                              LEFT JOIN residents ON barangays.id = residents.barangay_id 
                              GROUP BY barangays.id");
$barangayData = $barangayQuery->fetchAll();

// Fetch all residents data
$residentQuery = $pdo->query("SELECT residents.*, barangays.name AS barangay_name FROM residents 
                              JOIN barangays ON residents.barangay_id = barangays.id");
$residentData = $residentQuery->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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

        .main-content {
            margin-left: 250px;
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 20px;
            position: relative;
            overflow: hidden;
        }

        h1 {
            font-size: 30px;
            text-align: center;
            margin-bottom: 20px;
            color: #2C3E50;
        }

        h2, h3 {
            text-align: center;
        }

        .card-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 100%;
            transition: box-shadow 0.3s, transform 0.3s;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px); /* Subtle lift without scaling out of bounds */
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
        }

        button {
            padding: 10px 20px;
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

        /* Gender Distribution Styles */
        .gender-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 100px;
            text-align: center;
        }

        .gender-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .gender-icon {
            font-size: 120px;
            margin-bottom: 10px;
        }

        .gender-text {
            font-size: 20px;
            font-weight: bold;
            margin: 0;
            color: #2C3E50;
        }

        .gender-count {
            font-size: 36px;
            font-weight: bold;
            color: #3498db;
        }

        .gender-content .fa-mars {
            color: #3498db;
        }

        .gender-content .fa-venus {
            color: #e74c3c;
        }
        
    </style>
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
    <h1>Municipal Management System</h1>
        <h3>Welcome to your user dashboard, <?php echo $_SESSION['username']; ?>!</h3>
       
        <div class="card-container">
            <!-- Gender Distribution -->
            <div class="card">
                <h2>Gender Distribution</h2>
                <div class="gender-container">
                    <div class="gender-content">
                        <i class="fa fa-mars gender-icon" aria-hidden="true"></i>
                        <p class="gender-text">Total Male Residents</p>
                        <p class="gender-count"><?php echo $genderData[0]['count']; ?></p>
                    </div>
                    <div class="gender-content">
                        <i class="fa fa-venus gender-icon" aria-hidden="true"></i>
                        <p class="gender-text">Total Female Residents</p>
                        <p class="gender-count"><?php echo $genderData[1]['count']; ?></p>
                    </div>
                </div>
            </div>

            <!-- Barangay List -->
            <div class="card">
                <h2>Barangay List</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Barangay Name</th>
                            <th>Number of Residents</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $grand_total_residents = 0;
                        foreach ($barangayData as $barangay): 
                            $grand_total_residents += $barangay['total_residents'];
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($barangay['name']); ?></td>
                                <td><?php echo htmlspecialchars($barangay['total_residents']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td style="font-weight: bold;">Total Residents:</td>
                            <td style="font-weight: bold;"><?php echo $grand_total_residents; ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

<!-- FontAwesome for Gender Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

</body>
</html>
