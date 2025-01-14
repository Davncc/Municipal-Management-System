<?php
require 'db.php';

$stmt = $pdo->prepare("SELECT id, name FROM barangays");
$stmt->execute();
$barangays = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['name'])) {
        $name = $_POST['name'];
        $stmt = $pdo->prepare("INSERT INTO barangays (name) VALUES (?)");
        $stmt->execute([$name]);
        echo "<script>alert('Barangay added successfully!'); window.location.href = 'add_barangay.php';</script>";
    } elseif (isset($_POST['delete_barangay_id'])) {
        $barangayId = $_POST['delete_barangay_id'];
        $stmt = $pdo->prepare("DELETE FROM barangays WHERE id = ?");
        $stmt->execute([$barangayId]);
        echo "<script>alert('Barangay deleted successfully!'); window.location.href = 'add_barangay.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Management</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background-color: #f0f0f0; }
        .wrapper { display: flex; }
        .sidebar { width: 250px; background-color: #2C3E50; color: white; padding: 20px; position: fixed; top: 0; bottom: 0; left: 0; box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1); }
        .sidebar h2 { text-align: center; font-size: 28px; color: #ECF0F1; margin-bottom: 40px; }
        .sidebar ul { list-style: none; padding-left: 0; }
        .sidebar ul li { margin-bottom: 25px; }
        .sidebar ul li a { text-decoration: none; color: #BDC3C7; font-size: 18px; padding: 10px; display: block; border-radius: 5px; transition: background-color 0.3s; }
        .sidebar ul li a:hover { background-color: rgb(66, 203, 194); color: white; transform: scale(1.05); }
        .main-content { margin-left: 250px; padding: 20px; flex-grow: 1; }
        h2,h3 { font-size: 28px; margin-bottom: 20px; color: #2C3E50; text-align:center;}
        .form-container { max-width: 500px; margin: 20px auto; padding: 20px; background-color: white; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); border-radius: 8px; }
        label { display: block; margin-bottom: 8px; font-size: 16px; color: #2C3E50; }
        input, button { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; }
        button { background-color: #4CAF50; color: white; cursor: pointer; transition: background-color 0.3s; }
        button:hover { background-color: #45a049; }
        .card { margin: 20px auto; padding: 20px; background-color: white; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); border-radius: 8px; }
        .card h3 { margin-bottom: 15px; color: #2C3E50; }
        table { width: 100%; border-collapse: collapse; margin-bottom:20px;}
        th, td { padding: 12px; border: 1px solid #ddd; text-align: center; }
        th { background-color: #f2f2f2; color: #2C3E50; }
        tr:hover { background-color: #f5f5f5; }

        .selected { background-color:rgb(127, 227, 154); color: black; }

        /* Button Styles */
        .edit-btn, #delete-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 20px;
            font-size: 14px;
            width: 100%;
            text-align: center;
            transition: background-color 0.3s;
           
        }

        #delete-btn 
        
            background-color: #4CAF50; 

        .edit-btn:hover, #delete-btn:hover {
            
            background-color: rgb(146, 209, 165);
        }

        #delete-btn:hover {
            background-color:rgb(163, 114, 114);
        }

        /* Centering Edit Button */
        #edit-btn-container {
            display: flex;
            justify-content: center;
            margin-bottom: 10px;  /* Increased space between last row and the edit button */
            width: 100%;
        }

        .action-buttons {
            display: none;
            width: 100%;
            max-width: 500px;
            margin: 0 auto; 
        }

        .action-buttons.show {
            display: block;
        }

        .barangay-row:hover { background-color:rgb(139, 148, 139); color: white; }
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

            <div class="form-container">
            <h2>Barangay Management</h2>
                <form action="add_barangay.php" method="POST">
                    <label for="name">Barangay Name:</label>
                    <input type="text" id="name" name="name" required>
                    <button type="submit">Add Barangay</button>
                </form>
            </div>

            <div class="card">
                <h3>Barangay List Management</h3>
                <form id="barangay-form" action="add_barangay.php" method="POST">
                    <input type="hidden" id="delete_barangay_id" name="delete_barangay_id">
                    <table>
                        <thead>
                            <tr>
                                <th>List of Barangay</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($barangays as $barangay): ?>
                                <tr class="barangay-row" data-id="<?php echo $barangay['id']; ?>" onclick="highlightRow(<?php echo $barangay['id']; ?>)">
                                    <td><?php echo htmlspecialchars($barangay['name']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div id="action-buttons-container" class="action-buttons">
                        <div id="edit-btn-container">
                            <a href="#" id="edit-btn" class="edit-btn" onclick="editBarangay()">Edit Selected Barangay</a>
                        </div>
                        <button type="button" id="delete-btn" onclick="deleteBarangay()">Delete Selected Barangay</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let selectedBarangayId = null;

        function highlightRow(barangayId) {
            const rows = document.querySelectorAll('.barangay-row');
            rows.forEach(row => row.classList.remove('selected'));
            const selectedRow = document.querySelector(`.barangay-row[data-id='${barangayId}']`);
            selectedRow.classList.add('selected');
            selectedBarangayId = barangayId;
            toggleActionButtons();
        }

        function toggleActionButtons() {
            const actionButtonsContainer = document.getElementById('action-buttons-container');
            actionButtonsContainer.style.display = selectedBarangayId ? 'block' : 'none';
        }

        function deleteBarangay() {
            if (!selectedBarangayId) {
                alert("Please select a barangay to delete.");
                return;
            }
            if (confirm('Are you sure you want to delete the selected barangay?')) {
                document.getElementById('delete_barangay_id').value = selectedBarangayId;
                document.getElementById('barangay-form').submit();
            }
        }

        function editBarangay() {
            if (selectedBarangayId) {
                window.location.href = `edit_barangay.php?id=${selectedBarangayId}`;
            } else {
                alert("Please select a barangay to edit.");
            }
        }
    </script>
</body>
</html>
