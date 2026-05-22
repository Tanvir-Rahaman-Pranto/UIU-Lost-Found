<?php
include '../db.php';
$id = $_POST['id'];
$name  = $_POST['full_name'];
$email = $_POST['email'];
$pass  = password_hash($_POST['password'], PASSWORD_DEFAULT);

$sql = "INSERT INTO users (id, full_name, email, password_hash) 
        VALUES ('$id', '$name', '$email', '$pass')";

if (mysqli_query($conn, $sql)) {
    header("Location: ../login.html");
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}
?>