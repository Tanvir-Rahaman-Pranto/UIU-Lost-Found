<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$sql  = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

// Fetch user posts
$posts_sql    = "SELECT * FROM posts WHERE user_id = '$user_id' ORDER BY created_at DESC LIMIT 3";
$posts_result = mysqli_query($conn, $posts_sql);
$total_posts  = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM posts WHERE user_id = '$user_id'"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - UIU Lost & Found</title>
    <link rel="stylesheet" href="user.css">
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>

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

    <div class="user-menu">
      <?php if (!empty($user['profile_photo'])): ?>
        <img src="<?= htmlspecialchars($user['profile_photo']) ?>" alt="User" />
      <?php else: ?>
        <div class="nav-avatar"><?= strtoupper(substr($user['full_name'], 0, 1)) ?></div>
      <?php endif; ?>
      <h4><?= htmlspecialchars($user['full_name']) ?></h4>
      <i class="fa-solid fa-chevron-down" id="chevronIcon" onclick="toggleDropdown(event)"></i>
      <div class="user-dropdown" id="userDropdown">
        <a href="user.php"><i class="fa-solid fa-user"></i> My Profile</a>
        <a href="my-posts.php"><i class="fa-solid fa-list"></i> My Posts</a>
        <hr style="border:none;border-top:1px solid #eee;margin:4px 0;">
        <a href="php/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
      </div>
    </div>
  </div>

  <button class="hamburger" onclick="toggleMenu()" aria-label="Toggle menu">
    <span></span><span></span><span></span>
  </button>
</nav>

<div class="mobile-menu" id="mobileMenu">
  <a href="index.php"><i class="fa-solid fa-house"></i> Home</a>
  <a href="user.php"><i class="fa-solid fa-user"></i> My Profile</a>
  <a href="my-posts.php"><i class="fa-solid fa-list"></i> My Posts</a>
  <a href="php/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</div>

