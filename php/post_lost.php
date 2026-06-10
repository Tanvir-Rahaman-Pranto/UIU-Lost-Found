<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}

$user_id       = $_SESSION['user_id'];
$type          = 'lost';
$item_name     = $_POST['item_name'];
$category      = $_POST['category'];
$description   = $_POST['description'];
$location      = $_POST['location'];
$specific_spot = $_POST['specific_spot'];
$date_reported = $_POST['date_reported'];
$time_reported = $_POST['time_reported'];

// Handle photo upload
$photo_url = "";
if (!empty($_FILES['photo']['name'])) {
    $upload_dir = "../uploads/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $filename  = time() . "_" . uniqid() . "." . $extension;
    $allowed   = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array(strtolower($extension), $allowed)) {
        move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $filename);
        $photo_url = "uploads/" . $filename;
    }
}

$item_name     = mysqli_real_escape_string($conn, $item_name);
$category      = mysqli_real_escape_string($conn, $category);
$description   = mysqli_real_escape_string($conn, $description);
$location      = mysqli_real_escape_string($conn, $location);
$specific_spot = mysqli_real_escape_string($conn, $specific_spot);

$sql = "INSERT INTO posts 
        (user_id, type, item_name, category, description, photo_url, location, specific_spot, date_reported, time_reported) 
        VALUES 
        ('$user_id', '$type', '$item_name', '$category', '$description', '$photo_url', '$location', '$specific_spot', '$date_reported', '$time_reported')";

if (mysqli_query($conn, $sql)) {
    header("Location: ../index.php");
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}
?>