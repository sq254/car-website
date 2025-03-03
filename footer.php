<?php
// footer.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<footer>
    <div class="footer-container">
        <nav>
            <a href="pages/contact.php">Contact Us</a> 
            <a href="pages/about.php">About Us</a> 
            <a href="https://facebook.com" target="_blank">Facebook</a> 
            <a href="https://instagram.com" target="_blank">Instagram</a> 
            <a href="https://x.com" target="_blank">X</a>
            <?php if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin'])): ?>
                <!-- If neither a client nor an admin is logged in, show Register, Login, and Administrator -->
                <p><a href="/carrental/register.php">Register</a>
                <a href="/carrental/login.php">Login</a></p>
                <p><a href="/carrental/admin/login.php">Administrator</a></p>
            <?php else: ?>
                <!-- If a user or admin is logged in, show Logout -->
                <p><a href="/carrental/logout.php" style="color:red">Logout</a></p>
            <?php endif; ?>
        </nav>
        <p>&copy; 2025 Car Rental and Hire. All rights reserved.</p>
    </div>
</footer>
