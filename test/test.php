<?php
session_start();
require_once '../DB/db_connect.php';

// Include PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer from your directory structure
require '../PHPMailer-master/src/Exception.php';
require '../PHPMailer-master/src/PHPMailer.php';
require '../PHPMailer-master/src/SMTP.php';

// Create messages table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql) !== TRUE) {
    die("❌ Error creating table: " . $conn->error);
}

// ==========================================
// Form submission handling (AJAX POST)
// ==========================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Tell the browser we are sending back JSON
    header('Content-Type: application/json');

    // Safely capture all incoming form data
    $firstName = htmlspecialchars($_POST['firstName'] ?? '');
    $lastName  = htmlspecialchars($_POST['lastName'] ?? '');
    $email     = htmlspecialchars($_POST['email'] ?? '');
    $subject   = htmlspecialchars($_POST['subject'] ?? 'General Inquiry');
    $message   = htmlspecialchars($_POST['message'] ?? '');

    // Combine names for the database
    $fullName = trim($firstName . ' ' . $lastName);

    // 1. Save to database
    $stmt = $conn->prepare("INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $fullName, $email, $subject, $message);
    $db_success = $stmt->execute();
    $stmt->close();

    // 2. Send email using PHPMailer
    $mail = new PHPMailer(true);
    $email_success = false;

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'ravishkacourse@gmail.com'; // Your Gmail address
        $mail->Password   = 'bujkguplryiiuxza'; // Warning: Consider generating a new App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  
        $mail->Port       = 587;
        
        $mail->setFrom('prasajanravishka20@gmail.com', 'Website Contact Form');
        $mail->addAddress('ravishkacourse@gmail.com'); // Where the message is sent to
        $mail->addReplyTo($email, $fullName); // Allows you to hit "Reply" and email the customer directly

        $mail->isHTML(true);
        $mail->Subject = "New Contact Message: $subject";
        $mail->Body    = "
            <h3>New Contact Form Submission</h3>
            <p><strong>Name:</strong> $fullName</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Subject:</strong> $subject</p>
            <hr>
            <p><strong>Message:</strong><br>" . nl2br($message) . "</p>
        ";

        $mail->send();
        $email_success = true;
    } catch (Exception $e) {
        $email_success = false;
    }

    // 3. Return JSON response to the JavaScript
    if ($db_success) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error"]);
    }
    
    // Stop the script here so it doesn't load the HTML below during an AJAX request
    exit(); 
}
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
      background-color: var(--cream);
      font-family: 'DM Sans', sans-serif;
      color: var(--ink);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 16px;
      position: relative;
      overflow-x: hidden;
    }

    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background-image:
        radial-gradient(circle at 15% 20%, rgba(37, 99, 235, 0.08) 0%, transparent 40%),
        radial-gradient(circle at 85% 80%, rgba(37, 99, 235, 0.06) 0%, transparent 40%);
      pointer-events: none;
    }

    .page-wrapper {
      display: grid;
      grid-template-columns: 1fr 1.6fr;
      max-width: 900px;
      width: 100%;
      background: #fff;
      border: 1px solid var(--border);
      box-shadow: 8px 8px 0 var(--warm), 16px 16px 0 var(--border);
      position: relative;
      animation: fadeUp 0.7s ease both;
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(24px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .panel-left {
      background: var(--ink);
      color: var(--cream);
      padding: 56px 40px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      position: relative;
      overflow: hidden;
    }

    .panel-left::before {
      content: '';
      position: absolute;
      top: -60px; right: -60px;
      width: 200px; height: 200px;
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 50%;
    }

    .panel-left::after {
      content: '';
      position: absolute;
      bottom: -80px; left: -40px;
      width: 260px; height: 260px;
      border: 1px solid rgba(255,255,255,0.05);
      border-radius: 50%;
    }

    .brand-tag {
      font-family: 'DM Sans', sans-serif;
      font-size: 11px;
      font-weight: 500;
      letter-spacing: 0.2em;
      text-transform: uppercase;
      color: var(--accent-light);
      margin-bottom: 24px;
    }

    .panel-title {
      font-family: 'Playfair Display', serif;
      font-size: clamp(2rem, 3.5vw, 2.6rem);
      line-height: 1.15;
      margin-bottom: 20px;
    }

    .panel-title em {
      display: block;
      font-style: italic;
      color: var(--accent-light);
    }

    .panel-desc {
      font-size: 14px;
      line-height: 1.7;
      color: rgba(245,240,232,0.65);
      margin-bottom: 48px;
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
      align-items: flex-start;
      gap: 14px;
    }

    .ci-icon {
      width: 36px; height: 36px;
      border: 1px solid rgba(37, 99, 235, 0.4);
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
      color: var(--accent-light);
      font-size: 14px;
    }

    .ci-label {
      font-size: 10px;
      letter-spacing: 0.15em;
      text-transform: uppercase;
      color: var(--accent-light);
      margin-bottom: 3px;
    }

    .ci-value {
      font-size: 13px;
      color: rgba(232, 236, 245, 0.8);
      line-height: 1.4;
    }

    .panel-right {
      padding: 56px 48px;
    }

    .form-header {
      margin-bottom: 36px;
    }

    .form-label-top {
      font-size: 11px;
      font-weight: 500;
      letter-spacing: 0.2em;
      text-transform: uppercase;
      color: var(--accent);
      margin-bottom: 8px;
    }

    .form-title {
      font-family: 'Playfair Display', serif;
      font-size: 1.9rem;
      line-height: 1.2;
      color: var(--ink);
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin-bottom: 20px;
    }

    .field {
      display: flex;
      flex-direction: column;
      gap: 6px;
      animation: fadeUp 0.6s ease both;
    }

    .field:nth-child(1) { animation-delay: 0.05s; }
    .field:nth-child(2) { animation-delay: 0.1s; }

    .field label {
      font-size: 11px;
      font-weight: 500;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--muted);
    }

    .field input,
    .field select,
    .field textarea {
      width: 100%;
      background: transparent;
      border: none;
      border-bottom: 1.5px solid var(--border);
      padding: 10px 0;
      font-family: 'DM Sans', sans-serif;
      font-size: 14px;
      color: var(--ink);
      outline: none;
      transition: border-color 0.25s;
      appearance: none;
    }

    .field input::placeholder,
    .field textarea::placeholder {
      color: var(--border);
    }

    .field input:focus,
    .field select:focus,
    .field textarea:focus {
      border-color: var(--accent);
    }

    .field textarea {
      resize: none;
      height: 90px;
    }

    .field select {
      cursor: pointer;
      color: var(--ink);
    }

    .field-full {
      margin-bottom: 20px;
    }

    .submit-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-top: 36px;
    }

    .submit-note {
      font-size: 12px;
      color: var(--muted);
    }

    .btn-submit {
      background: var(--ink);
      color: var(--cream);
      border: none;
      padding: 14px 36px;
      font-family: 'DM Sans', sans-serif;
      font-size: 13px;
      font-weight: 500;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      cursor: pointer;
      position: relative;
      overflow: hidden;
      transition: background 0.3s;
    }

    .btn-submit::before {
      content: '';
      position: absolute;
      inset: 0;
      background: var(--accent);
      transform: translateX(-100%);
      transition: transform 0.35s ease;
    }

    .btn-submit:hover::before { transform: translateX(0); }
    .btn-submit span { position: relative; z-index: 1; }

    .success-overlay {
      display: none;
      position: absolute;
      inset: 0;
      background: #fff;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 40px;
      animation: fadeUp 0.5s ease;
      z-index: 10;
    }

    .success-overlay.show { display: flex; }

    .success-icon {
      width: 60px; height: 60px;
      border: 2px solid var(--accent);
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 26px;
      color: var(--accent);
      margin-bottom: 20px;
    }

    .success-title {
      font-family: 'Playfair Display', serif;
      font-size: 1.6rem;
      margin-bottom: 12px;
    }

    .success-msg {
      font-size: 14px;
      color: var(--muted);
      line-height: 1.6;
      max-width: 280px;
      margin-bottom: 25px;
    }

    .btn-close-overlay {
      background: var(--accent);
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
      font-family: 'DM Sans', sans-serif;
    }

    @media (max-width: 680px) {
      .page-wrapper {
        grid-template-columns: 1fr;
        box-shadow: 6px 6px 0 var(--border);
      }
      .panel-left { padding: 40px 28px; }
      .panel-right { padding: 40px 28px; }
      .form-row { grid-template-columns: 1fr; }
      .submit-row { flex-direction: column; gap: 16px; align-items: flex-start; }
    }
  </style>
