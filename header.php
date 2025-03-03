<?php
// header.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<header>
  <div class="header-container">
    <h1 style="color: white;">
      <a href="/carrental/index.php" style="color: inherit; text-decoration: none;">Car Rental & Hire Hub</a>
    </h1>
    <nav>
      <ul>
        <li><a href="/carrental/index.php">Home</a></li>
        <li><a href="/carrental/search.php">Search</a></li>
        <li><a href="/carrental/pages/about.php">About Us</a></li>
        <li><a href="/carrental/pages/contact.php">Contact Us</a></li>
      </ul>
    </nav>
  </div>
</header>
