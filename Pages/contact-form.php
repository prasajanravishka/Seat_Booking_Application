<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Contact Us - RouteLink</title>
  <link rel="stylesheet" href="../Css/footer.css">
  <link rel="stylesheet" href="../Css/about.css">
  <link rel="stylesheet" href="../Css/navbar.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
 
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      /* BLUE THEME PALETTE */
      --ink: #0f172a;          
      --cream: #f8fafc;        
      --warm: #e2e8f0;         
      --accent: #2563eb;       
      --accent-light: #93c5fd; 
      --muted: #64748b;        
      --border: #cbd5e1;       
    }

    body {
      min-height: 100vh;
      margin: 0;
      
      /* --- FULL PAGE BACKGROUND SETTINGS --- */
      background-image: 
        linear-gradient(rgba(15, 23, 42, 0.75), rgba(15, 23, 42, 0.85)), 
        url('https://images.unsplash.com/photo-1570125909232-eb263c188f7e?q=80&w=2000&auto=format&fit=crop'); 
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      background-repeat: no-repeat;
      /* ------------------------------------- */

      font-family: 'DM Sans', sans-serif;
      color: var(--ink);
      
      /* NEW FIX: Stretches the page so Nav is top, Footer is bottom */
      display: flex;
      flex-direction: column;
      overflow-x: hidden;
    }

    /* NEW FIX: This holds the card perfectly in the center of the available space */
    .main-content {
      flex: 1; /* Pushes footer to the bottom */
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 60px 16px;
      width: 100%;
    }

    /* Two-Column Card Layout (Floating above the background) */
    .page-wrapper {
      display: grid;
      grid-template-columns: 1fr 1.2fr;
      max-width: 900px;
      width: 100%;
      background: rgba(15, 23, 42, 0.65); /* Semi-transparent dark background */
      backdrop-filter: blur(16px); /* Beautiful glass blur effect */
      -webkit-backdrop-filter: blur(16px);
      color: var(--cream);
      border: 1px solid rgba(255, 255, 255, 0.1);
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
      position: relative;
      animation: fadeUp 0.7s ease both;
      border-radius: 16px;
      overflow: hidden;
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(24px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    /* Left Side - Inner Image Panel */
    .image-panel {
      /* Sleek Modern Blue Passenger Bus matching your theme */
      background-image: url('https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?q=80&w=1000&auto=format&fit=crop');
      background-size: cover;
      background-position: center;
      min-height: 400px;
      position: relative;
    }

    /* Dark overlay on the inner image so it blends into the dark card */
    .image-panel::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(to right, transparent 50%, rgba(15, 23, 42, 0.9) 100%);
    }

    /* Right Side - Content Panel */
    .content-panel {
      padding: 56px 48px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      position: relative;
    }

    /* Decorative Background Circles */
    .content-panel::before {
      content: ''; position: absolute; top: -60px; right: -60px;
      width: 200px; height: 200px; border: 1px solid rgba(255,255,255,0.08); border-radius: 50%;
      pointer-events: none;
    }

    .brand-tag {
      font-family: 'DM Sans', sans-serif;
      font-size: 11px;
      font-weight: 500;
      letter-spacing: 0.2em;
      text-transform: uppercase;
      color: var(--accent-light);
      margin-bottom: 16px;
    }

    .panel-title {
      font-family: 'Playfair Display', serif;
      font-size: clamp(2rem, 3vw, 2.6rem);
      line-height: 1.15;
      margin-bottom: 16px;
    }

    .panel-title em {
      display: block;
      font-style: italic;
      color: var(--accent-light);
    }

    .panel-desc {
      font-size: 14px;
      line-height: 1.7;
      color: rgba(245,240,232,0.8);
      margin-bottom: 40px;
    }

    .contact-items {
      display: flex;
      flex-direction: column;
      gap: 20px;
      position: relative;
      z-index: 1;
    }

    .contact-item {
      display: flex;
      align-items: center;
      gap: 16px;
      background: rgba(255,255,255,0.05);
      padding: 16px 20px;
      border-radius: 8px;
      border: 1px solid rgba(255,255,255,0.1);
      transition: background 0.3s ease, transform 0.2s ease, border-color 0.3s ease;
    }

    .contact-item:hover {
        background: rgba(255,255,255,0.1);
        border-color: rgba(147, 197, 253, 0.3);
        transform: translateX(5px);
    }

    .ci-icon {
      width: 40px; height: 40px;
      background: rgba(37, 99, 235, 0.2);
      border: 1px solid rgba(37, 99, 235, 0.5);
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
      color: var(--accent-light);
      font-size: 16px;
      border-radius: 50%;
    }

    .ci-label {
      font-size: 10px;
      letter-spacing: 0.15em;
      text-transform: uppercase;
      color: var(--accent-light);
      margin-bottom: 4px;
    }

    .ci-value {
      font-size: 14px;
      color: #ffffff;
      line-height: 1.5;
      font-weight: 500;
    }

    /* Mobile Responsive Layout */
    @media (max-width: 768px) {
      .page-wrapper { 
        grid-template-columns: 1fr; 
      }
      .image-panel {
        min-height: 250px;
      }
      .image-panel::after {
        background: linear-gradient(to bottom, transparent, rgba(15, 23, 42, 1));
      }
      .content-panel { padding: 40px 28px; }
    }
  </style>
</head>
<body>
  
<?php include 'navbar.php'; ?>

<div class="main-content">
    <div class="page-wrapper">

        <div class="image-panel"></div>

        <div class="content-panel">
            <div>
              <p class="brand-tag">Get in touch</p>
              <h1 class="panel-title">We're here to <em>help.</em></h1>
              <p class="panel-desc">Have a question about your journey, tickets, or our services? Reach out to our team using the details below.</p>
            </div>

            <div class="contact-items">
              <div class="contact-item">
                <div class="ci-icon">✉</div>
                <div>
                  <p class="ci-label">Email Support</p>
                  <a href="mailto:prasajanravishka20@gmail.com" class="ci-value" style="text-decoration: none; transition: color 0.3s;" onmouseover="this.style.color='#93c5fd'" onmouseout="this.style.color='#ffffff'">prasajanravishka20@gmail.com</a>
                </div>
              </div>
              
              <div class="contact-item">
                <div class="ci-icon">☏</div>
                <div>
                  <p class="ci-label">Phone Direct</p>
                  <a href="tel:+94778189106" class="ci-value" style="text-decoration: none; transition: color 0.3s;" onmouseover="this.style.color='#93c5fd'" onmouseout="this.style.color='#ffffff'">+94 77 818 9106</a>
                </div>
              </div>
              
              <div class="contact-item">
                <div class="ci-icon">◎</div>
                <div>
                  <p class="ci-label">Main Office</p>
                  <p class="ci-value">Nittambuwa, Western Province<br/>Sri Lanka</p>
                </div>
              </div>
            </div>
        </div>

    </div>
</div>

<?php include 'footer.php'; ?>

</body>
</html>