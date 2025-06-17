<?php
session_start();
require_once 'includes/db_connect.php';

// Redirect if not logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();

// Get user's reservations
$reservations = [];
try {
    $stmt = $conn->prepare("
        SELECT r.*, hr.room_number, hr.capacity, hr.base_price 
        FROM reservations r 
        JOIN hotel_rooms hr ON r.room_id = hr.id 
        WHERE r.user_id = ? 
        ORDER BY r.checkin_date DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Failed to load reservations";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reservations - Hotel Reservations</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">Hotel Reservations</div>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="rooms.php">Rooms</a></li>
                <li><a href="my_reservations.php">My Reservations</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h2>My Reservations</h2>
        
        <?php if(isset($_GET['success']) && $_GET['success'] === 'booked'): ?>
            <div class="success">Room booked successfully!</div>
        <?php endif; ?>

        <?php if(isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if(empty($reservations)): ?>
            <p>You have no reservations yet. <a href="rooms.php">Book a room now!</a></p>
        <?php else: ?>
            <div class="reservations-grid">
                <?php foreach($reservations as $reservation): ?>
                    <div class="reservation-card">
                        <h3>Room <?php echo htmlspecialchars($reservation['room_number']); ?></h3>
                        <p><strong>Check-in:</strong> <?php echo htmlspecialchars($reservation['checkin_date']); ?></p>
                        <p><strong>Check-out:</strong> <?php echo htmlspecialchars($reservation['checkout_date']); ?></p>
                        <p><strong>Guests:</strong> <?php echo htmlspecialchars($reservation['number_of_guests']); ?></p>
                        <p><strong>Total Price:</strong> $<?php echo htmlspecialchars($reservation['total_price']); ?></p>
                        <form action="handlers/cancel_reservation.php" method="POST" onsubmit="return confirm('Are you sure you want to cancel this reservation?');">
                            <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                            <div class="form-group">
                                <input type="submit" value="Cancel Reservation" class="btn btn-danger">
                            </div>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2024 Hotel Reservations. All rights reserved.</p>
    </footer>
</body>
</html> 