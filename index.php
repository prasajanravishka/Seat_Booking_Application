<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<?php

// Connect to database
include 'DB/db_connect.php';
include("DB/create_users.php");
include("DB/create_buses.php");
include("DB/create_routes.php");
include("DB/create_schedules.php");
include("DB/create_seats.php");
include("DB/create_bookings.php");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyBooking — Home</title>
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="home.css">
    <link rel="stylesheet" href="footer.css">
</head>
<style>
/* ========== HOME PAGE ========== */
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&display=swap');

body {
    margin: 0;
    padding: 0;
    font-family: 'Sora', sans-serif;
    background: #f8fafc;
    color: #1e293b;
}

/* ----- HERO ----- */
.hero {
    min-height: 90vh;
    background:
        linear-gradient(135deg, rgba(11,17,32,0.85) 0%, rgba(11,17,32,0.6) 100%),
        url('https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=1600&q=80') center/cover no-repeat;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 60px 20px;
    position: relative;
    overflow: hidden;
}

.hero::before {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    right: 0;
    height: 80px;
    background: #f8fafc;
    clip-path: ellipse(55% 100% at 50% 100%);
}

.hero-badge {
    background: rgba(56, 189, 248, 0.15);
    border: 1px solid rgba(56, 189, 248, 0.35);
    color: #38bdf8;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    padding: 6px 16px;
    border-radius: 50px;
    margin-bottom: 24px;
    display: inline-block;
}

.hero h1 {
    font-size: clamp(36px, 6vw, 64px);
    font-weight: 800;
    color: #fff;
    line-height: 1.1;
    letter-spacing: -1.5px;
    margin-bottom: 20px;
    max-width: 720px;
}

.hero h1 span {
    color: #38bdf8;
}

.hero p {
    font-size: 17px;
    color: #94a3b8;
    max-width: 480px;
    line-height: 1.7;
    margin-bottom: 36px;
}

.hero-actions {
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
    justify-content: center;
}

.btn-primary {
    background: #38bdf8;
    color: #0b1120;
    padding: 14px 28px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 700;
    font-size: 15px;
    transition: background 0.2s, transform 0.15s;
}

.btn-primary:hover {
    background: #7dd3fc;
    transform: translateY(-2px);
}

.btn-secondary {
    background: transparent;
    color: #e2e8f0;
    padding: 14px 28px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 15px;
    border: 1px solid rgba(255,255,255,0.2);
    transition: border-color 0.2s, color 0.2s;
}

.btn-secondary:hover {
    border-color: #38bdf8;
    color: #38bdf8;
}

/* ----- STATS BAR ----- */
.stats-bar {
    background: #fff;
    border-bottom: 1px solid #e2e8f0;
    padding: 20px 60px;
    display: flex;
    justify-content: center;
    gap: 60px;
    flex-wrap: wrap;
}

.stat {
    text-align: center;
}

.stat strong {
    display: block;
    font-size: 24px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.5px;
}

.stat span {
    font-size: 13px;
    color: #64748b;
}

/* ----- FEATURES ----- */
.features {
    padding: 80px 60px;
    background: #f8fafc;
}

.features-header {
    text-align: center;
    margin-bottom: 50px;
}

.features-header h2 {
    font-size: 34px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.8px;
    margin-bottom: 10px;
}

.features-header p {
    color: #64748b;
    font-size: 15px;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    max-width: 1000px;
    margin: 0 auto;
}

.card {
    background: #fff;
    padding: 32px 28px;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    transition: box-shadow 0.25s, transform 0.2s;
    position: relative;
    overflow: hidden;
}

.card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #38bdf8, #818cf8);
    opacity: 0;
    transition: opacity 0.25s;
}

.card:hover {
    box-shadow: 0 12px 30px rgba(0,0,0,0.08);
    transform: translateY(-4px);
}

.card:hover::before {
    opacity: 1;
}

.card-icon {
    font-size: 32px;
    margin-bottom: 16px;
}

.card h3 {
    font-size: 17px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 10px;
    letter-spacing: -0.2px;
}

.card p {
    font-size: 14px;
    color: #64748b;
    line-height: 1.7;
}

/* ----- CTA SECTION ----- */
.cta-section {
    background: linear-gradient(135deg, #0b1120 0%, #0f2040 100%);
    padding: 80px 60px;
    text-align: center;
}

.cta-section h2 {
    font-size: 34px;
    font-weight: 800;
    color: #fff;
    letter-spacing: -0.8px;
    margin-bottom: 12px;
}

.cta-section p {
    color: #64748b;
    font-size: 15px;
    margin-bottom: 30px;
}

@media (max-width: 768px) {
    .features-grid {
        grid-template-columns: 1fr;
    }

    .features,
    .cta-section {
        padding: 60px 24px;
    }

    .stats-bar {
        gap: 30px;
        padding: 20px 24px;
    }
}
</style>
<body>

    <?php include 'Pages/navbar.php'; ?>

    <!-- HERO -->
    <section class="hero">
        <span class="hero-badge">✦ Online Bus Booking</span>
        <h1>Travel Smarter,<br>Book <span>Faster</span></h1>
        <p>Find, compare, and reserve your bus tickets in seconds. Safe, simple, and always on time.</p>
        <div class="hero-actions">
            <a href="Pages/logging_page.php" class="btn-primary">Browse Routes</a>
            <a href="#" class="btn-secondary">Learn More</a>
        </div>
    </section>

    <!-- STATS BAR -->
    <div class="stats-bar">
        <div class="stat">
            <strong>10,000+</strong>
            <span>Bookings Made</span>
        </div>
        <div class="stat">
            <strong>50+</strong>
            <span>Routes Available</span>
        </div>
        <div class="stat">
            <strong>99%</strong>
            <span>Customer Satisfaction</span>
        </div>
        <div class="stat">
            <strong>24/7</strong>
            <span>Live Support</span>
        </div>
    </div>

    <!-- FEATURES -->
    <section class="features">
        <div class="features-header">
            <h2>Why Choose MyBooking?</h2>
            <p>Everything you need for a hassle-free journey</p>
        </div>
        <div class="features-grid">
            <div class="card">
                <div class="card-icon">⚡</div>
                <h3>Easy Booking</h3>
                <p>Book your seat in just a few clicks — no queues, no hassle, anywhere and anytime.</p>
            </div>
            <div class="card">
                <div class="card-icon">🔒</div>
                <h3>Secure System</h3>
                <p>Your personal data and payments are fully encrypted and protected at every step.</p>
            </div>
            <div class="card">
                <div class="card-icon">🎧</div>
                <h3>24/7 Support</h3>
                <p>Our support team is always ready to assist you, day or night, for any issue.</p>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
        <h2>Ready to Book Your Journey?</h2>
        <p>Join thousands of happy travellers using MyBooking every day.</p>
        <a href="Pages/register_page.php" class="btn-primary">Get Started Free</a>
    </section>

    <?php include 'Pages/footer.php'; ?>

</body>
</html>
