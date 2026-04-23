<?php
session_start();
require_once '../DB/db_connect.php';


// CRITICAL SAFETY: If the user visits the login page, destroy any old sessions so they are forced to log in again.
if (isset($_SESSION['u_id'])) {
    session_unset();
    session_destroy();
    session_start(); // Restart a fresh session for the new login attempt
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT u_id, first_name, password FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Check if the password matches (Allows both Secure Hash and Plain-text testing)
        if (password_verify($password, $user['password']) || $password === $user['password']) {
            
            session_regenerate_id(true);

            // Success! Store user data
            $_SESSION['u_id'] = $user['u_id'];
            $_SESSION['user_name'] = $user['first_name'];
            
            // Safely redirect to the booking search page!
            header("Location: booking1page.php"); 
            exit();
        } else {
            $message = "<div class='alert error show'>❌ Incorrect Password. Please try again.</div>";
        }
    } else {
        $message = "<div class='alert error show'>❌ No account found with that email.</div>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - RouteLink</title>
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

        /* CSS Reset */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh; 
            background: var(--bg); 
            font-family: 'Mulish', sans-serif;
            color: var(--text); 
            /* Changed to column so navbar sits at top, auth-wrapper in middle */
            display: flex; 
            flex-direction: column;
            position: relative; 
            overflow-x: hidden; 
        }

        body::before {
            content: ''; 
            position: fixed; 
            top: -30vh; 
            left: 50%; 
            transform: translateX(-50%);
            width: 700px; 
            height: 500px; 
            background: radial-gradient(ellipse, rgba(58,111,255,0.12) 0%, transparent 70%); 
            pointer-events: none;
        }

        body::after {
            content: ''; 
            position: fixed; 
            inset: 0;
            background-image: 
                repeating-linear-gradient(90deg, rgba(255,255,255,0.015) 0px, rgba(255,255,255,0.015) 1px, transparent 1px, transparent 40px),
                repeating-linear-gradient(0deg,  rgba(255,255,255,0.015) 0px, rgba(255,255,255,0.015) 1px, transparent 1px, transparent 40px);
            pointer-events: none;
            z-index: -1;
        }

        .auth-wrapper {
            width: 100%; 
            max-width: 980px; 
            display: grid; 
            grid-template-columns: 1fr 1fr;
            min-height: 600px; 
            position: relative; 
            z-index: 1; 
            border: 1px solid var(--border);
            border-radius: 20px; 
            overflow: hidden; 
            box-shadow: 0 0 80px rgba(58,111,255,0.08), 0 40px 80px rgba(0,0,0,0.6);
            animation: rise 0.7s cubic-bezier(0.16,1,0.3,1) both;
            /* Margin auto ensures it centers perfectly in the remaining space below navbar */
            margin: auto;
            margin-top: 40px;
            margin-bottom: 40px;
        }

        @keyframes rise {
            from { opacity: 0; transform: translateY(32px) scale(0.98); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .brand-panel {
            background: linear-gradient(145deg, #0d1428 0%, #0a0f1e 60%, #060810 100%);
            padding: 52px 44px; 
            display: flex; 
            flex-direction: column; 
            justify-content: space-between;
            border-right: 1px solid var(--border); 
            position: relative; 
            overflow: hidden;
        }

        .seat-deco { 
            display: grid; 
            grid-template-columns: repeat(5, 20px); 
            gap: 6px; 
            margin-bottom: 40px; 
        }

        .seat-deco span { 
            width: 20px; 
            height: 16px; 
            border-radius: 4px 4px 2px 2px; 
            background: var(--muted2); 
            display: block; 
        }

        .seat-deco span.taken { 
            background: var(--primary); 
            box-shadow: 0 0 8px var(--primary-glow); 
        }

        .seat-deco span.gold { 
            background: var(--seat-gold); 
            box-shadow: 0 0 8px rgba(245,166,35,0.3); 
        }

        .brand-logo { 
            display: flex; 
            align-items: center; 
            gap: 10px; 
            margin-bottom: 32px; 
        }

        .logo-icon { 
            width: 38px; 
            height: 38px; 
            background: var(--primary); 
            border-radius: 10px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-size: 18px; 
            box-shadow: 0 0 20px var(--primary-glow); 
        }

        .logo-name { 
            font-family: 'Syne', sans-serif; 
            font-size: 1.35rem; 
            font-weight: 800; 
            letter-spacing: -0.02em; 
        }

        .logo-name span { color: var(--primary-light); }

        .brand-title { 
            font-family: 'Syne', sans-serif; 
            font-size: clamp(1.6rem, 2.5vw, 2.1rem); 
            font-weight: 700; 
            line-height: 1.2; 
            margin-bottom: 16px; 
        }

        .brand-title mark { 
            background: none; 
            color: var(--primary-light); 
        }

        .brand-desc { 
            font-size: 14px; 
            color: var(--muted); 
            line-height: 1.7; 
            margin-bottom: 40px; 
        }

        .form-panel { 
            background: var(--card); 
            display: flex; 
            flex-direction: column; 
            overflow: hidden; 
        }

        .tab-bar { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            border-bottom: 1px solid var(--border); 
        }

        .tab-btn { 
            padding: 20px; 
            background: none; 
            border: none; 
            color: var(--muted); 
            font-family: 'Syne', sans-serif; 
            font-size: 14px; 
            font-weight: 600; 
            letter-spacing: 0.05em; 
            cursor: pointer; 
            position: relative; 
            transition: color 0.25s; 
        }

        .tab-btn::after { 
            content: ''; 
            position: absolute; 
            bottom: 0; 
            left: 0; 
            right: 0; 
            height: 2px; 
            background: var(--primary); 
            transform: scaleX(0); 
            transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1); 
        }

        .tab-btn.active { color: var(--text); }
        .tab-btn.active::after { transform: scaleX(1); }

        .form-container { 
            flex: 1; 
            padding: 36px 40px; 
            overflow-y: auto; 
            display: none; 
        }

        .form-container.active { 
            display: block; 
            animation: fadeSlide 0.35s ease both; 
        }

        @keyframes fadeSlide { 
            from { opacity: 0; transform: translateX(12px); } 
            to   { opacity: 1; transform: translateX(0); } 
        }

        .form-section-title { 
            font-family: 'Syne', sans-serif; 
            font-size: 1.35rem; 
            font-weight: 700; 
            margin-bottom: 6px; 
        }

        .form-section-sub { 
            font-size: 13px; 
            color: var(--muted); 
            margin-bottom: 28px; 
        }

        .alert { 
            padding: 11px 14px; 
            border-radius: 8px; 
            font-size: 13px; 
            margin-bottom: 18px; 
            display: none; 
        }

        .alert.show { display: block; }

        .alert.error { 
            background: rgba(239,68,68,0.12); 
            border: 1px solid rgba(239,68,68,0.3); 
            color: #fca5a5; 
        }

        .field { margin-bottom: 20px; }

        .field label { 
            display: block; 
            font-size: 11px; 
            font-weight: 600; 
            letter-spacing: 0.1em; 
            text-transform: uppercase; 
            color: var(--muted); 
            margin-bottom: 7px; 
        }

        .pw-wrap {
            position: relative;
            width: 100%;
        }

        .field input { 
            width: 100%; 
            background: var(--surface); 
            border: 1px solid var(--border); 
            border-radius: 8px; 
            padding: 11px 14px; 
            font-family: 'Mulish', sans-serif; 
            font-size: 13.5px; 
            color: var(--text); 
            outline: none; 
            transition: border-color 0.2s, box-shadow 0.2s; 
        }

        .field input:focus { 
            border-color: var(--border-active); 
            box-shadow: 0 0 0 3px var(--primary-glow); 
        }

        .btn-primary { 
            width: 100%; 
            padding: 13px; 
            background: var(--primary); 
            border: none; 
            border-radius: 8px; 
            color: #fff; 
            font-family: 'Syne', sans-serif; 
            font-size: 14px; 
            font-weight: 700; 
            letter-spacing: 0.05em; 
            cursor: pointer; 
            transition: background 0.25s, box-shadow 0.25s, transform 0.15s; 
            box-shadow: 0 4px 20px var(--primary-glow); 
            margin-top: 10px; 
        }

        .btn-primary:hover:not(:disabled) { 
            background: var(--primary-light); 
            box-shadow: 0 6px 28px rgba(58,111,255,0.4); 
            transform: translateY(-1px); 
        }

        @media (max-width: 768px) {
            .auth-wrapper { 
                grid-template-columns: 1fr; 
                margin: 20px;
                width: auto;
            }
            .brand-panel { display: none; }
            .form-container { padding: 28px 24px; }
        }
    </style>
</head>
<body>

<div class="auth-wrapper">
    <div class="brand-panel">
        <div>
            <div class="brand-logo">
                <div class="logo-icon">🚌</div>
                <div class="logo-name">Route<span>Link</span></div>
            </div>
            <h1 class="brand-title">Welcome Back,<br><mark>Ready to Travel?</mark></h1>
            <p class="brand-desc">Log in to manage your bookings, check schedules, and explore new destinations.</p>
        </div>
        
        <div class="seat-deco">
            <span></span><span class="taken"></span><span></span><span class="gold"></span><span></span>
            <span class="taken"></span><span></span><span></span><span class="taken"></span><span></span>
        </div>
    </div>

    <div class="form-panel">
        <div class="tab-bar">
            <button class="tab-btn active">Log In</button>
            <button class="tab-btn" onclick="window.location.href='register_page.php'">Register</button>
        </div>

        <div class="form-container active">
            <h2 class="form-section-title">Account Login</h2>
            <p class="form-section-sub">Enter your credentials to access your account.</p>

            <?php echo $message; ?>

            <form action="" method="POST">
                <div class="field">
                    <label>Email Address</label>
                    <input type="email" name="email" required>
                </div>

                <div class="field">
                    <label>Password</label>
                    <div class="pw-wrap">
                        <input type="password" name="password" required>
                    </div>
                </div>

                <button type="submit" class="btn-primary">
                    <span class="btn-text">Sign In</span>
                </button>
            </form>
        </div>
    </div>
</div>

</body>
</html>