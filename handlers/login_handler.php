<?php
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        header("Location: ../login.php?error=empty_fields");
        exit();
    }

    $database = new Database();
    $conn = $database->getConnection();

    if (!$conn) {
        header("Location: ../login.php?error=db_error");
        exit();
    }

    try {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $password == $user['password']) { // Note: In production, use password_verify() with hashed passwords
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: ../index.php");
            exit();
        } else {
            header("Location: ../login.php?error=invalid_credentials");
            exit();
        }
    } catch(PDOException $e) {
        header("Location: ../login.php?error=db_error");
        exit();
    }
} else {
    header("Location: ../login.php");
    exit();
}
?> 