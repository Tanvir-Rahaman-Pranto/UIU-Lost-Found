<?php session_start(); include 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - UIU Lost & Found</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="stylesheet" href="homestyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>

<nav>
  <div class="nav-logo">
    <a href="index.php" class="nav-logo">
      <img src="logo.png" alt="UIU Lost & Found Logo">
      UIU <span>Lost &amp; Found</span>
    </a>
  </div>

  <div class="nav-links">
    <button type="button" onclick="window.location.href='searchpage.html'" class="search-btn">
      <i class="fa-solid fa-magnifying-glass"></i>
    </button>

    <a href="index.php"><i class="fa-solid fa-house"></i> Home</a>

    <?php if (isset($_SESSION['user_id'])): ?>
      <div class="profile-dropdown">
        <div class="profile-btn" onclick="toggleDropdown()">
          <?php if (!empty($_SESSION['user_photo'])): ?>
            <img src="<?= htmlspecialchars($_SESSION['user_photo']) ?>" alt="Profile" class="profile-img" />
          <?php else: ?>
            <div class="profile-avatar"><?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?></div>
          <?php endif; ?>
          <span class="profile-name"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
          <i class="fa-solid fa-chevron-down"></i>
        </div>
        <div class="dropdown-menu" id="dropdownMenu">
          <a href="user.php"><i class="fa-solid fa-user"></i> My Profile</a>
          <a href="my-posts.php"><i class="fa-solid fa-list"></i> My Posts</a>
          <hr>
          <a href="php/logout.php" class="logout-link"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
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

<main>
  <div class="badge">🎓 United International University</div>

  <h1>Lost something on<br><em>campus?</em> We can help.</h1>

  <p class="subtitle">
    Report a missing item or help reunite someone with what they left behind.
  </p>

  <div class="actions">
    <a href="rli.php" class="btn-primary">📢 Report Lost Item</a>
    <a href="ifs.php" class="btn-secondary">🔍 I Found Something</a>
  </div>

  <p class="steps-label">How it works</p>
  <div class="steps">
    <div class="step">
      <span class="step-num">1</span>
      <span class="step-icon">📝</span>
      <h3>Post a Listing</h3>
      <p>Report a lost or found item with a description and your contact number.</p>
    </div>
    <div class="step">
      <span class="step-num">2</span>
      <span class="step-icon">🔍</span>
      <h3>Browse &amp; Match</h3>
      <p>Search through posts to find your item or identify who an item belongs to.</p>
    </div>
    <div class="step">
      <span class="step-num">3</span>
      <span class="step-icon">🤝</span>
      <h3>Connect &amp; Return</h3>
      <p>Contact the poster directly and arrange a handover on campus.</p>
    </div>
  </div>

  <!-- POSTS -->
  <?php
  $sql    = "SELECT posts.*, users.full_name, users.profile_photo
             FROM posts
             JOIN users ON posts.user_id = users.id
             ORDER BY posts.created_at DESC";
  $result = mysqli_query($conn, $sql);
  $count  = mysqli_num_rows($result);
  ?>

  <?php if ($count > 0): ?>
    <div class="posts-header">
      <h2>Recent Posts</h2>
      <span><?= $count ?> item(s) listed</span>
    </div>
    <div class="posts-grid">
      <?php while ($post = mysqli_fetch_assoc($result)): ?>
        <div class="post-card">
          <?php if (!empty($post['photo_url'])): ?>
            <img src="<?= htmlspecialchars($post['photo_url']) ?>" alt="<?= htmlspecialchars($post['item_name']) ?>" class="card-img" />
          <?php else: ?>
            <div class="card-img-placeholder">📦</div>
          <?php endif; ?>
          <div class="card-body">
            <span class="card-type <?= $post['type'] === 'lost' ? 'type-lost' : 'type-found' ?>">
              <?= $post['type'] === 'lost' ? '🔴 Lost' : '🟢 Found' ?>
            </span>
            <div class="card-title"><?= htmlspecialchars($post['item_name']) ?></div>
            <div class="card-desc"><?= htmlspecialchars($post['description']) ?></div>
            <div class="card-meta">
              <span><i class="fa-solid fa-user"></i> <?= htmlspecialchars($post['full_name']) ?></span>
              <span><i class="fa-solid fa-calendar"></i> <?= date('M j, Y', strtotime($post['created_at'])) ?></span>
            </div>
            <a href="item-detail.php?id=<?= $post['id'] ?>" class="card-link">View Details →</a>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <div class="no-posts">
      <span>📭</span>
      <p>No posts yet. Be the first to report a lost or found item!</p>
    </div>
  <?php endif; ?>

</main>

<footer>
  Made with ❤️ for <strong>UIU</strong> students &nbsp;·&nbsp; © 2026 UIU Lost &amp; Found
</footer>

<script>
  function toggleMenu() {
    document.getElementById('mobileMenu').classList.toggle('open');
  }
  function toggleDropdown() {
    document.getElementById('dropdownMenu').classList.toggle('open');
  }
  document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('dropdownMenu');
    if (dropdown && !e.target.closest('.profile-dropdown')) {
      dropdown.classList.remove('open');
    }
  });
</script>

</body>
</html>
