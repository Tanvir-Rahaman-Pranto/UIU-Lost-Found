<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!empty($_FILES['profile_photo']['name'])) {
    $upload_dir = "../uploads/profiles/";

    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $extension = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
    $filename  = "profile_" . $user_id . "_" . time() . "." . $extension;
    $filepath  = $upload_dir . $filename;

    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array(strtolower($extension), $allowed)) {
        move_uploaded_file($_FILES['profile_photo']['tmp_name'], $filepath);
        $photo_url = "uploads/profiles/" . $filename;

        $sql = "UPDATE users SET profile_photo = '$photo_url' WHERE id = '$user_id'";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['user_photo'] = $photo_url;
        }
    }
}

header("Location: ../user.php");
exit();
?>