<!-- MAIN -->
<main class="main-wrapper">

  <!-- Left Section -->
  <section class="left-section">

    <!-- Profile Card -->
    <div class="profile-card">
      <div class="profile-image-box">
        <?php if (!empty($user['profile_photo'])): ?>
          <img src="<?= htmlspecialchars($user['profile_photo']) ?>" alt="Profile" id="profilePreview" />
        <?php else: ?>
          <div class="profile-avatar-large" id="profilePreview"><?= strtoupper(substr($user['full_name'], 0, 1)) ?></div>
        <?php endif; ?>

        <!-- Camera button triggers photo upload -->
        <form action="php/update_photo.php" method="post" enctype="multipart/form-data" id="photoForm">
          <input type="file" name="profile_photo" id="photoInput" accept="image/*" style="display:none;" onchange="document.getElementById('photoForm').submit()" />
        </form>
        <button class="camera-btn" onclick="document.getElementById('photoInput').click()">
          <i class="fa-solid fa-camera"></i>
        </button>
      </div>

      <div class="profile-info">
        <div class="profile-title-row">
          <div>
            <h1><?= htmlspecialchars($user['full_name']) ?></h1>
            <div class="member-badge">
              <i class="fa-solid fa-shield-heart"></i>
              Member since <?= date('M Y', strtotime($user['created_at'])) ?>
            </div>
          </div>
          <button class="edit-btn" onclick="openEditModal()">
            <i class="fa-regular fa-pen-to-square"></i>
            Edit Profile
          </button>
        </div>

        <div class="profile-details">
          <div class="contact-info">
            <div class="info-item">
              <i class="fa-solid fa-id-card"></i>
              <div>
                <h4>Student ID</h4>
                <p><?= !empty($user['student_id']) ? htmlspecialchars($user['student_id']) : 'Not set' ?></p>
              </div>
            </div>
            <div class="info-item">
              <i class="fa-regular fa-envelope"></i>
              <div>
                <h4>Email</h4>
                <p><?= htmlspecialchars($user['email']) ?></p>
              </div>
            </div>
            <div class="info-item">
              <i class="fa-solid fa-phone"></i>
              <div>
                <h4>Phone</h4>
                <p><?= !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'Not set' ?></p>
              </div>
            </div>
            <div class="info-item">
              <i class="fa-solid fa-location-dot"></i>
              <div>
                <h4>Location</h4>
                <p><?= !empty($user['location']) ? htmlspecialchars($user['location']) : 'Not set' ?></p>
              </div>
            </div>
          </div>

          <div class="bio-box">
            <div class="bio-heading">
              <i class="fa-regular fa-user"></i>
              <h4>Bio</h4>
            </div>
            <p><?= !empty($user['bio']) ? htmlspecialchars($user['bio']) : 'No bio added yet.' ?></p>
          </div>
        </div>
      </div>
    </div>

    <!-- My Posts -->
    <div class="posts-card">
      <div class="section-header">
        <div class="section-title">
          <i class="fa-regular fa-file-lines"></i>
          <h2>My Posts</h2>
        </div>
        <a href="my-posts.php">View All (<?= $total_posts ?>)</a>
      </div>

      <?php if (mysqli_num_rows($posts_result) > 0): ?>
        <?php while ($post = mysqli_fetch_assoc($posts_result)): ?>
          <div class="post-item" onclick="window.location.href='item.php?id=<?= $post['id'] ?>'">
            <?php if (!empty($post['photo_url'])): ?>
              <img src="<?= htmlspecialchars($post['photo_url']) ?>" alt="<?= htmlspecialchars($post['item_name']) ?>" />
            <?php else: ?>
              <div class="post-img-placeholder">📦</div>
            <?php endif; ?>

            <div class="post-content" onclick="window.location.href='item-detail.php?id=<?= $post['id'] ?>'">
              <h3><?= htmlspecialchars($post['item_name']) ?></h3>
              <p><?= htmlspecialchars(substr($post['description'], 0, 100)) ?>...</p>
              <div class="post-meta">
                <span><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($post['location'] ?? 'N/A') ?></span>
                <span class="dot">•</span>
                <span><?= date('M j, Y', strtotime($post['created_at'])) ?></span>
              </div>
            </div>

            <div class="post-right">
              <span class="status <?= $post['type'] ?>"><?= ucfirst($post['type']) ?></span>
              <div class="post-actions">
                <a href="item.php?id=<?= $post['id'] ?>" class="post-action-btn view-btn">
                  <i class="fa-regular fa-eye"></i>
                </a>
                <a href="php/delete_post.php?id=<?= $post['id'] ?>" class="post-action-btn delete-btn"
                   onclick="return confirm('Are you sure you want to delete this post? This cannot be undone.')">
                  <i class="fa-regular fa-trash-can"></i>
                </a>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="no-posts-msg">
          <p>You haven't posted anything yet.</p>
          <a href="rli.php">Report a lost item</a> or <a href="ifs.php">report a found item</a>.
        </div>
      <?php endif; ?>
    </div>

  </section>

  <!-- Right Sidebar -->
  <aside class="right-sidebar">
    <div class="sidebar-card">
      <div class="sidebar-title">
        <i class="fa-regular fa-clipboard"></i>
        <h2>Report / Found Item</h2>
      </div>

      <div class="side-action" onclick="window.location.href='rli.php'">
        <div class="side-icon orange"><i class="fa-solid fa-question"></i></div>
        <div>
          <h3>Report Lost Item</h3>
          <p>Report an item you have lost</p>
        </div>
        <i class="fa-solid fa-chevron-right arrow"></i>
      </div>

      <div class="side-action" onclick="window.location.href='ifs.php'">
        <div class="side-icon green"><i class="fa-solid fa-briefcase"></i></div>
        <div>
          <h3>Report Found Item</h3>
          <p>Report an item you have found</p>
        </div>
        <i class="fa-solid fa-chevron-right arrow"></i>
      </div>

      <div class="safety-box">
        <div class="safety-title">
          <i class="fa-solid fa-shield-heart"></i>
          <h3>Safety Tip</h3>
        </div>
        <p>Never share personal information. Meet in public places when retrieving items.</p>
      </div>
    </div>
  </aside>

</main>

<!-- EDIT PROFILE MODAL -->
<div class="modal-overlay" id="editModal" onclick="closeModalOutside(event)">
  <div class="modal">
    <button class="modal-close" onclick="closeEditModal()">✕</button>
    <h2><i class="fa-regular fa-pen-to-square"></i> Edit Profile</h2>

    <form action="php/update_profile.php" method="post">
      <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required />
      </div>
      <div class="form-group">
        <label>Student ID</label>
        <input type="text" name="student_id" value="<?= htmlspecialchars($user['student_id'] ?? '') ?>" placeholder="e.g. 011201234" />
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required />
      </div>
      <div class="form-group">
        <label>Phone</label>
        <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="01XXXXXXXXX" />
      </div>
      <div class="form-group">
        <label>Location</label>
        <input type="text" name="location" value="<?= htmlspecialchars($user['location'] ?? '') ?>" placeholder="e.g. Satarkul, Dhaka" />
      </div>
      <div class="form-group">
        <label>Bio</label>
        <textarea name="bio" placeholder="Tell something about yourself..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
      </div>
      <button type="submit" class="modal-submit">Save Changes</button>
    </form>
  </div>
</div>

<footer>
  Made with ❤️ for <strong>UIU</strong> students &nbsp;·&nbsp; © 2026 UIU Lost &amp; Found
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
  function openEditModal() {
    document.getElementById('editModal').classList.add('open');
  }
  function closeEditModal() {
    document.getElementById('editModal').classList.remove('open');
  }
  function closeModalOutside(e) {
    if (e.target === document.getElementById('editModal')) closeEditModal();
  }
</script>

</body>
</html>