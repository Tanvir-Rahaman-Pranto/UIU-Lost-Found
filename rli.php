<?php session_start(); include 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Lost Item - UIU Lost & Found</title>
    <link rel="stylesheet" href="rli.css">
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

    <?php if (isset($_SESSION['user_id'])): ?>
      <div class="profile-dropdown">
        <div class="profile-btn" onclick="toggleDropdown()">
          <?php if (!empty($_SESSION['user_photo'])): ?>
            <img src="<?= htmlspecialchars($_SESSION['user_photo']) ?>" alt="Profile" class="profile-img" />
          <?php else: ?>
            <div class="profile-avatar"><?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?></div>
          <?php endif; ?>
          <span class="profile-name"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
          <i class="fa-solid fa-chevron-down fa-xs"></i>
        </div>
        <div class="dropdown-menu" id="dropdownMenu">
          <a href="profile.php"><i class="fa-solid fa-user"></i> My Profile</a>
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
    <a href="profile.php"><i class="fa-solid fa-user"></i> My Profile</a>
    <a href="my-posts.php"><i class="fa-solid fa-list"></i> My Posts</a>
    <a href="php/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
  <?php else: ?>
    <a href="login.html"><i class="fa-solid fa-user"></i> Login</a>
    <a href="register.html" class="btn-nav"><i class="fa-solid fa-user-plus"></i> Register</a>
  <?php endif; ?>
</div>

<main>
  <div class="card">
    <div class="card-header">
      <h1>📢 Report a Lost Item</h1>
      <p>Fill in the details below. The more specific you are, the easier it is for someone to identify and return your item.</p>
    </div>

    <div class="card-body">
      <form action="php/post_lost.php" method="post" enctype="multipart/form-data">

        <div class="form-section-title">Item Details</div>

        <div class="form-group">
          <label>Item Name <span class="required">*</span></label>
          <input type="text" name="item_name" placeholder="e.g. Samsung Galaxy A54, Blue Backpack, Student ID…" required />
        </div>

        <div class="form-group">
          <label>Category <span class="required">*</span></label>
          <select name="category" required>
            <option value="" disabled selected>Select a category</option>
            <option>📱 Electronics</option>
            <option>🪪 ID / Cards</option>
            <option>🎒 Bags &amp; Accessories</option>
            <option>📚 Books &amp; Notes</option>
            <option>👕 Clothing</option>
            <option>🔑 Keys</option>
            <option>💳 Wallet / Money</option>
            <option>👓 Eyewear</option>
            <option>🖊️ Stationery</option>
            <option>📦 Other</option>
          </select>
        </div>

        <div class="form-group">
          <label>Description <span class="required">*</span></label>
          <textarea name="description" placeholder="Describe the item — color, brand, model, size, any unique marks or stickers…" required></textarea>
          <p class="hint">Be as detailed as possible to help identify the item.</p>
        </div>

        <div class="form-group">
          <label>Photo (optional)</label>
          <div class="upload-box">
            <input type="file" name="photo" accept="image/*" />
            <span class="upload-icon">📷</span>
            <p>Click to upload a photo of the item</p>
            <span>JPG, PNG – max 5MB</span>
          </div>
        </div>

        <hr class="divider" />
        <div class="form-section-title">Where &amp; When</div>

        <div class="form-group">
          <label>Last Seen Location <span class="required">*</span></label>
          <select name="location" required>
            <option value="" disabled selected>Select a location</option>
            <option>Library</option>
            <option>Cafeteria</option>
            <option>Classroom</option>
            <option>Lab</option>
            <option>Prayer Room</option>
            <option>Parking Lot</option>
            <option>ATM Booth</option>
            <option>Main Gate / Reception</option>
            <option>Field</option>
            <option>Other</option>
          </select>
        </div>

        <div class="form-group">
          <label>Specific Spot</label>
          <input type="text" name="specific_spot" placeholder="e.g. 4th floor near room 412, front row seat…" />
          <p class="hint">Optional but helpful.</p>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Date Lost <span class="required">*</span></label>
            <input type="date" name="date_lost" required />
          </div>
          <div class="form-group">
            <label>Approximate Time</label>
            <input type="time" name="time_lost" />
          </div>
        </div>

        <hr class="divider" />
        <div class="form-section-title">Your Contact Info</div>

        <div class="form-group">
          <label>Full Name <span class="required">*</span></label>
          <input type="text" name="full_name" placeholder="Enter your full name" required />
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Phone Number <span class="required">*</span></label>
            <input type="tel" name="phone" placeholder="01XXXXXXXXX" required />
          </div>
          <div class="form-group">
            <label>Student ID (optional)</label>
            <input type="text" name="student_id" placeholder="e.g. 011231456" />
          </div>
        </div>

        <div class="form-group">
          <label>Email (optional)</label>
          <input type="email" name="email" placeholder="your@email.com" />
          <p class="hint">We'll notify you if someone reports your item found.</p>
        </div>

        <button type="submit" class="submit-btn">📢 Submit Lost Item Report</button>
        <a href="index.php" class="cancel-link">Cancel and go back</a>

      </form>
    </div>
  </div>
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