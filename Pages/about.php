<?php
// MUST BE LINE 1: Start the session so your navbar knows if someone is logged in!
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us — RouteLink</title>
    
    <link rel="stylesheet" href="../Css/footer.css">
    <link rel="stylesheet" href="../Css/about.css">
    <link rel="stylesheet" href="../Css/navbar.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,700;1,600&display=swap" rel="stylesheet">

    <style>
        /* ========== ABOUT PAGE ========== */
        :root {
            --sky: #38bdf8;
            --sky-dim: rgba(56, 189, 248, 0.12);
            --sky-border: rgba(56, 189, 248, 0.25);
            --dark: #0b1120;
            --dark-2: #0f172a;
            --text: #1e293b;
            --muted: #64748b;
            --light-bg: #f8fafc;
            --white: #ffffff;
            --border: #e2e8f0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Sora', sans-serif;
            background: var(--light-bg);
            color: var(--text);
            /* Added to ensure footer pushes to bottom if content is short */
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        main {
            flex: 1; /* Allows main content to grow and push footer down */
        }

        /* ---- PAGE HERO ---- */
        .about-hero {
            background: linear-gradient(135deg, var(--dark) 0%, #0f2040 60%, #0b1120 100%);
            padding: 100px 60px 120px;
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .about-hero::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0; right: 0;
            height: 70px;
            background: var(--light-bg);
            clip-path: ellipse(55% 100% at 50% 100%);
        }

        /* decorative orbs */
        .about-hero::before {
            content: '';
            position: absolute;
            top: -120px; right: -120px;
            width: 420px; height: 420px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(56,189,248,0.12) 0%, transparent 70%);
            pointer-events: none;
        }

        .orb-left {
            position: absolute;
            bottom: -80px; left: -100px;
            width: 340px; height: 340px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(99,102,241,0.1) 0%, transparent 70%);
            pointer-events: none;
        }

        .about-hero-badge {
            display: inline-block;
            background: var(--sky-dim);
            border: 1px solid var(--sky-border);
            color: var(--sky);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 6px 18px;
            border-radius: 50px;
            margin-bottom: 28px;
        }

        .about-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(38px, 6vw, 68px);
            font-weight: 700;
            color: var(--white);
            line-height: 1.12;
            letter-spacing: -1px;
            margin-bottom: 20px;
        }

        .about-hero h1 em {
            font-style: italic;
            color: var(--sky);
        }

        .about-hero p {
            font-size: 16px;
            color: #94a3b8;
            max-width: 520px;
            margin: 0 auto;
            line-height: 1.8;
        }

        /* ---- MISSION STRIP ---- */
        .mission-strip {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            padding: 50px 60px;
            display: flex;
            align-items: center;
            gap: 60px;
            max-width: 1100px;
            margin: 0 auto;
        }

        .mission-strip .tag {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--sky);
            margin-bottom: 12px;
        }

        .mission-strip h2 {
            font-family: 'Playfair Display', serif;
            font-size: 30px;
            color: var(--dark-2);
            line-height: 1.3;
            min-width: 300px;
        }

        .mission-strip p {
            font-size: 15px;
            color: var(--muted);
            line-height: 1.85;
        }

        /* ---- VALUES ---- */
        .values-section {
            padding: 90px 60px;
            background: var(--light-bg);
        }

        .section-header {
            text-align: center;
            margin-bottom: 56px;
        }

        .section-header .tag {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--sky);
            display: block;
            margin-bottom: 10px;
        }

        .section-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            color: var(--dark-2);
            letter-spacing: -0.5px;
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
            max-width: 1050px;
            margin: 0 auto;
        }

        .value-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 36px 28px;
            position: relative;
            overflow: hidden;
            transition: transform 0.25s, box-shadow 0.25s;
        }

        .value-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 16px 40px rgba(0,0,0,0.07);
        }

        .value-card .number {
            position: absolute;
            top: 20px; right: 24px;
            font-size: 52px;
            font-weight: 800;
            color: #f1f5f9;
            line-height: 1;
            font-family: 'Playfair Display', serif;
            user-select: none;
        }

        .value-card .icon {
            font-size: 30px;
            margin-bottom: 18px;
        }

        .value-card h3 {
            font-size: 17px;
            font-weight: 700;
            color: var(--dark-2);
            margin-bottom: 10px;
        }

        .value-card p {
            font-size: 14px;
            color: var(--muted);
            line-height: 1.75;
        }

        .value-card .accent-line {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--sky), #818cf8);
            opacity: 0;
            transition: opacity 0.25s;
        }

        .value-card:hover .accent-line {
            opacity: 1;
        }

        /* ---- TEAM SECTION ---- */
        .team-section {
            background: var(--dark);
            padding: 90px 60px;
            text-align: center;
        }

        .team-section .section-header h2 {
            color: var(--white);
        }

        .team-section .section-header .tag {
            color: var(--sky);
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            max-width: 900px;
            margin: 0 auto;
        }

        .team-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px;
            padding: 36px 24px;
            transition: background 0.25s, border-color 0.25s;
        }

        .team-card:hover {
            background: rgba(56,189,248,0.07);
            border-color: var(--sky-border);
        }

        .team-avatar {
            width: 72px; height: 72px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--sky) 0%, #818cf8 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin: 0 auto 18px;
            border: 3px solid rgba(255,255,255,0.1);
        }

        .team-card h3 {
            font-size: 16px;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 5px;
        }

        .team-card span {
            font-size: 12px;
            color: var(--sky);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .team-card p {
            font-size: 13.5px;
            color: #64748b;
            line-height: 1.7;
            margin-top: 12px;
        }

        /* ---- HOW IT WORKS ---- */
        .how-section {
            padding: 90px 60px;
            background: var(--white);
        }

        .steps-row {
            display: flex;
            gap: 0;
            max-width: 900px;
            margin: 0 auto;
            position: relative;
        }

        .steps-row::before {
            content: '';
            position: absolute;
            top: 32px;
            left: calc(16.6% + 16px);
            right: calc(16.6% + 16px);
            height: 2px;
            background: linear-gradient(90deg, var(--sky), #818cf8);
            z-index: 0;
        }

        .step {
            flex: 1;
            text-align: center;
            padding: 0 16px;
            position: relative;
            z-index: 1;
        }

        .step-num {
            width: 64px; height: 64px;
            border-radius: 50%;
            background: var(--dark);
            color: var(--sky);
            font-size: 20px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            border: 3px solid var(--sky-border);
            font-family: 'Playfair Display', serif;
        }

        .step h3 {
            font-size: 15px;
            font-weight: 700;
            color: var(--dark-2);
            margin-bottom: 8px;
        }

        .step p {
            font-size: 13px;
            color: var(--muted);
            line-height: 1.7;
        }

        /* ---- CTA ---- */
        .about-cta {
            background: linear-gradient(135deg, #0b1120 0%, #0f2040 100%);
            padding: 80px 60px;
            text-align: center;
        }

        .about-cta h2 {
            font-family: 'Playfair Display', serif;
            font-size: 38px;
            color: var(--white);
            margin-bottom: 14px;
            letter-spacing: -0.5px;
        }

        .about-cta p {
            color: #64748b;
            font-size: 15px;
            margin-bottom: 32px;
        }

        .btn-primary {
            background: var(--sky);
            color: var(--dark);
            padding: 14px 32px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            display: inline-block;
            transition: background 0.2s, transform 0.15s;
        }

        .btn-primary:hover {
            background: #7dd3fc;
            transform: translateY(-2px);
        }

        /* ---- RESPONSIVE ---- */
        @media (max-width: 900px) {
            .values-grid, .team-grid { grid-template-columns: 1fr 1fr; }
            .steps-row { flex-direction: column; gap: 32px; }
            .steps-row::before { display: none; }
            .mission-strip { flex-direction: column; gap: 20px; }
        }

        @media (max-width: 600px) {
            .about-hero, .values-section, .team-section, .how-section, .about-cta { padding: 60px 24px; }
            .values-grid, .team-grid { grid-template-columns: 1fr; }
            .mission-strip { padding: 40px 24px; }
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main>
        <section class="about-hero">
            <div class="orb-left"></div>
            <span class="about-hero-badge">✦ Our Story</span>
            <h1>We Make Travel<br><em>Effortless</em></h1>
            <p>RouteLink was built on a simple idea — that reserving a bus seat should take seconds, not stress. Here's how we got here.</p>
        </section>

        <div style="background:#fff; border-bottom:1px solid #e2e8f0;">
            <div class="mission-strip">
                <div>
                    <div class="tag">Our Mission</div>
                    <h2>Connecting People<br>to Their Destinations</h2>
                </div>
                <p>We set out to eliminate the friction from bus travel — long queues, uncertain seat availability, cash-only counters. RouteLink digitises every step of the journey, from searching routes to printing your e-ticket, so passengers spend their energy on what matters: the trip itself.</p>
            </div>
        </div>

        <section class="values-section">
            <div class="section-header">
                <span class="tag">What We Stand For</span>
                <h2>Our Core Values</h2>
            </div>
            <div class="values-grid">
                <div class="value-card">
                    <span class="number">01</span>
                    <div class="icon">⚡</div>
                    <h3>Speed & Simplicity</h3>
                    <p>From search to booking confirmation in under 60 seconds. We respect your time above everything else.</p>
                    <div class="accent-line"></div>
                </div>
                <div class="value-card">
                    <span class="number">02</span>
                    <div class="icon">🔒</div>
                    <h3>Security First</h3>
                    <p>All personal data is encrypted end-to-end. We never sell your information or compromise your privacy.</p>
                    <div class="accent-line"></div>
                </div>
                <div class="value-card">
                    <span class="number">03</span>
                    <div class="icon">🤝</div>
                    <h3>Reliability</h3>
                    <p>99.9% uptime, accurate schedules, real-time seat availability — you can count on us every single day.</p>
                    <div class="accent-line"></div>
                </div>
                <div class="value-card">
                    <span class="number">04</span>
                    <div class="icon">🌍</div>
                    <h3>Accessibility</h3>
                    <p>Designed to work on any device, any connection speed. No one should be left behind because of technology.</p>
                    <div class="accent-line"></div>
                </div>
                <div class="value-card">
                    <span class="number">05</span>
                    <div class="icon">🎧</div>
                    <h3>Human Support</h3>
                    <p>Real people, not bots. Our support team is available around the clock to resolve any issue you face.</p>
                    <div class="accent-line"></div>
                </div>
                <div class="value-card">
                    <span class="number">06</span>
                    <div class="icon">🚀</div>
                    <h3>Constant Growth</h3>
                    <p>We ship improvements every week based on passenger feedback. Your suggestions literally shape the product.</p>
                    <div class="accent-line"></div>
                </div>
            </div>
        </section>

        <section class="how-section">
            <div class="section-header">
                <span class="tag">The Process</span>
                <h2>How RouteLink Works</h2>
            </div>
            <div class="steps-row">
                <div class="step">
                    <div class="step-num">1</div>
                    <h3>Search a Route</h3>
                    <p>Enter your origin, destination, and travel date to see all available buses.</p>
                </div>
                <div class="step">
                    <div class="step-num">2</div>
                    <h3>Pick Your Seat</h3>
                    <p>View the live seat map and choose exactly where you want to sit.</p>
                </div>
                <div class="step">
                    <div class="step-num">3</div>
                    <h3>Pay Securely</h3>
                    <p>Complete your booking in seconds with our encrypted checkout.</p>
                </div>
                <div class="step">
                    <div class="step-num">4</div>
                    <h3>Travel & Enjoy</h3>
                    <p>Show your e-ticket at the door and relax — we've handled the rest.</p>
                </div>
            </div>
        </section>

        <section class="team-section">
            <div class="section-header">
                <span class="tag">The People</span>
                <h2>Meet Our Team</h2>
            </div>
            <div class="team-grid">
                <div class="team-card">
                    <div class="team-avatar">👨‍💼</div>
                    <h3>AAAAAAA</h3>
                    <span>Founder & CEO</span>
                    <p>Passionate about solving real-world transport problems with elegant digital solutions.</p>
                </div>
                <div class="team-card">
                    <div class="team-avatar">👩‍💻</div>
                    <h3>BBBBBBB</h3>
                    <span>Lead Developer</span>
                    <p>Architect of the booking engine — obsessed with performance and clean code.</p>
                </div>
                <div class="team-card">
                    <div class="team-avatar">👨‍🎨</div>
                    <h3>CCCCCC</h3>
                    <span>UX Designer</span>
                    <p>Every pixel on RouteLink has been carefully considered to make your experience seamless.</p>
                </div>
            </div>
        </section>

        <section class="about-cta">
            <h2>Ready to Start Your Journey?</h2>
            <p>Join thousands of travellers who book smarter with RouteLink.</p>
            <a href="register_page.php" class="btn-primary">Create Free Account</a>
        </section>

    </main>

    <?php include 'footer.php'; ?>

</body>
</html>