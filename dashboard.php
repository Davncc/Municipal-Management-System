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

// Fetch total number of residents
$stmt = $pdo->query("SELECT COUNT(*) AS total_residents FROM residents");
$residents_data = $stmt->fetch();

// Fetch all barangays along with the total number of residents in each
$stmt = $pdo->prepare("
    SELECT b.id, b.name, COUNT(r.id) AS total_residents 
    FROM barangays b 
    LEFT JOIN residents r ON b.id = r.barangay_id 
    GROUP BY b.id
");
$stmt->execute();
$barangays = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query to count male and female residents
$stmt = $pdo->prepare("SELECT gender, COUNT(*) AS count FROM residents GROUP BY gender");
$stmt->execute();
$resident_counts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set default values if no data found
$male_count = 0;
$female_count = 0;

foreach ($resident_counts as $row) {
    if ($row['gender'] == 'male') {
        $male_count = $row['count'];
    } elseif ($row['gender'] == 'female') {
        $female_count = $row['count'];
    }
}

// Fetch all residents for profiling
$stmt = $pdo->query("SELECT r.id, r.first_name, r.middle_name, r.last_name,  r.gender, b.name AS barangay_name FROM residents r JOIN barangays b ON r.barangay_id = b.id");
$residents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle deletion
if (isset($_POST['delete_resident_id'])) {
    $delete_id = $_POST['delete_resident_id'];
    $delete_stmt = $pdo->prepare("DELETE FROM residents WHERE id = ?");
    if ($delete_stmt->execute([$delete_id])) {
        header("Location: dashboard.php?status=deleted");
        exit;
    } else {
        header("Location: dashboard.php?status=error");
        exit;
    }
}

// Handle update (load resident data for editing)
$resident_to_edit = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM residents WHERE id = ?");
    $stmt->execute([$edit_id]);
    $resident_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle the update form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_resident_id'])) {
    $update_id = $_POST['update_resident_id'];
    $update_first_name = $_POST['first_name'];
    $update_last_name = $_POST['last_name'];
    $update_affiliation = $_POST['affiliation'];
    $update_employment = $_POST['employment'];
    $update_gender = $_POST['gender'];

    $update_stmt = $pdo->prepare("UPDATE residents SET first_name = ?, last_name = ?, affiliation = ?, employment = ?, gender = ? WHERE id = ?");
    if ($update_stmt->execute([$update_first_name, $update_last_name, $update_affiliation, $update_employment, $update_gender, $update_id])) {
        header("Location: dashboard.php?status=updated");
        exit;
    } else {
        header("Location: dashboard.php?status=error");
        exit;
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
         } h2{
text-align:center;
            
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
        .welcome-message {
            text-align: center;
            font-size: 24px;
            color: #3498db;
            margin-bottom: 30px;
        }
    </style>

</head>
<body>
<div class="wrapper">
    <div class="sidebar">
        <h2>Dashboard</h2>
        <ul>
            <li><a href="dashboard.php">Home</a></li>
            <li><a href="add_barangay.php">Barangay Management</a></li>
            <li><a href="add_resident.php">Resident Management</a></li>
            <li><a href="upload.php">Image Upload</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h1>Municipal Management System</h1>
        <div class="welcome-message">
        <h3>Welcome Admin, <?php echo $_SESSION['username']; ?>!</h3>
        </div>
    
        
            <!-- Gender Distribution -->
            <div class="card">
                <h2>Gender Distribution</h2>
                <div class="gender-container">
                    <div class="gender-content">
                        <i class="fa fa-mars gender-icon" aria-hidden="true"></i>
                        <p class="gender-text">Total Male Residents</p>
                        <p class="gender-count"><?php echo $male_count; ?></p>
                    </div>
                    <div class="gender-content">
                        <i class="fa fa-venus gender-icon" aria-hidden="true"></i>
                        <p class="gender-text">Total Female Residents</p>
                        <p class="gender-count"><?php echo $female_count; ?></p>
                    </div>
                </div>
            </div>

    <!-- gender icons FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
                        foreach ($barangays as $barangay): 
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
        <!-- Residents List -->
        <div class="card">
       
    <div class="list-container">
            <h2>Residents List </h2>
            <table>
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>Last Name</th>
                        <th>Barangay</th>
                        <th>Gender</th>
                        <th>Age</th>
                        <th>Date of Birth</th> <!-- New column -->
                    
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch residents from the database
                    $stmt = $pdo->query("SELECT residents.*, barangays.name AS barangay_name FROM residents 
                                          JOIN barangays ON residents.barangay_id = barangays.id");
                    $residents = $stmt->fetchAll();

                    foreach ($residents as $resident) {
                        echo "<tr>";
                        echo "<td>".htmlspecialchars($resident['first_name'], ENT_QUOTES)."</td>";
                        echo "<td>".htmlspecialchars($resident['middle_name'], ENT_QUOTES)."</td>";
                        echo "<td>".htmlspecialchars($resident['last_name'], ENT_QUOTES)."</td>";
                        echo "<td>".htmlspecialchars($resident['barangay_name'], ENT_QUOTES)."</td>";
                        echo "<td>".htmlspecialchars($resident['gender'], ENT_QUOTES)."</td>";
                        echo "<td>".htmlspecialchars($resident['age'], ENT_QUOTES)."</td>";
                        echo "<td>".htmlspecialchars($resident['date_of_birth'], ENT_QUOTES)."</td>"; // New column
                       
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
</html>