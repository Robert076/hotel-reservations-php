<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Reservations</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">Hotel Reservations</div>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="rooms.php">Rooms</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="my_reservations.php">My Reservations</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <section class="hero">
            <h1>Welcome to Our Hotel</h1>
            <p>Book your perfect stay with us</p>
            <?php if(!isset($_SESSION['user_id'])): ?>
                <div class="cta-buttons">
                    <a href="register.php" class="btn">Register Now</a>
                    <a href="login.php" class="btn">Login</a>
                </div>
            <?php else: ?>
                <div class="cta-buttons">
                    <a href="rooms.php" class="btn">Book a Room</a>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Hotel Reservations. All rights reserved.</p>
    </footer>
</body>
</html> 