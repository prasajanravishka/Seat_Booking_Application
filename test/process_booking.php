<?php
session_start();
require_once '../DB/db_connect.php';

$payment_success = false;
$error_message = "";

// ==========================================================
// 1. Handle Final Payment Submission & Database Insert
// ==========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_now'])) {
    
    $schedule_id = $_POST['schedule_id'];
    $seat_ids_str = $_POST['selected_seat_ids'];
    $total_amount = $_POST['total_amount'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    
    // Check if user is logged in, otherwise set to NULL (or handle guest checkout based on your DB rules)
    $session_id = $_SESSION['u_id'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? NULL;
    $seat_ids = explode(',', $seat_ids_str);
    
    // Start a database transaction to ensure all seats are booked together or none at all
    $conn->begin_transaction();
    
    try {
        // Prepare the insert statement for the bookings table
        // IMPORTANT: Adjust column names (user_id, schedule_id, seat_id, status) to exactly match your DB schema
        $insert_query = "INSERT INTO bookings (user_id, schedule_id, seat_id, status) VALUES (?, ?, ?, 'confirmed')";
        $stmt = $conn->prepare($insert_query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        foreach ($seat_ids as $seat_id) {
            $stmt->bind_param("iii", $session_id, $schedule_id, $seat_id);
            $stmt->execute();
        }
        
        // If everything is successful, commit the transaction
        $conn->commit();
        $stmt->close();
        $payment_success = true;
        
    } catch (Exception $e) {
        // If there's an error (e.g., someone else booked the seat a millisecond ago), roll back
        $conn->rollback();
        $error_message = "Payment failed or seats became unavailable. Error: " . $e->getMessage();
    }
} 
// ==========================================================
// 2. Arriving from Page 2 (Seat Selection)
// ==========================================================
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_seat_ids'])) {
    $schedule_id = $_POST['schedule_id'];
    $seat_ids_str = $_POST['selected_seat_ids'];
    $total_amount = $_POST['total_amount'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    
    $seat_count = count(explode(',', $seat_ids_str));
} else {
    // If someone tries to access this page directly via URL, kick them back to start
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout - RouteLink</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Syne:wght@700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0f172a; 
            --card-bg: #1e293b; 
            --text-main: #f8fafc; 
            --text-muted: #94a3b8;
            --primary-btn: #3b82f6; 
            --primary-hover: #2563eb;
            --success: #10b981;
            --error: #ef4444;
            --border: #334155;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--bg-color); 
            color: var(--text-main); 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 2rem 1rem; 
        }

        .checkout-container { 
            background: var(--card-bg); 
            padding: 2.5rem; 
            border-radius: 20px; 
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); 
            max-width: 500px; 
            width: 100%; 
        }

        .brand-title { 
            font-family: 'Syne', sans-serif; 
            font-size: 1.5rem; 
            text-align: center; 
            margin-bottom: 2rem; 
        }

        .brand-title span { color: var(--primary-btn); }

        /* Order Summary */
        .summary-box {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.8rem;
            font-size: 0.95rem;
        }

        .summary-row.total {
            border-top: 1px dashed var(--border);
            padding-top: 1rem;
            margin-top: 1rem;
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--primary-btn);
        }

        /* Form Fields */
        .input-group { margin-bottom: 1.2rem; }
        .input-group label { 
            display: block; 
            font-size: 0.85rem; 
            color: var(--text-muted); 
            margin-bottom: 0.5rem; 
        }
        
        .input-group input { 
            width: 100%; 
            padding: 0.9rem; 
            border-radius: 8px; 
            border: 1px solid var(--border); 
            background: #0f172a; 
            color: white; 
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: border-color 0.2s;
        }

        .input-group input:focus {
            border-color: var(--primary-btn);
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }

        .row-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .btn-submit { 
            width: 100%; 
            background: var(--primary-btn); 
            color: white; 
            padding: 1.1rem; 
            border: none; 
            border-radius: 10px; 
            font-size: 1rem;
            font-weight: 600; 
            cursor: pointer; 
            transition: all 0.2s ease; 
            margin-top: 1rem;
        }
        
        .btn-submit:hover { background: var(--primary-hover); }

        /* Success State */
        .success-state { text-align: center; }
        .success-icon {
            width: 70px;
            height: 70px;
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 1.5rem auto;
        }

        .error-banner {
            background: rgba(239, 68, 68, 0.1);
            color: #ff9999;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid var(--error);
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="checkout-container">
    
    <?php if ($payment_success): ?>
        <div class="success-state">
            <div class="success-icon">✓</div>
            <h2 style="margin-bottom: 10px; font-family: 'Syne';">Payment Successful!</h2>
            <p style="color: var(--text-muted); margin-bottom: 2rem; line-height: 1.6;">
                Thank you, <?= htmlspecialchars($name) ?>. Your seats have been confirmed and an itinerary has been sent to <?= htmlspecialchars($email) ?>.
            </p>
            
            <div class="summary-box" style="text-align: left;">
                <div class="summary-row"><span>Booking Ref:</span> <strong>#RL-<?= rand(10000, 99999) ?></strong></div>
                <div class="summary-row"><span>Amount Paid:</span> <strong>Rs. <?= number_format($total_amount, 2) ?></strong></div>
            </div>

            <a href="index.php" style="display: block; width: 100%; padding: 1rem; background: #334155; color: white; text-align: center; text-decoration: none; border-radius: 10px; font-weight: 600;">Return to Home</a>
        </div>

    <?php else: ?>
        <h1 class="brand-title">Route<span>Link</span> Checkout</h1>

        <?php if (!empty($error_message)): ?>
            <div class="error-banner"><?= $error_message ?></div>
        <?php endif; ?>

        <div class="summary-box">
            <div class="summary-row">
                <span>Passenger</span>
                <span><?= htmlspecialchars($name) ?></span>
            </div>
            <div class="summary-row">
                <span>Total Seats</span>
                <span><?= isset($seat_count) ? $seat_count : 0 ?> Seats</span>
            </div>
            <div class="summary-row total">
                <span>Total Amount</span>
                <span>Rs. <?= number_format($total_amount, 2) ?></span>
            </div>
        </div>

        <form action="process_booking.php" method="POST">
            <input type="hidden" name="schedule_id" value="<?= htmlspecialchars($schedule_id) ?>">
            <input type="hidden" name="selected_seat_ids" value="<?= htmlspecialchars($seat_ids_str) ?>">
            <input type="hidden" name="total_amount" value="<?= htmlspecialchars($total_amount) ?>">
            <input type="hidden" name="name" value="<?= htmlspecialchars($name) ?>">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

            <h3 style="font-size: 1rem; margin-bottom: 1rem; color: var(--text-muted);">Payment Details</h3>

            <div class="input-group">
                <label>Cardholder Name</label>
                <input type="text" placeholder="J.P.R. Weerasingha" required>
            </div>

            <div class="input-group">
                <label>Card Number</label>
                <input type="text" placeholder="0000 0000 0000 0000" maxlength="19" required pattern="\d{4}\s?\d{4}\s?\d{4}\s?\d{4}">
            </div>

            <div class="row-grid">
                <div class="input-group">
                    <label>Expiry Date</label>
                    <input type="text" placeholder="MM/YY" maxlength="5" required>
                </div>
                <div class="input-group">
                    <label>CVV</label>
                    <input type="password" placeholder="•••" maxlength="3" required>
                </div>
            </div>

            <button type="submit" name="pay_now" class="btn-submit">Pay Rs. <?= number_format($total_amount, 2) ?> Securely</button>
        </form>
    <?php endif; ?>

</div>

</body>
</html>