<?php
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($username) || empty($password) || empty($confirm_password)) {
        header("Location: ../register.php?error=empty_fields");
        exit();
    }

    if ($password !== $confirm_password) {
        header("Location: ../register.php?error=password_mismatch");
        exit();
    }

    $database = new Database();
    $conn = $database->getConnection();

    if (!$conn) {
        header("Location: ../register.php?error=db_error");
        exit();
    }

    try {
        // Check if username already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            header("Location: ../register.php?error=username_taken");
            exit();
        }

        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $password]); // Note: In production, use password_hash()

        header("Location: ../login.php?success=registered");
        exit();
    } catch(PDOException $e) {
        header("Location: ../register.php?error=db_error");
        exit();
    }
} else {
    header("Location: ../register.php");
    exit();
}
?> 