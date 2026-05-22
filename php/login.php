<?php
session_start();
include '../db.php';

$student_id = $_POST['id'];
$email      = $_POST['email'];
$pass       = $_POST['password'];

$sql    = "SELECT * FROM users WHERE id = '$student_id' AND email = '$email'";
$result = mysqli_query($conn, $sql);
$user   = mysqli_fetch_assoc($result);

if ($user && password_verify($pass, $user['password_hash'])) {
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_name'] = $user['full_name'];
    header("Location: ../index.php");
    exit();
} else {
    header("Location: ../login.html?error=1");
    exit();
}
?>