<?php
// =========================================================================
// DYNAMIC PATHING LOGIC
// This checks if the user is inside the "Pages" folder or the main folder.
// It automatically adjusts the links so the navbar works perfectly everywhere!
// =========================================================================
$is_pages_dir = (basename(getcwd()) === 'Pages');
$root_dir = $is_pages_dir ? '../' : '';
$pages_dir = $is_pages_dir ? '' : 'Pages/';
?>

<link rel="stylesheet" href="<?php echo $root_dir; ?>Css/navbar.css">

<header>
    <a href="<?php echo $root_dir; ?>index.php" class="logo">Route<span>Link</span></a>
    
    <nav>


        <?php if (isset($_SESSION['u_id'])): ?>
            
            <a href="<?php echo $pages_dir; ?>booking1page.php" class="btn" style="background: var(--primary); color: white;">Book Now</a>
            <a href="<?php echo $pages_dir; ?>logout.php" class="btn logout">Logout</a>
            
        <?php else: ?>
            
            <a href="<?php echo $pages_dir; ?>logging_page.php" class="btn">Login</a>
            <a href="<?php echo $pages_dir; ?>register_page.php" class="btn" style="background: var(--primary); color: white; border: none;">Sign Up</a>
            
        <?php endif; ?>
    </nav>
</header>