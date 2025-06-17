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

// Get all rooms
$rooms = [];
try {
    $stmt = $conn->query("SELECT * FROM hotel_rooms ORDER BY room_number");
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Failed to load rooms";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Rooms - Hotel Reservations</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">Hotel Reservations</a>
            <div class="nav-links">
                <a href="rooms.php">Rooms</a>
                <a href="my_reservations.php">My Reservations</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <main class="container">
        <h1>Available Rooms</h1>
        
        <?php if(isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="rooms-grid">
            <?php foreach($rooms as $room): ?>
                <div class="room-card">
                    <h3>Room <?php echo htmlspecialchars($room['room_number']); ?></h3>
                    <p><strong>Capacity:</strong> <?php echo htmlspecialchars($room['capacity']); ?> guests</p>
                    <p><strong>Base Price:</strong> $<?php echo htmlspecialchars($room['base_price']); ?> per night</p>
                    <form action="book_room.php" method="GET">
                        <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                        <div class="form-group">
                            <label for="checkin_<?php echo $room['id']; ?>">Check-in Date:</label>
                            <input type="date" id="checkin_<?php echo $room['id']; ?>" name="checkin" required>
                        </div>
                        <div class="form-group">
                            <label for="checkout_<?php echo $room['id']; ?>">Check-out Date:</label>
                            <input type="date" id="checkout_<?php echo $room['id']; ?>" name="checkout" required>
                        </div>
                        <div class="form-group">
                            <label for="guests_<?php echo $room['id']; ?>">Number of Guests:</label>
                            <input type="number" id="guests_<?php echo $room['id']; ?>" name="guests" min="1" max="<?php echo $room['capacity']; ?>" required>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn">Book Now</button>
                        </div>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 Hotel Reservations. All rights reserved.</p>
        </div>
    </footer>
</body>
</html> 