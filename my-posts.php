<?php
session_start();
include 'db.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build query
$where = "WHERE posts.user_id = '$user_id'";
if ($filter === 'lost') $where .= " AND posts.type = 'lost'";
if ($filter === 'found') $where .= " AND posts.type = 'found'";
if ($status_filter === 'open') $where .= " AND posts.status = 'open'";
if ($status_filter === 'resolved') $where .= " AND posts.status = 'resolved'";

$sql = "SELECT posts.*,
        (SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.id) AS comment_count
        FROM posts
        $where
        ORDER BY posts.created_at DESC";

$result = mysqli_query($conn, $sql);
$posts  = [];
while ($row = mysqli_fetch_assoc($result)) {
    $posts[] = $row;
}

$total = count($posts);

// Count stats
$stats_sql = "SELECT
    COUNT(*) AS total,
    SUM(type='lost') AS lost_count,
    SUM(type='found') AS found_count,
    SUM(status='open') AS open_count,
    SUM(status='resolved') AS resolved_count
    FROM posts WHERE user_id = '$user_id'";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $stats_sql));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Posts – UIU Lost & Found</title>
  <link rel="icon" type="image/png" href="logo.png" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="my-posts.css" />
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
    <?php endif; ?>
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
<main>

  <!-- Page Header -->
  <div class="page-header">
    <div>
      <h1 class="page-title">My <span>Posts</span></h1>
    </div>
   <div style="display:flex; gap:10px; flex-wrap:wrap;">
  <a href="rli.php" class="new-post-btn" style="background:#DC2626; box-shadow:0 4px 12px rgba(220,38,38,0.3);">
    <i class="fa-solid fa-circle-exclamation"></i> Report Lost
  </a>
  <a href="ifs.php" class="new-post-btn" style="background:#16A34A; box-shadow:0 4px 12px rgba(22,163,74,0.3);">
    <i class="fa-solid fa-circle-check"></i> Report Found
  </a>
</div>
  </div>

  <!-- Stats -->
  <div class="stats-row">
    <div class="stat-card">
      <div class="stat-number"><?= $stats['total'] ?? 0 ?></div>
      <div class="stat-label">Total Posts</div>
    </div>
    <div class="stat-card">
      <div class="stat-number"><?= $stats['lost_count'] ?? 0 ?></div>
      <div class="stat-label">Lost Items</div>
    </div>
    <div class="stat-card">
      <div class="stat-number"><?= $stats['found_count'] ?? 0 ?></div>
      <div class="stat-label">Found Items</div>
    </div>
    <div class="stat-card">
      <div class="stat-number"><?= $stats['resolved_count'] ?? 0 ?></div>
      <div class="stat-label">Resolved</div>
    </div>
  </div>

  <!-- Filters -->
  <div class="filters-bar">
    <div class="filter-group">
      <a href="?filter=all&status=<?= $status_filter ?>" class="filter-btn <?= $filter === 'all' ? 'active' : '' ?>">All</a>
      <a href="?filter=lost&status=<?= $status_filter ?>" class="filter-btn <?= $filter === 'lost' ? 'active' : '' ?>"><i class="fa-solid fa-circle-exclamation"></i> Lost</a>
      <a href="?filter=found&status=<?= $status_filter ?>" class="filter-btn <?= $filter === 'found' ? 'active' : '' ?>"><i class="fa-solid fa-circle-check"></i> Found</a>
    </div>
    <div class="filter-divider"></div>
    <div class="filter-group">
      <a href="?filter=<?= $filter ?>&status=all" class="filter-btn <?= $status_filter === 'all' ? 'active' : '' ?>">All Status</a>
      <a href="?filter=<?= $filter ?>&status=open" class="filter-btn <?= $status_filter === 'open' ? 'active' : '' ?>">Open</a>
      <a href="?filter=<?= $filter ?>&status=resolved" class="filter-btn <?= $status_filter === 'resolved' ? 'active' : '' ?>">Resolved</a>
    </div>
  </div>

  <p class="results-info">Showing <strong><?= $total ?></strong> post<?= $total !== 1 ? 's' : '' ?></p>

  <!-- Posts -->
  <?php if ($total > 0): ?>
    <div class="posts-grid">
      <?php foreach ($posts as $post): ?>
        <div class="post-card">

          <!-- Image -->
          <div class="post-card-img">
            <?php if (!empty($post['photo_url'])): ?>
              <img src="<?= htmlspecialchars($post['photo_url']) ?>" alt="<?= htmlspecialchars($post['item_name']) ?>" />
            <?php else: ?>
              📦
            <?php endif; ?>
          </div>

          <!-- Body -->
          <div class="post-card-body">
            <div class="post-card-top">
              <span class="type-badge <?= $post['type'] === 'lost' ? 'type-lost' : 'type-found' ?>">
                <i class="fa-solid <?= $post['type'] === 'lost' ? 'fa-circle-exclamation' : 'fa-circle-check' ?>"></i>
                <?= ucfirst($post['type']) ?>
              </span>
              <span class="status-badge <?= $post['status'] === 'open' ? 'status-open' : 'status-resolved' ?>">
                <?= $post['status'] === 'open' ? 'Open' : '✅ Resolved' ?>
              </span>
            </div>
            <div class="post-card-title"><?= htmlspecialchars($post['item_name']) ?></div>
            <?php if (!empty($post['description'])): ?>
              <div class="post-card-desc"><?= htmlspecialchars($post['description']) ?></div>
            <?php endif; ?>
            <div class="post-card-meta">
              <?php if (!empty($post['location'])): ?>
                <span><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($post['location']) ?></span>
              <?php endif; ?>
              <span><i class="fa-solid fa-calendar"></i> <?= date('M j, Y', strtotime($post['created_at'])) ?></span>
              <span><i class="fa-regular fa-comment"></i> <?= $post['comment_count'] ?> comment<?= $post['comment_count'] != 1 ? 's' : '' ?></span>
            </div>
          </div>

          <!-- Actions -->
          <div class="post-card-actions">
            <a href="item.php?id=<?= $post['id'] ?>" class="action-btn action-view">
              <i class="fa-solid fa-eye"></i> View
            </a>
            <?php if ($post['status'] === 'open'): ?>
              <a href="item.php?id=<?= $post['id'] ?>&resolve=1" class="action-btn action-resolve"
                 onclick="return confirm('Mark this post as resolved?')">
                <i class="fa-solid fa-circle-check"></i> Resolve
              </a>
            <?php endif; ?>
            <a href="php/delete_post.php?id=<?= $post['id'] ?>" class="action-btn action-delete"
               onclick="return confirm('Delete this post? This cannot be undone.')">
              <i class="fa-solid fa-trash"></i> Delete
            </a>
          </div>

        </div>
      <?php endforeach; ?>
    </div>

  <?php else: ?>
    <div class="empty-state">
      <span class="empty-icon">📭</span>
      <h3>No posts found</h3>
      <p>
        <?php if ($filter !== 'all' || $status_filter !== 'all'): ?>
          No posts match your current filters. Try adjusting them.
        <?php else: ?>
          You haven't posted anything yet. Start by reporting a lost or found item!
        <?php endif; ?>
      </p>
      <a href="post.php" class="new-post-btn"><i class="fa-solid fa-plus"></i> Create Your First Post</a>
    </div>
  <?php endif; ?>

</main>

<footer>
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