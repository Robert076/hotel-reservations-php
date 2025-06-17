<?php
session_start();
require_once 'includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get room ID from URL
$room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;
$checkin = isset($_GET['checkin']) ? $_GET['checkin'] : '';
$checkout = isset($_GET['checkout']) ? $_GET['checkout'] : '';
$guests = isset($_GET['guests']) ? (int)$_GET['guests'] : 0;

if (!$room_id || !$checkin || !$checkout || !$guests) {
    header('Location: rooms.php');
    exit();
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Get room details
    $stmt = $conn->prepare("SELECT * FROM hotel_rooms WHERE id = ?");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$room) {
        header('Location: rooms.php');
        exit();
    }

    // Calculate number of nights
    $checkin_date = new DateTime($checkin);
    $checkout_date = new DateTime($checkout);
    $nights = $checkin_date->diff($checkout_date)->days;

    // Calculate occupancy rate for the date range
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT r.room_id) as booked_rooms
        FROM reservations r
        WHERE (r.checkin_date <= ? AND r.checkout_date >= ?)
        OR (r.checkin_date <= ? AND r.checkout_date >= ?)
        OR (r.checkin_date >= ? AND r.checkout_date <= ?)
    ");
    $stmt->execute([$checkout, $checkin, $checkout, $checkin, $checkin, $checkout]);
    $booked_rooms = $stmt->fetch(PDO::FETCH_ASSOC)['booked_rooms'];

    // Get total number of rooms
    $stmt = $conn->query("SELECT COUNT(*) as total_rooms FROM hotel_rooms");
    $total_rooms = $stmt->fetch(PDO::FETCH_ASSOC)['total_rooms'];

    // Calculate occupancy rate
    $occupancy_rate = ($booked_rooms / $total_rooms) * 100;

    // Calculate price multiplier based on occupancy
    $price_multiplier = 1.0; // Base price
    if ($occupancy_rate > 80) {
        $price_multiplier = 1.5; // 50% increase
    } elseif ($occupancy_rate > 50) {
        $price_multiplier = 1.2; // 20% increase
    }

    // Calculate total price with dynamic pricing
    $base_price = $room['base_price'];
    $price_per_night = $base_price * $price_multiplier;
    $total_price = $price_per_night * $nights;

} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Room - Hotel Reservations</title>
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
        <h1>Book Room <?php echo htmlspecialchars($room['room_number']); ?></h1>
        
        <div class="booking-details">
            <h2>Booking Details</h2>
            <p><strong>Room Number:</strong> <?php echo htmlspecialchars($room['room_number']); ?></p>
            <p><strong>Capacity:</strong> <?php echo htmlspecialchars($room['capacity']); ?> guests</p>
            <p><strong>Check-in Date:</strong> <?php echo htmlspecialchars($checkin); ?></p>
            <p><strong>Check-out Date:</strong> <?php echo htmlspecialchars($checkout); ?></p>
            <p><strong>Number of Nights:</strong> <?php echo $nights; ?></p>
            <p><strong>Number of Guests:</strong> <?php echo $guests; ?></p>
            <p><strong>Base Price per Night:</strong> $<?php echo number_format($base_price, 2); ?></p>
            <?php if ($price_multiplier > 1.0): ?>
                <p><strong>Price Adjustment:</strong> <?php echo ($price_multiplier - 1) * 100; ?>% (due to <?php echo $occupancy_rate > 80 ? 'high' : 'moderate'; ?> occupancy)</p>
            <?php endif; ?>
            <p><strong>Price per Night:</strong> $<?php echo number_format($price_per_night, 2); ?></p>
            <p><strong>Total Price:</strong> $<?php echo number_format($total_price, 2); ?></p>
        </div>

        <form action="handlers/create_reservation.php" method="POST" class="booking-form">
            <input type="hidden" name="room_id" value="<?php echo $room_id; ?>">
            <input type="hidden" name="checkin_date" value="<?php echo $checkin; ?>">
            <input type="hidden" name="checkout_date" value="<?php echo $checkout; ?>">
            <input type="hidden" name="number_of_guests" value="<?php echo $guests; ?>">
            <input type="hidden" name="total_price" value="<?php echo $total_price; ?>">
            
            <button type="submit" class="btn">Confirm Booking</button>
        </form>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 Hotel Reservations. All rights reserved.</p>
        </div>
    </footer>
</body>
</html> 