</head>
<body>
  
<div class="page-wrapper">

  <div class="panel-left">
    <div>
      <p class="brand-tag">Get in touch</p>
      <h1 class="panel-title">Let's start a <em>conversation.</em></h1>
      <p class="panel-desc">We'd love to hear from you. Send us a message and we'll get back to you as soon as possible.</p>
    </div>

    <div class="contact-items">
      <div class="contact-item">
        <div class="ci-icon">✉</div>
        <div>
          <p class="ci-label">Email</p>
          <p class="ci-value">support@routelink.com</p>
        </div>
      </div>
      <div class="contact-item">
        <div class="ci-icon">☏</div>
        <div>
          <p class="ci-label">Phone</p>
          <p class="ci-value">+94 77 818 9106</p>
        </div>
      </div>
      <div class="contact-item">
        <div class="ci-icon">◎</div>
        <div>
          <p class="ci-label">Location</p>
          <p class="ci-value">Nittambuwa, Western Province<br/>Sri Lanka</p>
        </div>
      </div>
    </div>
  </div>

  <div class="panel-right" style="position:relative;">
    <div class="form-header">
      <p class="form-label-top">Send a message</p>
      <h2 class="form-title">How can we help you?</h2>
    </div>

    <form id="contactForm" novalidate>

      <div class="form-row">
        <div class="field">
          <label for="firstName">First Name</label>
          <input type="text" id="firstName" name="firstName" placeholder="Jane" required />
        </div>
        <div class="field">
          <label for="lastName">Last Name</label>
          <input type="text" id="lastName" name="lastName" placeholder="Doe" required />
        </div>
      </div>

      <div class="field-full field">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" placeholder="jane@example.com" required />
      </div>

      <div class="field-full field">
        <label for="subject">Subject</label>
        <select id="subject" name="subject" required>
          <option value="" disabled selected>Choose a topic…</option>
          <option value="General Inquiry">General Inquiry</option>
          <option value="Partnership">Partnership</option>
          <option value="Support">Support</option>
          <option value="Feedback">Feedback</option>
          <option value="Other">Other</option>
        </select>
      </div>

      <div class="field-full field">
        <label for="message">Your Message</label>
        <textarea id="message" name="message" placeholder="Tell us what's on your mind…" required></textarea>
      </div>

      <div class="submit-row">
        <p class="submit-note">We reply within 24 hours.</p>
        <button type="submit" class="btn-submit"><span>Send Message →</span></button>
      </div>

    </form>

    <div class="success-overlay" id="successOverlay">
      <div class="success-icon">✓</div>
      <h3 class="success-title">Message Sent!</h3>
      <p class="success-msg">Thank you for reaching out. We'll be in touch with you very soon.</p>
      <button class="btn-close-overlay" onclick="closeOverlay()">Send Another</button>
    </div>
  </div>

