<?php
require 'db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Delete resident from database
    $stmt = $pdo->prepare("DELETE FROM residents WHERE id = ?");
    $stmt->execute([$id]);

    echo "<script>alert('Resident deleted successfully!'); window.location.href = 'add_resident.php';</script>";
} else {
    echo "<script>alert('Invalid request.'); window.location.href = 'add_resident.php';</script>";
}
?>
