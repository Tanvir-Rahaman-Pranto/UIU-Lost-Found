<?php
session_start();
include 'db.php';

// Get post ID from URL
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$post_id = $_GET['id'];

// Fetch post with user info
$sql    = "SELECT posts.*, users.full_name, users.student_id, users.phone, users.email, users.profile_photo, users.created_at as member_since
           FROM posts
           JOIN users ON posts.user_id = users.id
           WHERE posts.id = '$post_id'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) === 0) {
    header("Location: index.php");
    exit();
}

$post = mysqli_fetch_assoc($result);

// Fetch comments
$comments_sql    = "SELECT comments.*, users.full_name, users.profile_photo
                    FROM comments
                    JOIN users ON comments.user_id = users.id
                    WHERE comments.post_id = '$post_id'
                    ORDER BY comments.created_at ASC";
$comments_result = mysqli_query($conn, $comments_sql);
$comment_count   = mysqli_num_rows($comments_result);

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.html");
        exit();
    }
    $user_id = $_SESSION['user_id'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    if (!empty($comment)) {
        $c_sql = "INSERT INTO comments (post_id, user_id, comment) VALUES ('$post_id', '$user_id', '$comment')";
        mysqli_query($conn, $c_sql);
        header("Location: item.php?id=$post_id");
        exit();
    }
}

// Handle mark as resolved
if (isset($_GET['resolve']) && isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']) {
    mysqli_query($conn, "UPDATE posts SET status = 'resolved' WHERE id = '$post_id'");
    header("Location: item.php?id=$post_id");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['item_name']) ?> - UIU Lost & Found</title>
    <link rel="stylesheet" href="item.css">
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>

<!-- NAV -->
<nav>
  <a href="index.php" class="nav-logo">
    <img src="logo.png" alt="UIU Lost & Found Logo">
    UIU <span>Lost &amp; Found</span>
  </a>

  <div class="nav-links">
    <button type="button" onclick="window.location.href='searchpage.php'" class="search-btn">
      <i class="fa-solid fa-magnifying-glass"></i>
    </button>

    <a href="index.php"><i class="fa-solid fa-house"></i> Home</a>

    <?php if (isset($_SESSION['user_id'])): ?>
      <div class="user-menu">
        <?php if (!empty($_SESSION['user_photo'])): ?>
          <img src="<?= htmlspecialchars($_SESSION['user_photo']) ?>" alt="User" />
        <?php else: ?>
          <div class="nav-avatar"><?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?></div>
        <?php endif; ?>
        <h4><?= htmlspecialchars($_SESSION['user_name']) ?></h4>
        <i class="fa-solid fa-chevron-down" onclick="toggleDropdown(event)"></i>
        <div class="user-dropdown" id="userDropdown">
          <a href="user.php"><i class="fa-solid fa-user"></i> My Profile</a>
          <a href="my-posts.php"><i class="fa-solid fa-list"></i> My Posts</a>
          <hr style="border:none;border-top:1px solid #eee;margin:4px 0;">
          <a href="php/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
      </div>
    <?php else: ?>
      <a href="login.html"><i class="fa-solid fa-user"></i> Login</a>
      <a href="register.html" class="btn-nav"><i class="fa-solid fa-user-plus"></i> Register</a>
    <?php endif; ?>
  </div>

  <button class="hamburger" onclick="toggleMenu()" aria-label="Toggle menu">
    <span></span><span></span><span></span>
  </button>
</nav>

<div class="mobile-menu" id="mobileMenu">
  <a href="index.php"><i class="fa-solid fa-house"></i> Home</a>
  <?php if (isset($_SESSION['user_id'])): ?>
    <a href="user.php"><i class="fa-solid fa-user"></i> My Profile</a>
    <a href="my-posts.php"><i class="fa-solid fa-list"></i> My Posts</a>
    <a href="php/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
  <?php else: ?>
    <a href="login.html"><i class="fa-solid fa-user"></i> Login</a>
    <a href="register.html" class="btn-nav"><i class="fa-solid fa-user-plus"></i> Register</a>
  <?php endif; ?>
