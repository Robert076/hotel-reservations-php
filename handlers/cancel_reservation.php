<?php
session_start();
require_once '../includes/db_connect.php';

// Redirect if not logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reservation_id = $_POST['reservation_id'] ?? null;

    if(!$reservation_id) {
        header("Location: ../my_reservations.php?error=missing_data");
        exit();
    }

    $database = new Database();
    $conn = $database->getConnection();

    try {
        // Verify the reservation belongs to the user
        $stmt = $conn->prepare("SELECT id FROM reservations WHERE id = ? AND user_id = ?");
        $stmt->execute([$reservation_id, $_SESSION['user_id']]);
        
        if(!$stmt->fetch()) {
            header("Location: ../my_reservations.php?error=unauthorized");
            exit();
        }

        // Delete the reservation
        $stmt = $conn->prepare("DELETE FROM reservations WHERE id = ?");
        $stmt->execute([$reservation_id]);

        header("Location: ../my_reservations.php?success=cancelled");
        exit();
    } catch(PDOException $e) {
        header("Location: ../my_reservations.php?error=db_error");
        exit();
    }
} else {
    header("Location: ../my_reservations.php");
    exit();
}
?> 