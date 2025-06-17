<?php
session_start();
require_once '../includes/db_connect.php';

// Redirect if not logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = $_POST['room_id'] ?? null;
    $checkin_date = $_POST['checkin_date'] ?? null;
    $checkout_date = $_POST['checkout_date'] ?? null;
    $number_of_guests = $_POST['number_of_guests'] ?? null;
    $total_price = $_POST['total_price'] ?? null;

    if(!$room_id || !$checkin_date || !$checkout_date || !$number_of_guests || !$total_price) {
        header("Location: ../rooms.php?error=missing_data");
        exit();
    }

    $database = new Database();
    $conn = $database->getConnection();

    try {
        // Check if room is available for these dates
        $stmt = $conn->prepare("
            SELECT COUNT(*) FROM reservations 
            WHERE room_id = ? 
            AND ((checkin_date <= ? AND checkout_date >= ?) 
            OR (checkin_date <= ? AND checkout_date >= ?))
        ");
        $stmt->execute([$room_id, $checkout_date, $checkout_date, $checkin_date, $checkin_date]);
        $conflicting_reservations = $stmt->fetchColumn();

        if($conflicting_reservations > 0) {
            header("Location: ../rooms.php?error=room_unavailable");
            exit();
        }

        // Create reservation
        $stmt = $conn->prepare("
            INSERT INTO reservations (user_id, room_id, checkin_date, checkout_date, number_of_guests, total_price) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $room_id,
            $checkin_date,
            $checkout_date,
            $number_of_guests,
            $total_price
        ]);

        header("Location: ../my_reservations.php?success=booked");
        exit();
    } catch(PDOException $e) {
        header("Location: ../rooms.php?error=db_error");
        exit();
    }
} else {
    header("Location: ../rooms.php");
    exit();
}
?> 