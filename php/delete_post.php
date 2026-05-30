<?php
session_start();
include '../db.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}

// Must have a valid post ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../index.php");
    exit();
}

$post_id = (int) $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch the post to verify ownership
$sql    = "SELECT * FROM posts WHERE id = '$post_id'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) === 0) {
    header("Location: ../index.php");
    exit();
}

$post = mysqli_fetch_assoc($result);

// Only the post owner can delete
if ($post['user_id'] != $user_id) {
    header("Location: ../item.php?id=$post_id");
    exit();
}

// Delete associated comments first (to avoid FK constraint issues)
mysqli_query($conn, "DELETE FROM comments WHERE post_id = '$post_id'");

// Delete the post
mysqli_query($conn, "DELETE FROM posts WHERE id = '$post_id' AND user_id = '$user_id'");

// Redirect to My Posts or Home
// Redirect back to referring page, but not item.php (post no longer exists)
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../index.php';

if (strpos($referer, 'item.php') !== false) {
    header("Location: ../index.php");
} else {
    header("Location: $referer");
}
exit();
?>