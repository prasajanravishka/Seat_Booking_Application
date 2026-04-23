<?php
session_start();
require_once '../DB/db_connect.php';

$booking_success = false;
$error_message = "";

// Variables to hold display data
$dep_city = $dest_city = $bus_number = $bus_type = $formatted_date = $seat_numbers_str = "";

// Generate a random booking reference number
$booking_ref = "RL-" . rand(10000, 99999);

// ==========================================================
// 1. Handle Final Booking Submission & Database Insert
// ==========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_now'])) {
    
    $schedule_id = $_POST['schedule_id'];
    $seat_ids_str = $_POST['selected_seat_ids'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    
    $session_id = $_SESSION['u_id'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? NULL;
    $seat_ids = explode(',', $seat_ids_str);
    
    // Ensure a user ID exists to prevent database crash
    if (!$session_id) {
        $user_check = $conn->query("SELECT u_id FROM user LIMIT 1");
        if ($user_check && $user_check->num_rows > 0) {
            $session_id = $user_check->fetch_assoc()['u_id'];
        } else {
            $conn->query("INSERT INTO user (first_name, last_name, email) VALUES ('Guest', 'User', 'guest@example.com')");
            $session_id = $conn->insert_id;
        }
    }
    
    $conn->begin_transaction();
    
    try {
        $insert_query = "INSERT INTO bookings (u_id, schedule_id, seat_id, status) VALUES (?, ?, ?, 'confirmed')";
        $stmt = $conn->prepare($insert_query);
        
        if (!$stmt) {
            throw new \Exception("Prepare failed: " . $conn->error);
        }

        foreach ($seat_ids as $seat_id) {
            $s_id_int = (int)$schedule_id;
            $seat_int = (int)$seat_id;
            
            $stmt->bind_param("iii", $session_id, $s_id_int, $seat_int);
            if (!$stmt->execute()) {
                 throw new \Exception("Execution failed: " . $stmt->error);
            }
        }
        
        $conn->commit();
        $stmt->close();
        $booking_success = true;
        
        // ==========================================================
        // 2. FETCH DETAILS FOR THE TICKET DISPLAY
        // ==========================================================
        
        $stmt_trip = $conn->prepare("
            SELECT r.departure_city, r.destination_city, b.bus_number, b.model_type, s.departure_time 
            FROM schedules s 
            JOIN routes r ON s.route_id = r.route_id 
            JOIN buses b ON s.bus_id = b.bus_id 
            WHERE s.schedule_id = ?
        ");
        $stmt_trip->bind_param("i", $schedule_id);
        $stmt_trip->execute();
        $stmt_trip->bind_result($dep_city, $dest_city, $bus_number, $bus_type, $dep_time);
        if ($stmt_trip->fetch()) {
            $formatted_date = date('F d, Y \a\t h:i A', strtotime($dep_time));
        }
        $stmt_trip->close();

        // Fetch Actual Seat Numbers
        $seat_numbers = [];
        $safe_seat_ids = implode(',', array_map('intval', $seat_ids));
        $res_seats = $conn->query("SELECT seat_number FROM seats WHERE seat_id IN ($safe_seat_ids)");
        while ($r = $res_seats->fetch_assoc()) {
            $seat_numbers[] = $r['seat_number'];
        }
        
        // Sort seats nicely for the ticket
        sort($seat_numbers, SORT_NUMERIC);
        $seat_numbers_str = implode(', ', $seat_numbers);

    } catch (\Exception $e) {
        $conn->rollback();
        $error_message = "Booking failed! Error: " . $e->getMessage();
    }
} 
// ==========================================================
// 3. Arriving from Page 2 (Seat Selection Review)
// ==========================================================
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_seat_ids'])) {
    $schedule_id = $_POST['schedule_id'];
    $seat_ids_str = $_POST['selected_seat_ids'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    
    $seat_count = count(explode(',', $seat_ids_str));
} else {
    header("Location: ../index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Booking - RouteLink</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Syne:wght@700&display=swap" rel="stylesheet">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <style>
        :root {
            --bg-color: #0f172a; 
            --card-bg: #1e293b; 
            --text-main: #f8fafc; 
            --text-muted: #94a3b8;
            --primary-btn: #3b82f6; 
            --primary-hover: #2563eb;
            --success: #10b981;
            --success-hover: #059669;
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

        .summary-box {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.8rem;
            font-size: 0.95rem;
        }

        .summary-row:last-child { margin-bottom: 0; }

        .summary-row.total {
            border-top: 1px dashed var(--border);
            padding-top: 1rem;
            margin-top: 1rem;
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--primary-btn);
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

        .btn-download {
            background: var(--success);
            margin-top: 0;
            margin-bottom: 15px;
        }
        .btn-download:hover { background: var(--success-hover); }

        .btn-home {
            display: block; width: 100%; padding: 1rem; 
            background: transparent; color: var(--text-muted); 
            text-align: center; text-decoration: none; 
            border-radius: 10px; font-weight: 600;
            border: 1px solid var(--border);
        }
        .btn-home:hover { background: rgba(255,255,255,0.05); color: white;}

        .success-state { text-align: center; }
        .success-icon {
            width: 70px; height: 70px;
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem; margin: 0 auto 1.5rem auto;
        }

        .error-banner {
            background: rgba(239, 68, 68, 0.1); color: #ff9999;
            padding: 1rem; border-radius: 8px; border: 1px solid var(--error);
            margin-bottom: 1.5rem; font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="checkout-container">
    
    <?php if ($booking_success): ?>
        <div class="success-state">
            <div class="success-icon">✓</div>
            <h2 style="margin-bottom: 10px; font-family: 'Syne';">Booking Successful!</h2>
            <p style="color: var(--text-muted); margin-bottom: 2rem; line-height: 1.6;">
                Thank you, <?= htmlspecialchars($name) ?>. Your seats have been successfully reserved. 
                Please download your ticket below.
            </p>
            
            <div id="ticket-content" style="background-color: #1e293b; padding: 25px; border-radius: 12px; margin-bottom: 25px; border: 1px solid #334155; text-align: left; font-family: 'Inter', sans-serif;">
                
                <h3 style="font-family: 'Syne', sans-serif; color: #3b82f6; margin-top: 0; margin-bottom: 20px; text-align: center; font-size: 1.5rem; border-bottom: 1px dashed #475569; padding-bottom: 15px;">RouteLink E-Ticket</h3>
                
                <table style="width: 100%; border-collapse: collapse; font-size: 15px;">
                    <tr>
                        <td style="padding: 10px 0; color: #94a3b8;">Booking Ref:</td>
                        <td style="padding: 10px 0; color: #ffffff; text-align: right; font-weight: bold;"><?= $booking_ref ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; color: #94a3b8;">Passenger:</td>
                        <td style="padding: 10px 0; color: #ffffff; text-align: right;"><?= htmlspecialchars($name) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; color: #94a3b8;">Route:</td>
                        <td style="padding: 10px 0; color: #ffffff; text-align: right; font-weight: 500;"><?= htmlspecialchars($dep_city) ?> to <?= htmlspecialchars($dest_city) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; color: #94a3b8;">Date & Time:</td>
                        <td style="padding: 10px 0; color: #ffffff; text-align: right;"><?= htmlspecialchars($formatted_date) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; color: #94a3b8; border-bottom: 1px dashed #475569; padding-bottom: 15px;">Bus Assigned:</td>
                        <td style="padding: 10px 0; color: #ffffff; text-align: right; border-bottom: 1px dashed #475569; padding-bottom: 15px;"><?= htmlspecialchars($bus_number) ?> (<?= htmlspecialchars($bus_type) ?>)</td>
                    </tr>
                    <tr>
                        <td style="padding: 15px 0 10px 0; color: #94a3b8;">Seat Number(s):</td>
                        <td style="padding: 15px 0 10px 0; color: #3b82f6; text-align: right; font-weight: bold; font-size: 1.2rem;"><?= htmlspecialchars($seat_numbers_str) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0; color: #94a3b8;">Status:</td>
                        <td style="padding: 5px 0; color: #10b981; text-align: right; font-weight: bold;">Confirmed & Paid ✓</td>
                    </tr>
                </table>

            </div>
            
            <button onclick="downloadImage()" class="btn-submit btn-download">⬇ Download E-Ticket (Image)</button>
            <a href="../index.php" class="btn-home">Return to Home</a>
        </div>

    <?php else: ?>
        <h1 class="brand-title">Route<span>Link</span> Booking</h1>

        <?php if (!empty($error_message)): ?>
            <div class="error-banner"><?= $error_message ?></div>
        <?php endif; ?>

        <div class="summary-box">
            <div class="summary-row">
                <span>Passenger</span>
                <span><?= htmlspecialchars($name) ?></span>
            </div>
            <div class="summary-row">
                <span>Email</span>
                <span><?= htmlspecialchars($email) ?></span>
            </div>
            <div class="summary-row total">
                <span>Total Seats Reserved</span>
                <span><?= isset($seat_count) ? $seat_count : 0 ?> Seats</span>
            </div>
        </div>

        <form action="" method="POST">
            <input type="hidden" name="schedule_id" value="<?= htmlspecialchars($schedule_id) ?>">
            <input type="hidden" name="selected_seat_ids" value="<?= htmlspecialchars($seat_ids_str) ?>">
            <input type="hidden" name="name" value="<?= htmlspecialchars($name) ?>">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

            <p style="font-size: 0.9rem; color: var(--text-muted); text-align: center; margin-bottom: 1rem;">
                Please review your reservation details above. Click confirm to lock in your seats.
            </p>

            <button type="submit" name="book_now" class="btn-submit">Confirm Booking</button>
        </form>
    <?php endif; ?>

</div>

<script>
    function downloadImage() {
        // Select the ticket block
        const element = document.getElementById('ticket-content');
        
        // Take a "screenshot" of the div
        html2canvas(element, {
            scale: 3, // High resolution
            backgroundColor: '#1e293b', // Force the exact background color
            useCORS: true 
        }).then(canvas => {
            // Create a temporary link to trigger the download
            const link = document.createElement('a');
            link.download = '<?= $booking_ref ?>_Ticket.png'; // Saves as a PNG image
            link.href = canvas.toDataURL('image/png');
            link.click();
        });
    }
</script>

</body>
</html>