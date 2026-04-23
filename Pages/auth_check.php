<?php
// ─────────────────────────────────────────
//  SeatBook — Session Guard
//  config/auth_check.php
//
//  Include this at the TOP of any page
//  that requires the user to be logged in:
//
//  require_once 'config/auth_check.php';
// ─────────────────────────────────────────

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['u_id'])) {
    header('Location: index.php?error=Please+log+in+to+continue.');
    exit;
}
?>
