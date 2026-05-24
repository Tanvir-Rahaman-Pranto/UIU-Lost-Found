<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}

$user_id    = $_SESSION['user_id'];
$full_name  = mysqli_real_escape_string($conn, $_POST['full_name']);
$student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
$email      = mysqli_real_escape_string($conn, $_POST['email']);
$phone      = mysqli_real_escape_string($conn, $_POST['phone']);
$location   = mysqli_real_escape_string($conn, $_POST['location']);
$bio        = mysqli_real_escape_string($conn, $_POST['bio']);

$sql = "UPDATE users SET 
        full_name  = '$full_name',
        student_id = '$student_id',
        email      = '$email',
        phone      = '$phone',
        location   = '$location',
        bio        = '$bio'
        WHERE id   = '$user_id'";

if (mysqli_query($conn, $sql)) {
    $_SESSION['user_name'] = $full_name;
    header("Location: ../user.php");
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
