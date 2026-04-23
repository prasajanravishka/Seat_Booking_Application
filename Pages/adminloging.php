<?php
session_start();
require_once '../DB/db_connect.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // IMPORTANT: add role='admin'
    $stmt = $conn->prepare("SELECT u_id, first_name, password, role FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Check password
        if (password_verify($password, $user['password'])) {

            // ✅ ADMIN CHECK
            if ($user['role'] !== 'admin') {
                $message = "<div class='alert error show'>Access denied. Admin only.</div>";
            } else {
                $_SESSION['admin_id'] = $user['u_id'];
                $_SESSION['admin_name'] = $user['first_name'];

                header("Location: admin.php"); // redirect to admin panel
                exit();
            }

        } else {
            $message = "<div class='alert error show'>Invalid email or password.</div>";
        }
    } else {
        $message = "<div class='alert error show'>Invalid email or password.</div>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login</title>

<link href="https://fonts.googleapis.com/css2?family=Mulish:wght@400;600;700&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">

<style>
:root {
  --bg: #08090e;
  --card: #13151f;
  --border: #1e2130;
  --primary: #3a6fff;
  --primary-glow: rgba(58,111,255,0.25);
  --text: #e8eaf2;
  --muted: #6b7080;
  --error: #ef4444;
}

/* BACKGROUND */
body {
  margin:0;
  font-family:'Mulish',sans-serif;
  background: radial-gradient(circle at top, #0f172a, #020617);
  color:var(--text);
  display:flex;
  align-items:center;
  justify-content:center;
  height:100vh;
}

/* CARD */
.login-box {
  width:400px;
  background:var(--card);
  border-radius:18px;
  padding:40px;
  box-shadow:0 20px 60px rgba(0,0,0,0.7);
  border:1px solid var(--border);
  animation:fadeUp 0.6s ease;
}

@keyframes fadeUp {
  from {opacity:0; transform:translateY(30px);}
  to {opacity:1; transform:translateY(0);}
}

/* HEADER */
.title {
  font-family:'Syne',sans-serif;
  font-size:26px;
  margin-bottom:8px;
}

.subtitle {
  font-size:13px;
  color:var(--muted);
  margin-bottom:25px;
}

/* INPUT */
.field {
  margin-bottom:18px;
}

.field label {
  font-size:12px;
  color:var(--muted);
}

.field input {
  width:100%;
  padding:12px;
  border-radius:8px;
  border:1px solid var(--border);
  background:#0f1118;
  color:#fff;
  outline:none;
}

.field input:focus {
  border-color:var(--primary);
  box-shadow:0 0 0 3px var(--primary-glow);
}

/* BUTTON */
button {
  width:100%;
  padding:14px;
  background:var(--primary);
  border:none;
  border-radius:8px;
  color:#fff;
  font-weight:bold;
  cursor:pointer;
  transition:0.3s;
}

button:hover {
  background:#5b84ff;
}

/* ALERT */
.alert {
  padding:10px;
  border-radius:8px;
  margin-bottom:15px;
  font-size:13px;
}

.error {
  background:rgba(239,68,68,0.1);
  border:1px solid rgba(239,68,68,0.4);
  color:#fca5a5;
}
</style>

</head>

<body>

<div class="login-box">

    <div class="title">Admin Panel 🔐</div>
    <div class="subtitle">Authorized access only</div>

    <?= $message ?>

    <form action="Admindashbord.php" method="POST">
        <div class="field">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="field">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit">Login as Admin</button>
    </form>

</div>

</body>
</html>