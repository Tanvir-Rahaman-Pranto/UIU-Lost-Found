<?php
include '../db.php';

$student_id = $_POST['student_id'];
$name       = $_POST['full_name'];
$email      = $_POST['email'];
$pass       = password_hash($_POST['password'], PASSWORD_DEFAULT);

$sql = "INSERT INTO users (full_name, student_id, email, password_hash) 
        VALUES ('$name', '$student_id', '$email', '$pass')";

if (mysqli_query($conn, $sql)) {
    header("Location: ../login.html");
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}
?>