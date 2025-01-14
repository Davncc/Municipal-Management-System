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

    
    if (empty($first_name) || empty($last_name) || empty($barangay_id) || empty($gender) || empty($date_of_birth)) {
        echo "<script>alert('All fields are required!');</script>";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO residents (first_name, middle_name, last_name, barangay_id, gender, age, date_of_birth) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$first_name, $middle_name, $last_name, $barangay_id, $gender, $age, $date_of_birth]);

            echo "<script>alert('Resident added successfully!'); window.location.href = 'add_resident.php';</script>";
        } catch (PDOException $e) {
            echo "Database Error: " . $e->getMessage();
            
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
        h2 { font-size: 28px; margin-bottom: 20px; color: #2C3E50; text-align:center; }
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
            <li><a href="dashboard.php">Home</a></li>
            <li><a href="add_barangay.php">Barangay Management</a></li>
            <li><a href="add_resident.php">Resident Management</a></li>
            <li><a href="upload.php">Image Upload</a></li>

            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
       
        <div class="list-container">
            <h2>Residents List Management</h2>
            <table>
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>Last Name</th>
                        <th>Barangay</th>
                        <th>Gender</th>
                        <th>Age</th>
                        <th>Date of Birth</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($residents as $resident): ?>
                        <tr class="barangay-row" data-id="<?php echo $resident['id']; ?>" onclick="highlightRow(<?php echo $resident['id']; ?>)">
                            <td><?php echo htmlspecialchars($resident['first_name']); ?></td>
                            <td><?php echo htmlspecialchars($resident['middle_name']); ?></td>
                            <td><?php echo htmlspecialchars($resident['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($resident['barangay_name']); ?></td>
                            <td><?php echo htmlspecialchars($resident['gender']); ?></td>
                            <td><?php echo htmlspecialchars($resident['age']); ?></td>
                            <td><?php echo htmlspecialchars($resident['date_of_birth']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div id="action-buttons" class="action-buttons">
                <button onclick="editResident()">Edit Selected Resident</button>
                <button onclick="deleteResident()">Delete Selected Resident</button>
            </div>
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