</div>

<script>
  document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Validate the fields
    const fields = [
      document.getElementById('firstName'),
      document.getElementById('lastName'),
      document.getElementById('email'),
      document.getElementById('subject'),
      document.getElementById('message'),
    ];

    let valid = true;
    fields.forEach(f => {
      if (!f.value.trim()) {
        f.style.borderColor = '#c0392b'; 
        valid = false;
      } else {
        f.style.borderColor = '';
      }
    });

    if (!valid) return;

    const submitBtn = this.querySelector('.btn-submit span');
    const originalText = submitBtn.innerText;
    submitBtn.innerText = "Sending...";

    const formData = new FormData(this);

    // FIX: Send the data to THIS exact file (contact-form.php) instead of process_contact.php
    fetch(window.location.href, {
      method: 'POST',
      body: formData
    })
    .then(response => response.json()) // Parse the JSON from PHP
    .then(data => {
      if(data.status === 'success') {
          document.getElementById('successOverlay').classList.add('show');
          this.reset(); 
      } else {
          alert("Could not send the message. Please try again.");
      }
      submitBtn.innerText = originalText;
    })
    .catch(error => {
      alert("Oops! Something went wrong with the server connection.");
      submitBtn.innerText = originalText;
    });
  });

  function closeOverlay() {
      document.getElementById('successOverlay').classList.remove('show');
  }
</script>

</body>
</html>