</div>

<!-- MAIN -->
<main>

  <!-- Breadcrumb -->
  <div class="breadcrumb">
    <a href="index.php">Home</a> <span>›</span>
    <a href="#"><?= $post['type'] === 'lost' ? 'Lost Items' : 'Found Items' ?></a> <span>›</span>
    <span><?= htmlspecialchars($post['item_name']) ?></span>
  </div>

  <div class="detail-card">

    <!-- Top -->
    <div class="detail-top">
      <?php if (!empty($post['photo_url'])): ?>
        <img class="detail-img" src="<?= htmlspecialchars($post['photo_url']) ?>" alt="<?= htmlspecialchars($post['item_name']) ?>" />
      <?php else: ?>
        <div class="detail-img-placeholder">📦</div>
      <?php endif; ?>

      <div class="detail-info">
        <span class="type-badge <?= $post['type'] === 'lost' ? 'type-lost' : 'type-found' ?>">
          <i class="fa-solid <?= $post['type'] === 'lost' ? 'fa-circle-exclamation' : 'fa-circle-check' ?>"></i>
          <?= ucfirst($post['type']) ?> Item
        </span>

        <h1 class="detail-title"><?= htmlspecialchars($post['item_name']) ?></h1>
        <p class="detail-desc"><?= htmlspecialchars($post['description']) ?></p>

        <div class="meta-row">
          <?php if (!empty($post['location'])): ?>
            <span class="meta-pill"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($post['location']) ?></span>
          <?php endif; ?>
          <?php if (!empty($post['date_reported'])): ?>
            <span class="meta-pill"><i class="fa-solid fa-calendar"></i> <?= date('M j, Y', strtotime($post['date_reported'])) ?></span>
          <?php endif; ?>
          <?php if (!empty($post['time_reported'])): ?>
            <span class="meta-pill"><i class="fa-solid fa-clock"></i> <?= date('g:i A', strtotime($post['time_reported'])) ?></span>
          <?php endif; ?>
          <?php if (!empty($post['category'])): ?>
            <span class="meta-pill"><i class="fa-solid fa-tag"></i> <?= htmlspecialchars($post['category']) ?></span>
          <?php endif; ?>
        </div>

        <?php if ($post['status'] === 'open'): ?>
          <span class="status-open">Active &nbsp;·&nbsp; Open</span>
        <?php else: ?>
          <span class="status-resolved">✅ &nbsp;Resolved</span>
        <?php endif; ?>
      </div>
    </div>

    <hr class="divider" />

    <!-- Bottom details -->
    <div class="detail-bottom">
      <div class="detail-section">
        <div class="section-title">Item Information</div>
        <div class="info-row">
          <i class="fa-solid fa-box"></i>
          <span class="info-row-label">Item Name</span>
          <span class="info-row-value"><?= htmlspecialchars($post['item_name']) ?></span>
        </div>
        <?php if (!empty($post['category'])): ?>
        <div class="info-row">
          <i class="fa-solid fa-tag"></i>
          <span class="info-row-label">Category</span>
          <span class="info-row-value"><?= htmlspecialchars($post['category']) ?></span>
        </div>
        <?php endif; ?>
        <div class="info-row">
          <i class="fa-solid fa-align-left"></i>
          <span class="info-row-label">Description</span>
          <span class="info-row-value"><?= htmlspecialchars($post['description']) ?></span>
        </div>
        <div class="info-row">
          <i class="fa-solid fa-location-dot"></i>
          <span class="info-row-label"><?= $post['type'] === 'lost' ? 'Last Seen' : 'Found At' ?></span>
          <span class="info-row-value"><?= htmlspecialchars($post['location'] ?? 'N/A') ?></span>
        </div>
        <?php if (!empty($post['specific_spot'])): ?>
        <div class="info-row">
          <i class="fa-solid fa-map-pin"></i>
          <span class="info-row-label">Specific Spot</span>
          <span class="info-row-value"><?= htmlspecialchars($post['specific_spot']) ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($post['date_reported'])): ?>
        <div class="info-row">
          <i class="fa-solid fa-calendar"></i>
          <span class="info-row-label"><?= $post['type'] === 'lost' ? 'Date Lost' : 'Date Found' ?></span>
          <span class="info-row-value"><?= date('M j, Y', strtotime($post['date_reported'])) ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($post['held_at'])): ?>
        <div class="info-row">
          <i class="fa-solid fa-warehouse"></i>
          <span class="info-row-label">Item Held At</span>
          <span class="info-row-value"><?= htmlspecialchars($post['held_at']) ?></span>
        </div>
        <?php endif; ?>
      </div>

      <div class="detail-section">
        <div class="section-title">Contact Information</div>
        <div class="info-row">
          <i class="fa-solid fa-user"></i>
          <span class="info-row-label"><?= $post['type'] === 'lost' ? 'Posted By' : 'Found By' ?></span>
          <span class="info-row-value"><?= htmlspecialchars($post['full_name']) ?></span>
        </div>
        <?php if (!empty($post['student_id'])): ?>
        <div class="info-row">
          <i class="fa-solid fa-id-card"></i>
          <span class="info-row-label">Student ID</span>
          <span class="info-row-value"><?= htmlspecialchars($post['student_id']) ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($post['phone'])): ?>
        <div class="info-row">
          <i class="fa-solid fa-phone"></i>
          <span class="info-row-label">Phone</span>
          <span class="info-row-value"><?= htmlspecialchars($post['phone']) ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($post['email'])): ?>
        <div class="info-row">
          <i class="fa-regular fa-envelope"></i>
          <span class="info-row-label">Email</span>
          <span class="info-row-value"><?= htmlspecialchars($post['email']) ?></span>
        </div>
        <?php endif; ?>
        <div class="info-row">
          <i class="fa-solid fa-comment"></i>
          <span class="info-row-label">Comments</span>
          <span class="info-row-value"><?= $comment_count ?></span>
        </div>
        <div class="info-row">
          <i class="fa-solid fa-calendar-plus"></i>
          <span class="info-row-label">Posted On</span>
          <span class="info-row-value"><?= date('M j, Y', strtotime($post['created_at'])) ?></span>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="detail-actions">
      <div class="actions-left">
        <?php if (!empty($post['phone'])): ?>
          <a href="tel:<?= htmlspecialchars($post['phone']) ?>" class="btn btn-contact">
            <i class="fa-solid fa-phone"></i>
            <?= $post['type'] === 'lost' ? 'Contact Owner' : 'Contact Finder' ?>
          </a>
        <?php endif; ?>

        <?php if ($post['type'] === 'found'): ?>
          <button class="btn btn-claim" onclick="document.getElementById('commentBox').scrollIntoView({behavior:'smooth'})">
            <i class="fa-solid fa-hand-holding-heart"></i> This is Mine
          </button>
        <?php endif; ?>

        <!-- Only show resolve button to post owner -->
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id'] && $post['status'] === 'open'): ?>
          <a href="item.php?id=<?= $post_id ?>&resolve=1" class="btn btn-resolved"
             onclick="return confirm('Mark this post as resolved?')">
            <i class="fa-solid fa-circle-check"></i> Mark as Resolved
          </a>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
  <a href="php/delete_post.php?id=<?= $post_id ?>" class="btn btn-danger"
     onclick="return confirm('Are you sure you want to delete this post? This cannot be undone.')">
    <i class="fa-solid fa-trash"></i> Delete Post
  </a>
