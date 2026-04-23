<?php
session_start();
require_once '../DB/db_connect.php'; 

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = trim($_POST['first_name']);
    $lname = trim($_POST['last_name']);
    $phone = trim($_POST['phone_number']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $province = trim($_POST['province']);
    $country = trim($_POST['country']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic Validation
    if ($password !== $confirm_password) {
        $message = "<div class='alert error show'>Passwords do not match.</div>";
    } else {
        $stmt = $conn->prepare("SELECT u_id FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "<div class='alert error show'>Email is already registered.</div>";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_stmt = $conn->prepare("INSERT INTO user (first_name, last_name, phone_number, email, address, province, country, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("ssssssss", $fname, $lname, $phone, $email, $address, $province, $country, $hashed_password);

            if ($insert_stmt->execute()) {
                $message = "<div class='alert success show'>Registration successful! You can now login.</div>";
            } else {
                $message = "<div class='alert error show'>Error: " . $conn->error . "</div>";
            }
            $insert_stmt->close();
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Bus Seat Booking</title>
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@400;600;700&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
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
          --success: #22c55e;
          --error: #ef4444;
          --seat-gold: #f5a623;
        }

        body {
          min-height: 100vh;
          background: var(--bg);
          font-family: 'Mulish', sans-serif;
          color: var(--text);
          display: flex;
          align-items: center;
          justify-content: center;
          padding: 24px 16px;
          position: relative;
          overflow-x: hidden;
          margin: 0;
        }

        body::before {
          content: ''; position: fixed; top: -30vh; left: 50%; transform: translateX(-50%);
          width: 700px; height: 500px; background: radial-gradient(ellipse, rgba(58,111,255,0.12) 0%, transparent 70%); pointer-events: none;
        }

        body::after {
          content: ''; position: fixed; inset: 0;
          background-image: repeating-linear-gradient(90deg, rgba(255,255,255,0.015) 0px, rgba(255,255,255,0.015) 1px, transparent 1px, transparent 40px),
                            repeating-linear-gradient(0deg,  rgba(255,255,255,0.015) 0px, rgba(255,255,255,0.015) 1px, transparent 1px, transparent 40px);
          pointer-events: none;
        }

        .auth-wrapper {
          width: 100%; max-width: 980px; display: grid; grid-template-columns: 1fr 1fr;
          min-height: 600px; position: relative; z-index: 1; border: 1px solid var(--border);
          border-radius: 20px; overflow: hidden; box-shadow: 0 0 80px rgba(58,111,255,0.08), 0 40px 80px rgba(0,0,0,0.6);
          animation: rise 0.7s cubic-bezier(0.16,1,0.3,1) both;
        }

        @keyframes rise {
          from { opacity: 0; transform: translateY(32px) scale(0.98); }
          to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .brand-panel {
          background: linear-gradient(145deg, #0d1428 0%, #0a0f1e 60%, #060810 100%);
          padding: 52px 44px; display: flex; flex-direction: column; justify-content: space-between;
          border-right: 1px solid var(--border); position: relative; overflow: hidden;
        }

        .brand-panel::before {
          content: ''; position: absolute; bottom: -80px; right: -80px; width: 300px; height: 300px;
          background: radial-gradient(circle, rgba(58,111,255,0.15) 0%, transparent 65%); pointer-events: none;
        }

        .seat-deco { display: grid; grid-template-columns: repeat(5, 20px); gap: 6px; margin-bottom: 40px; }
        .seat-deco span { width: 20px; height: 16px; border-radius: 4px 4px 2px 2px; background: var(--muted2); display: block; }
        .seat-deco span.taken { background: var(--primary); box-shadow: 0 0 8px var(--primary-glow); }
        .seat-deco span.gold  { background: var(--seat-gold); box-shadow: 0 0 8px rgba(245,166,35,0.3); }

        .brand-logo { display: flex; align-items: center; gap: 10px; margin-bottom: 32px; }
        .logo-icon { width: 38px; height: 38px; background: var(--primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; box-shadow: 0 0 20px var(--primary-glow); }
        .logo-name { font-family: 'Syne', sans-serif; font-size: 1.35rem; font-weight: 800; letter-spacing: -0.02em; }
        .logo-name span { color: var(--primary-light); }

        .brand-title { font-family: 'Syne', sans-serif; font-size: clamp(1.6rem, 2.5vw, 2.1rem); font-weight: 700; line-height: 1.2; margin-bottom: 16px; }
        .brand-title mark { background: none; color: var(--primary-light); }
        .brand-desc { font-size: 14px; color: var(--muted); line-height: 1.7; margin-bottom: 40px; }
        .feature-list { display: flex; flex-direction: column; gap: 14px; position: relative; z-index: 1; }
        .feature-item { display: flex; align-items: center; gap: 12px; font-size: 13px; color: rgba(232,234,242,0.7); }
        .fi-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--primary); flex-shrink: 0; box-shadow: 0 0 6px var(--primary); }

        .form-panel { background: var(--card); display: flex; flex-direction: column; overflow: hidden; }
        .tab-bar { display: grid; grid-template-columns: 1fr 1fr; border-bottom: 1px solid var(--border); }
        .tab-btn { padding: 20px; background: none; border: none; color: var(--muted); font-family: 'Syne', sans-serif; font-size: 14px; font-weight: 600; letter-spacing: 0.05em; cursor: pointer; position: relative; transition: color 0.25s; }
        .tab-btn::after { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 2px; background: var(--primary); transform: scaleX(0); transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1); }
        .tab-btn.active { color: var(--text); }
        .tab-btn.active::after { transform: scaleX(1); }

        .form-container { flex: 1; padding: 36px 40px; overflow-y: auto; display: none; }
        .form-container.active { display: block; animation: fadeSlide 0.35s ease both; }
        @keyframes fadeSlide { from { opacity: 0; transform: translateX(12px); } to   { opacity: 1; transform: translateX(0); } }

        .form-section-title { font-family: 'Syne', sans-serif; font-size: 1.35rem; font-weight: 700; margin-bottom: 6px; }
        .form-section-sub { font-size: 13px; color: var(--muted); margin-bottom: 28px; }

        .alert { padding: 11px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 18px; display: none; }
        .alert.show { display: block; }
        .alert.error   { background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.3); color: #fca5a5; }
        .alert.success { background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.3); color: #86efac; }

        .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }
        .field { margin-bottom: 14px; }
        .field-row .field { margin-bottom: 0; }
        .field label { display: block; font-size: 11px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted); margin-bottom: 7px; }
        .field label .req { color: var(--primary-light); margin-left: 2px; }
        .field input, .field select { width: 100%; background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding: 11px 14px; font-family: 'Mulish', sans-serif; font-size: 13.5px; color: var(--text); outline: none; transition: border-color 0.2s, box-shadow 0.2s; box-sizing: border-box;}
        .field input::placeholder { color: var(--muted2); }
        .field input:focus, .field select:focus { border-color: var(--border-active); box-shadow: 0 0 0 3px var(--primary-glow); }

        .btn-primary { width: 100%; padding: 13px; background: var(--primary); border: none; border-radius: 8px; color: #fff; font-family: 'Syne', sans-serif; font-size: 14px; font-weight: 700; letter-spacing: 0.05em; cursor: pointer; transition: background 0.25s, box-shadow 0.25s, transform 0.15s; box-shadow: 0 4px 20px var(--primary-glow); margin-top: 10px;}
        .btn-primary:hover:not(:disabled) { background: var(--primary-light); box-shadow: 0 6px 28px rgba(58,111,255,0.4); transform: translateY(-1px); }
        
        @media (max-width: 700px) {
          .auth-wrapper { grid-template-columns: 1fr; }
          .brand-panel  { display: none; }
          .form-container { padding: 28px 24px; }
          .field-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="auth-wrapper">
    <div class="brand-panel">
        <div>
            <div class="brand-logo">
                <div class="logo-icon">🚌</div>
                <div class="logo-name">Bus<span>Book</span></div>
            </div>
            <h1 class="brand-title">Your Journey,<br><mark>Perfectly Planned</mark></h1>
            <p class="brand-desc">Join our platform to book premium bus seats with ease. Experience comfort and reliability on every trip.</p>
            
            <div class="feature-list">
                <div class="feature-item"><div class="fi-dot"></div> Real-time seat availability</div>
                <div class="feature-item"><div class="fi-dot"></div> Secure payment processing</div>
                <div class="feature-item"><div class="fi-dot"></div> Instant ticket confirmation</div>
            </div>
        </div>
        
        <div class="seat-deco">
            <span></span><span class="taken"></span><span></span><span class="gold"></span><span></span>
            <span class="taken"></span><span></span><span></span><span class="taken"></span><span></span>
        </div>
    </div>

    <div class="form-panel">
        <div class="tab-bar">
            <button class="tab-btn" onclick="window.location.href='logging_page.php'">Log In</button>
            <button class="tab-btn active">Register</button>
        </div>

        <div class="form-container active">
            <h2 class="form-section-title">Create an Account</h2>
            <p class="form-section-sub">Fill in your details to get started.</p>

            <?php echo $message; ?>

            <form action="register_page.php" method="POST">
                <div class="field-row">
                    <div class="field">
                        <label>First Name <span class="req">*</span></label>
                        <input type="text" name="first_name" required>
                    </div>
                    <div class="field">
                        <label>Last Name <span class="req">*</span></label>
                        <input type="text" name="last_name" required>
                    </div>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label>Email <span class="req">*</span></label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="field">
                        <label>Phone Number <span class="req">*</span></label>
                        <input type="text" name="phone_number" placeholder="+94..." required>
                    </div>
                </div>

                <div class="field">
                    <label>Address <span class="req">*</span></label>
                    <input type="text" name="address" required>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label>Province <span class="req">*</span></label>
                        <select name="province" required>
                            <option value="">Select province…</option>
                            <option value="Central">Central</option>
                            <option value="Eastern">Eastern</option>
                            <option value="North Central">North Central</option>
                            <option value="Northern">Northern</option>
                            <option value="North Western">North Western</option>
                            <option value="Sabaragamuwa">Sabaragamuwa</option>
                            <option value="Southern">Southern</option>
                            <option value="Uva">Uva</option>
                            <option value="Western">Western</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Country <span class="req">*</span></label>
                        <select name="country" required>
                            <option value="">Select country…</option>
                            <option value="LK">🇱🇰 Sri Lanka</option>
                            <option value="IN">🇮🇳 India</option>
                            <option value="US">🇺🇸 United States</option>
                            <option value="GB">🇬🇧 United Kingdom</option>
                            <option value="AU">🇦🇺 Australia</option>
                            <option value="CA">🇨🇦 Canada</option>
                            <option value="SG">🇸🇬 Singapore</option>
                            <option value="AE">🇦🇪 UAE</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label>Password <span class="req">*</span></label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="field">
                        <label>Confirm Password <span class="req">*</span></label>
                        <input type="password" name="confirm_password" required>
                    </div>
                </div>

                <button type="submit" class="btn-primary">
                    <span class="btn-text">Create Account</span>
                </button>
            </form>
        </div>
    </div>
</div>

</body>
</html>