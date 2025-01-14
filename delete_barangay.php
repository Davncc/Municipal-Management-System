<?php
session_start();
require 'db.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if the barangay ID was sent
if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // Prepare the delete statement
    $stmt = $pdo->prepare("DELETE FROM barangays WHERE id = ?");
    
    // Execute the statement
    if ($stmt->execute([$id])) {
        // Redirect back to the dashboard with success message
        header("Location: dashboard.php?status=deleted");
    } else {
        // Redirect back with an error message
        header("Location: dashboard.php?status=error");
    }
} else {
    header("Location: dashboard.php?status=error");
}
exit;