<?php endif; ?>
      </div>
      <button class="btn btn-report"><i class="fa-solid fa-flag"></i> Report Post</button>
    </div>

  </div>

  <!-- Poster Card -->
  <div class="section-label">Posted by</div>
  <div class="poster-card">
    <?php if (!empty($post['profile_photo'])): ?>
      <img src="<?= htmlspecialchars($post['profile_photo']) ?>" alt="Profile" class="poster-img" />
    <?php else: ?>
      <div class="poster-avatar"><?= strtoupper(substr($post['full_name'], 0, 1)) ?></div>
    <?php endif; ?>
    <div class="poster-info">
      <div class="poster-name"><?= htmlspecialchars($post['full_name']) ?></div>
      <div class="poster-meta">
        <?php if (!empty($post['student_id'])): ?>
          <span><i class="fa-solid fa-id-card"></i> <?= htmlspecialchars($post['student_id']) ?></span>
        <?php endif; ?>
        <span><i class="fa-solid fa-calendar"></i> Member since <?= date('M Y', strtotime($post['member_since'])) ?></span>
      </div>
    </div>
  </div>

  <!-- COMMENTS SECTION -->
  <div class="comments-section" id="commentBox">
    <div class="comments-header">
      <i class="fa-regular fa-comments"></i>
      <h2>Comments <span>(<?= $comment_count ?>)</span></h2>
    </div>

    <!-- Comment Form -->
    <?php if (isset($_SESSION['user_id'])): ?>
      <form action="item.php?id=<?= $post_id ?>" method="post" class="comment-form">
        <div class="comment-input-row">
          <?php if (!empty($_SESSION['user_photo'])): ?>
            <img src="<?= htmlspecialchars($_SESSION['user_photo']) ?>" alt="You" class="comment-avatar-img" />
          <?php else: ?>
            <div class="comment-avatar"><?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?></div>
          <?php endif; ?>
          <textarea name="comment" placeholder="Write a comment..." required></textarea>
        </div>
        <div class="comment-submit-row">
          <button type="submit" class="btn btn-contact"><i class="fa-solid fa-paper-plane"></i> Post Comment</button>
        </div>
      </form>
    <?php else: ?>
      <div class="login-to-comment">
        <i class="fa-regular fa-comment"></i>
        <p><a href="login.html">Login</a> to leave a comment.</p>
      </div>
    <?php endif; ?>

    <!-- Comments List -->
    <div class="comments-list">
      <?php if ($comment_count > 0): ?>
        <?php while ($comment = mysqli_fetch_assoc($comments_result)): ?>
          <div class="comment-item">
            <?php if (!empty($comment['profile_photo'])): ?>
              <img src="<?= htmlspecialchars($comment['profile_photo']) ?>" alt="Profile" class="comment-avatar-img" />
            <?php else: ?>
              <div class="comment-avatar"><?= strtoupper(substr($comment['full_name'], 0, 1)) ?></div>
            <?php endif; ?>
            <div class="comment-body">
              <div class="comment-meta">
                <span class="comment-name"><?= htmlspecialchars($comment['full_name']) ?></span>
                <span class="comment-time"><?= date('M j, Y · g:i A', strtotime($comment['created_at'])) ?></span>
              </div>
              <p class="comment-text"><?= htmlspecialchars($comment['comment']) ?></p>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="no-comments">
          <i class="fa-regular fa-comment"></i>
          <p>No comments yet. Be the first to comment!</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

</main>

<footer style="text-align:center; padding:20px; font-size:0.8rem; color:#A8A29E; border-top:1px solid #E7E5E4; background:#fff;">
  Made with ❤️ for <strong style="color:#F97316;">UIU</strong> students &nbsp;·&nbsp; © 2026 UIU Lost &amp; Found
</footer>

<script>
  function toggleDropdown(e) {
    e.stopPropagation();
    document.getElementById('userDropdown').classList.toggle('open');
  }
  document.addEventListener('click', () => {
    const d = document.getElementById('userDropdown');
    if (d) d.classList.remove('open');
  });
  function toggleMenu() {
    document.getElementById('mobileMenu').classList.toggle('open');
  }
</script>

</body>
</html>