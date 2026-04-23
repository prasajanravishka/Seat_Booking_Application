<?php
session_start(); // Optional: Start session if your navbar depends on login status
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Center | Bus Booking</title>
    
    <link rel="stylesheet" href="../Css/navbar.css">
    <link rel="stylesheet" href="../Css/footer.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@400;600&family=Syne:wght@700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg: #08090e; 
            --surface: #0f1118; 
            --card: #13151f; 
            --border: #1e2130;
            --border-active: #3a6fff; 
            --primary: #3a6fff; 
            --primary-glow: rgba(58,111,255,0.25);
            --primary-light: #6690ff; 
            --text: #e8eaf2; 
            --muted: #6b7080; 
            --muted2: #3f4455;
        }

        body {
            min-height: 100vh;
            background: var(--bg);
            font-family: 'Mulish', sans-serif;
            color: var(--text);
            margin: 0;
            
            /* --- NEW LAYOUT FOR NAVBAR & FOOTER --- */
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            position: relative;
        }

        /* --- BACKGROUND IMAGE & EFFECTS --- */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: linear-gradient(to bottom, rgba(8, 9, 14, 0.8), var(--bg)), 
                        url('https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&q=80&w=2000');
            background-size: cover;
            background-position: center;
            z-index: -1;
            opacity: 0.4; 
        }

        body::after {
            content: ''; position: fixed; inset: 0;
            background-image: repeating-linear-gradient(90deg, rgba(255,255,255,0.01) 0px, rgba(255,255,255,0.01) 1px, transparent 1px, transparent 40px),
                              repeating-linear-gradient(0deg,  rgba(255,255,255,0.01) 0px, rgba(255,255,255,0.01) 1px, transparent 1px, transparent 40px);
            pointer-events: none;
            z-index: -1;
        }

        /* --- CENTERED WRAPPER --- */
        .main-content {
            flex: 1; /* Pushes the footer to the very bottom of the screen */
            display: flex;
            justify-content: center;
            align-items: flex-start; /* Keeps the FAQ near the top center */
            padding: 60px 20px;
            width: 100%;
            box-sizing: border-box;
        }

        .faq-wrapper {
            width: 100%;
            max-width: 850px;
            z-index: 1;
            animation: rise 0.7s cubic-bezier(0.16,1,0.3,1) both;
        }

        @keyframes rise {
            from { opacity: 0; transform: translateY(32px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .header-section {
            text-align: center;
            margin-bottom: 50px;
        }

        .header-section h1 {
            font-family: 'Syne', sans-serif;
            font-size: 2.5rem;
            margin-bottom: 10px;
            letter-spacing: -0.02em;
        }

        .header-section h1 span {
            color: var(--primary);
            text-shadow: 0 0 20px var(--primary-glow);
        }

        .header-section p {
            color: var(--muted);
            font-size: 14px;
        }

        /* --- ACCORDION DESIGN --- */
        .category-label {
            font-family: 'Syne', sans-serif;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: var(--primary-light);
            margin: 40px 0 15px 10px;
            display: block;
        }

        .faq-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            margin-bottom: 12px;
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .faq-card:hover {
            border-color: var(--muted2);
        }

        .faq-card.active {
            border-color: var(--border-active);
            box-shadow: 0 0 20px var(--primary-glow);
        }

        .faq-trigger {
            width: 100%;
            padding: 22px 28px;
            background: none;
            border: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            text-align: left;
            color: var(--text);
            font-family: 'Syne', sans-serif;
            font-size: 16px;
            font-weight: 600;
        }

        .faq-trigger .icon {
            width: 24px;
            height: 24px;
            background: var(--surface);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s;
            color: var(--primary);
            font-weight: bold;
        }

        .faq-card.active .icon {
            transform: rotate(45deg);
            background: var(--primary);
            color: white;
        }

        .faq-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background: rgba(255,255,255,0.02);
        }

        .faq-content p {
            padding: 0 28px 25px 28px;
            margin: 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.8;
        }

        /* --- CONTACT CTA --- */
        .support-footer {
            margin-top: 60px;
            padding: 30px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            text-align: center;
        }

        .btn-chat {
            display: inline-block;
            margin-top: 15px;
            padding: 12px 32px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-family: 'Syne', sans-serif;
            font-size: 14px;
            font-weight: 700;
            transition: 0.3s;
        }

        .btn-chat:hover {
            box-shadow: 0 0 25px var(--primary-glow);
            transform: translateY(-2px);
        }

    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="main-content">
    <div class="faq-wrapper">
        <div class="header-section">
            <h1>Travel <span>Support</span></h1>
            <p>Instant answers to your booking and travel queries</p>
        </div>

        <span class="category-label">Booking & Boarding</span>

        <div class="faq-card">
            <button class="faq-trigger">
                How do I show my ticket during boarding?
                <span class="icon">+</span>
            </button>
            <div class="faq-content">
                <p>You do not need a paper printout. Simply open your confirmation email or our app and show the QR code to the conductor. Ensure you have a valid ID matching the name on the ticket.</p>
            </div>
        </div>

        <div class="faq-card">
            <button class="faq-trigger">
                What is the baggage allowance?
                <span class="icon">+</span>
            </button>
            <div class="faq-content">
                <p>Standard tickets include one medium suitcase (max 20kg) stored in the luggage compartment and one small personal item to keep with you at your seat.</p>
            </div>
        </div>

        <span class="category-label">Payments & Policy</span>

        <div class="faq-card">
            <button class="faq-trigger">
                Can I change my travel date after booking?
                <span class="icon">+</span>
            </button>
            <div class="faq-content">
                <p>Yes, date changes are permitted up to 12 hours before departure through the 'Manage Booking' portal. A small re-scheduling fee may apply depending on the route.</p>
            </div>
        </div>

        <div class="faq-card">
            <button class="faq-trigger">
                Are my payment details secure?
                <span class="icon">+</span>
            </button>
            <div class="faq-content">
                <p>Absolutely. We use industry-standard SSL encryption and never store your full card details on our servers. All transactions are handled by verified payment gateways.</p>
            </div>
        </div>

        <div class="support-footer">
            <h3>Still need help?</h3>
            <p>Our support team is available 24/7 to assist with your journey.</p>
            <a href="contact-form.php" class="btn-chat">Open Live Chat</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
    document.querySelectorAll('.faq-trigger').forEach(trigger => {
        trigger.addEventListener('click', () => {
            const card = trigger.parentElement;
            const content = card.querySelector('.faq-content');
            
            // Toggle active class
            card.classList.toggle('active');

            if (card.classList.contains('active')) {
                content.style.maxHeight = content.scrollHeight + "px";
            } else {
                content.style.maxHeight = "0";
            }

            // Close other cards
            document.querySelectorAll('.faq-card').forEach(otherCard => {
                if (otherCard !== card) {
                    otherCard.classList.remove('active');
                    otherCard.querySelector('.faq-content').style.maxHeight = "0";
                }
            });
        });
    });
</script>

</body>
</html>