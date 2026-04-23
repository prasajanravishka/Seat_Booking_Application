<?php
// =========================================================================
// DYNAMIC PATHING LOGIC
// This checks if the user is inside the "Pages" folder or the main folder.
// It automatically adjusts the links so the footer works perfectly everywhere!
// =========================================================================
$is_pages_dir = (basename(getcwd()) === 'Pages');
$root_dir = $is_pages_dir ? '../' : '';
$pages_dir = $is_pages_dir ? '' : 'Pages/';
?>

<link rel="stylesheet" href="<?php echo $root_dir; ?>Css/footer.css">

<footer>
    <div class="footer-grid">
        <div class="footer-brand">
            <a href="<?php echo $root_dir; ?>index.php" class="logo">Route<span>Link</span></a>
            <p>Book your bus tickets safely, quickly, and easily — all in one place.</p>
        </div>

        <div class="footer-col">
            <h4>Navigation</h4>
            <a href="<?php echo $root_dir; ?>index.php">Home</a>
            <a href="<?php echo $pages_dir; ?>about.php">About Us</a>
            <a href="<?php echo $pages_dir; ?>FAQ.php">Support / FAQ</a> 
            <a href="<?php echo $pages_dir; ?>contact-form.php">Contact</a>
        </div>

        <div class="footer-col">
            <h4>Account</h4>
            <a href="<?php echo $pages_dir; ?>logging_page.php">Login</a>
            <a href="<?php echo $pages_dir; ?>register_page.php">Sign Up</a>
        </div>
    </div>

    <div class="footer-bottom">
        <p>© <?php echo date("Y"); ?> RouteLink. All rights reserved.</p>
        <div>
            <a href="#">Privacy Policy</a> &nbsp;&middot;&nbsp;
            <a href="#">Terms of Service</a>
        </div>
    </div>
</footer>