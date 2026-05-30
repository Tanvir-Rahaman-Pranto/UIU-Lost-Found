<?php
session_start();
include 'db.php';

$keyword  = isset($_GET['q']) ? trim($_GET['q']) : '';
$type     = isset($_GET['type']) ? $_GET['type'] : 'all';
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$status   = isset($_GET['status']) ? $_GET['status'] : 'all';

$posts = [];
$total = 0;

if ($keyword !== '' || isset($_GET['q'])) {
    $kw = mysqli_real_escape_string($conn, $keyword);

    $where = "WHERE (
        posts.item_name LIKE '%$kw%' OR
        posts.description LIKE '%$kw%' OR
        posts.location LIKE '%$kw%' OR
        posts.category LIKE '%$kw%'
    )";

    if ($type !== 'all')     $where .= " AND posts.type = '"     . mysqli_real_escape_string($conn, $type)     . "'";
    if ($status !== 'all')   $where .= " AND posts.status = '"   . mysqli_real_escape_string($conn, $status)   . "'";
    if ($category !== 'all') $where .= " AND posts.category = '" . mysqli_real_escape_string($conn, $category) . "'";

    $sql = "SELECT posts.*, users.full_name,
            (SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.id) AS comment_count
            FROM posts
            JOIN users ON posts.user_id = users.id
            $where
            ORDER BY posts.created_at DESC";

    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
    $total = count($posts);
}

// Hardcoded categories — always show all options
$categories = [
    '📱 Electronics',
    '🪪 ID / Cards',
    '🎒 Bags & Accessories',
    '📚 Books & Notes',
    '👕 Clothing',
    '🔑 Keys',
    '💳 Wallet / Money',
    '👓 Eyewear',
    '🎧 Headphones / Earbuds',
    '🖊️ Stationery',
    '📦 Other'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Search – UIU Lost & Found</title>
  <link rel="icon" type="image/png" href="logo.png" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --orange: #F97316;
      --orange-dark: #EA6C0A;
      --orange-light: #FFF7ED;
      --text-dark: #1C1917;
      --text-mid: #57534E;
      --text-light: #A8A29E;
      --bg: #F5F5F4;
      --border: #E7E5E4;
      --card: #ffffff;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: var(--bg);
      color: var(--text-dark);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* ── NAV ── */
    nav {
      background: #fff;
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 40px;
      height: 64px;
      box-shadow: 0 1px 8px rgba(0,0,0,0.06);
      position: sticky;
      top: 0;
      z-index: 100;
    }
    .nav-logo {
      display: flex; align-items: center; gap: 10px;
      font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 1.15rem;
      color: var(--orange); text-decoration: none; flex-shrink: 0;
    }
    .nav-logo img { width: 42px; height: 42px; object-fit: contain; }
    .nav-logo span { color: var(--text-dark); }
    .nav-links { display: flex; gap: 8px; align-items: center; }
    .nav-links a {
      text-decoration: none; font-size: 0.9rem; font-weight: 500;
      color: var(--text-mid); padding: 7px 14px; border-radius: 8px;
      transition: background 0.15s, color 0.15s; white-space: nowrap;
    }
    .nav-links a:hover { background: var(--orange-light); color: var(--orange); }
    .btn-nav { background: var(--orange) !important; color: #fff !important; font-weight: 600 !important; border-radius: 8px; }
    .btn-nav:hover { background: var(--orange-dark) !important; }
    .user-menu {
      display: flex; align-items: center; gap: 10px; position: relative;
      cursor: pointer; padding: 6px 12px; border-radius: 999px; transition: background 0.15s;
    }
    .user-menu:hover { background: var(--orange-light); }
    .user-menu img { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 2px solid var(--orange); }
    .nav-avatar {
      width: 36px; height: 36px; border-radius: 50%;
      background: linear-gradient(135deg, var(--orange), #FDBA74);
      color: #fff; font-weight: 800; font-size: 1rem;
      display: flex; align-items: center; justify-content: center;
    }
    .user-menu h4 { font-size: 0.9rem; font-weight: 600; color: var(--text-dark); }
    .user-menu > i { font-size: 0.75rem; color: var(--text-mid); }
    .user-dropdown {
      display: none; position: absolute; top: calc(100% + 8px); right: 0;
      background: #fff; border: 1px solid var(--border); border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.1); z-index: 999; min-width: 180px; overflow: hidden;
    }
    .user-dropdown.open { display: block; }
    .user-dropdown a {
      display: flex; align-items: center; gap: 10px; padding: 11px 16px;
      font-size: 0.88rem; font-weight: 500; color: var(--text-mid);
      text-decoration: none; transition: background 0.15s;
    }
    .user-dropdown a:hover { background: var(--orange-light); color: var(--orange); }
    .user-dropdown a:last-child:hover { color: #DC2626; background: #FEF2F2; }
    .hamburger { display: none; flex-direction: column; gap: 5px; cursor: pointer; padding: 6px; border: none; background: none; }
    .hamburger span { display: block; width: 24px; height: 2.5px; background: var(--text-dark); border-radius: 4px; }
    .mobile-menu {
      display: none; flex-direction: column; background: #fff;
      border-bottom: 1px solid var(--border); padding: 12px 24px 16px; gap: 4px;
      position: sticky; top: 64px; z-index: 99;
    }
    .mobile-menu a {
      text-decoration: none; font-size: 0.95rem; font-weight: 500;
      color: var(--text-mid); padding: 10px 14px; border-radius: 8px;
      transition: background 0.15s, color 0.15s;
    }
    .mobile-menu a:hover { background: var(--orange-light); color: var(--orange); }
    .mobile-menu .btn-nav {
      background: var(--orange) !important; color: #fff !important;
      text-align: center; font-weight: 600 !important; margin-top: 4px;
    }
    .mobile-menu.open { display: flex; }

    /* ── MAIN ── */
    main {
      flex: 1;
      max-width: 900px;
      width: 100%;
      margin: 40px auto;
      padding: 0 24px 60px;
    }

    /* Search hero */
    .search-hero {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 18px;
      padding: 32px;
      margin-bottom: 24px;
      box-shadow: 0 4px 24px rgba(0,0,0,0.06);
    }
    .search-hero h1 {
      font-family: 'Poppins', sans-serif;
      font-weight: 800;
      font-size: 1.5rem;
      color: var(--text-dark);
      margin-bottom: 6px;
    }
    .search-hero h1 span { color: var(--orange); }
    .search-hero p { font-size: 0.88rem; color: var(--text-light); margin-bottom: 20px; }

    .search-bar {
      display: flex;
      gap: 10px;
      margin-bottom: 16px;
    }
    .search-bar input {
      flex: 1;
      padding: 13px 18px;
      border: 1.5px solid var(--border);
      border-radius: 10px;
      font-size: 0.95rem;
      font-family: 'Inter', sans-serif;
      color: var(--text-dark);
      outline: none;
      transition: border-color 0.15s;
    }
    .search-bar input:focus { border-color: var(--orange); box-shadow: 0 0 0 3px rgba(249,115,22,0.1); }
    .search-bar input::placeholder { color: var(--text-light); }
    .search-bar button {
      background: var(--orange);
      color: #fff;
      border: none;
      border-radius: 10px;
      padding: 13px 24px;
      font-family: 'Poppins', sans-serif;
      font-weight: 600;
      font-size: 0.9rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: background 0.15s;
      white-space: nowrap;
    }
    .search-bar button:hover { background: var(--orange-dark); }

    /* Filter row */
    .filter-row {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      align-items: center;
    }
    .filter-select {
      padding: 8px 14px;
      border: 1.5px solid var(--border);
      border-radius: 8px;
      font-size: 0.83rem;
      font-family: 'Inter', sans-serif;
      color: var(--text-mid);
      background: var(--bg);
      outline: none;
      cursor: pointer;
      transition: border-color 0.15s;
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2357534E' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 10px center;
      padding-right: 28px;
    }
    .filter-select:focus { border-color: var(--orange); }

    /* Results header */
    .results-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 16px;
      flex-wrap: wrap;
      gap: 8px;
    }
    .results-count { font-size: 0.85rem; color: var(--text-light); }
    .results-count strong { color: var(--text-dark); }

    /* Post cards */
    .posts-grid { display: flex; flex-direction: column; gap: 16px; }

    .post-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 16px;
      display: grid;
      grid-template-columns: 120px 1fr;
      overflow: hidden;
      box-shadow: 0 2px 12px rgba(0,0,0,0.05);
      text-decoration: none;
      color: inherit;
      transition: box-shadow 0.2s, transform 0.2s;
    }
    .post-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,0.1); transform: translateY(-1px); }

    .post-card-img {
      width: 120px; height: 120px;
      object-fit: cover;
      background: #F0EDE9;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2.5rem;
      flex-shrink: 0;
    }
    .post-card-img img { width: 100%; height: 100%; object-fit: cover; display: block; }

    .post-card-body {
      padding: 16px 20px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      gap: 6px;
      min-width: 0;
    }
    .post-card-top { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

    .type-badge {
      display: inline-flex; align-items: center; gap: 5px;
      font-size: 0.72rem; font-weight: 700; letter-spacing: 0.4px;
      text-transform: uppercase; padding: 3px 10px; border-radius: 999px;
    }
    .type-lost { background: #FEE2E2; color: #DC2626; border: 1.5px solid #FECACA; }
    .type-found { background: #DCFCE7; color: #16A34A; border: 1.5px solid #BBF7D0; }
    .status-badge {
      font-size: 0.72rem; font-weight: 700; padding: 3px 10px;
      border-radius: 999px; display: inline-flex; align-items: center; gap: 4px;
    }
    .status-open { background: #FFF7ED; color: var(--orange); border: 1.5px solid #FED7AA; }
    .status-resolved { background: #DCFCE7; color: #16A34A; border: 1.5px solid #BBF7D0; }

    .post-card-title {
      font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 1rem;
      color: var(--text-dark); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .post-card-desc {
      font-size: 0.83rem; color: var(--text-mid);
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .post-card-meta { display: flex; gap: 12px; flex-wrap: wrap; font-size: 0.78rem; color: var(--text-light); }
    .post-card-meta span { display: flex; align-items: center; gap: 4px; }
    .post-card-meta i { color: var(--orange); font-size: 0.72rem; }

    /* Empty / initial state */
    .state-box {
      text-align: center;
      padding: 64px 24px;
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 18px;
      box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }
    .state-box .icon { font-size: 3rem; display: block; margin-bottom: 14px; }
    .state-box h3 {
      font-family: 'Poppins', sans-serif; font-weight: 700;
      font-size: 1.05rem; color: var(--text-dark); margin-bottom: 6px;
    }
    .state-box p { font-size: 0.85rem; color: var(--text-light); }

    mark { background: #FED7AA; color: var(--text-dark); border-radius: 3px; padding: 0 2px; }

    footer {
      text-align: center; padding: 20px; font-size: 0.8rem;
      color: var(--text-light); border-top: 1px solid var(--border); background: #fff;
    }

    @media (max-width: 768px) {
      nav { padding: 0 20px; }
      .nav-links { display: none !important; }
      .hamburger { display: flex; }
      .post-card { grid-template-columns: 90px 1fr; }
      .post-card-img { width: 90px; height: 90px; }
      .post-card-body { padding: 12px 14px; }
      .search-hero { padding: 20px; }
    }
    @media (max-width: 480px) {
      main { padding: 0 12px 40px; }
      .search-bar { flex-direction: column; }
      .search-bar button { width: 100%; justify-content: center; }
    }
  </style>
</head>
<body>

<!-- NAV -->
<nav>
  <a href="index.php" class="nav-logo">
    <img src="logo.png" alt="UIU Lost & Found Logo">
    UIU <span>Lost &amp; Found</span>
  </a>
  <div class="nav-links">
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

  <!-- Search Hero -->
  <div class="search-hero">
    <h1>Search <span>Lost &amp; Found</span></h1>
    <p>Search by item name, description, location or category.</p>

    <form method="GET" action="searchpage.php">
      <div class="search-bar">
        <input
          type="text"
          name="q"
          value="<?= htmlspecialchars($keyword) ?>"
          placeholder="e.g. black wallet, laptop, keys..."
          autofocus
        />
        <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
      </div>

      <div class="filter-row">
        <select name="type" class="filter-select">
          <option value="all"   <?= $type === 'all'   ? 'selected' : '' ?>>All Types</option>
          <option value="lost"  <?= $type === 'lost'  ? 'selected' : '' ?>>🔴 Lost</option>
          <option value="found" <?= $type === 'found' ? 'selected' : '' ?>>🟢 Found</option>
        </select>

        <select name="status" class="filter-select">
          <option value="all"      <?= $status === 'all'      ? 'selected' : '' ?>>All Status</option>
          <option value="open"     <?= $status === 'open'     ? 'selected' : '' ?>>Open</option>
          <option value="resolved" <?= $status === 'resolved' ? 'selected' : '' ?>>Resolved</option>
        </select>

        <select name="category" class="filter-select">
          <option value="all">All Categories</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
              <?= htmlspecialchars($cat) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </form>
  </div>

  <!-- Results -->
  <?php if (!isset($_GET['q'])): ?>
    <div class="state-box">
      <span class="icon">🔍</span>
      <h3>Search for an item</h3>
      <p>Type a keyword above to find lost or found items across the campus.</p>
    </div>

  <?php elseif ($total === 0): ?>
    <div class="state-box">
      <span class="icon">📭</span>
      <h3>No results for "<?= htmlspecialchars($keyword) ?>"</h3>
      <p>Try different keywords or remove some filters.</p>
    </div>

  <?php else: ?>
    <div class="results-header">
      <p class="results-count">
        Found <strong><?= $total ?></strong> result<?= $total !== 1 ? 's' : '' ?>
        <?= $keyword ? ' for <strong>"' . htmlspecialchars($keyword) . '"</strong>' : '' ?>
      </p>
    </div>

    <div class="posts-grid">
      <?php foreach ($posts as $post): ?>
        <a href="item.php?id=<?= $post['id'] ?>" class="post-card">

          <div class="post-card-img">
            <?php if (!empty($post['photo_url'])): ?>
              <img src="<?= htmlspecialchars($post['photo_url']) ?>" alt="<?= htmlspecialchars($post['item_name']) ?>" />
            <?php else: ?>
              📦
            <?php endif; ?>
          </div>

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
              <?php if (!empty($post['category'])): ?>
                <span><i class="fa-solid fa-tag"></i> <?= htmlspecialchars($post['category']) ?></span>
              <?php endif; ?>
              <span><i class="fa-solid fa-user"></i> <?= htmlspecialchars($post['full_name']) ?></span>
              <span><i class="fa-solid fa-calendar"></i> <?= date('M j, Y', strtotime($post['created_at'])) ?></span>
              <span><i class="fa-regular fa-comment"></i> <?= $post['comment_count'] ?></span>
            </div>
          </div>

        </a>
      <?php endforeach; ?>
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