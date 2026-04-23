<?php
// 1. Start the session at the very top
session_start();

// Include the database connection
require_once '../DB/db_connect.php'; 

// Ensure the required parameters are present in the URL
if (!isset($_GET['route_id']) || !isset($_GET['bus_id']) || !isset($_GET['travel_date'])) {
    header("Location: index.php");
    exit();
}

$route_id = intval($_GET['route_id']);
$bus_id = intval($_GET['bus_id']);
$travel_date = $_GET['travel_date'];

// ==========================================
// Fetch Logged-in User Details for Auto-fill
// ==========================================
$auto_name = "";
$auto_email = "";

$session_id = $_SESSION['u_id'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;

if ($session_id) {
    // Queries the singular 'user' table
    $user_query = "SELECT first_name, last_name, email FROM user WHERE u_id = ? LIMIT 1";
    $stmt_user = $conn->prepare($user_query);
    if ($stmt_user) {
        $stmt_user->bind_param("i", $session_id);
        $stmt_user->execute();
        $stmt_user->bind_result($f_name, $l_name, $u_email);
        if ($stmt_user->fetch()) {
            $auto_name = trim($f_name . ' ' . $l_name);
            $auto_email = $u_email;
        }
        $stmt_user->close();
    }
}

// ==========================================================
// 2. Get the schedule_id and Fare
// ==========================================================
$schedule_id = null;
$ticket_fare = 0;

$sched_query = "SELECT schedule_id, fare FROM schedules 
                WHERE route_id = ? AND bus_id = ? AND DATE(departure_time) = ? LIMIT 1";
$stmt_sched = $conn->prepare($sched_query);

if ($stmt_sched) {
    $stmt_sched->bind_param("iis", $route_id, $bus_id, $travel_date);
    $stmt_sched->execute();
    $stmt_sched->bind_result($schedule_id, $ticket_fare);
    $stmt_sched->fetch();
    $stmt_sched->close();
}

// 3. Fetch booked seats using schedule_id
$booked_seats = [];
if ($schedule_id) {
    $query = "SELECT seat_id FROM bookings WHERE schedule_id = ? AND status IN ('pending', 'confirmed')";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $schedule_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $booked_seats[] = $row['seat_id'];
        }
        $stmt->close();
    }
}

// ==========================================================
// 4. Fetch the ACTUAL layout from seats table (FIXED SORTING)
// ==========================================================
$bus_seats = [];
// CRITICAL FIX: Added CAST(seat_number AS UNSIGNED) so seats sort as 1, 2, 3... 10 instead of 1, 10, 11... 2
$seat_query = "SELECT seat_id, seat_number FROM seats WHERE bus_id = ? ORDER BY CAST(seat_number AS UNSIGNED) ASC";
$stmt_seats = $conn->prepare($seat_query);

if ($stmt_seats) {
    $stmt_seats->bind_param("i", $bus_id);
    $stmt_seats->execute();
    $result_seats = $stmt_seats->get_result();
    while ($row = $result_seats->fetch_assoc()) {
        $bus_seats[] = $row;
    }
    $stmt_seats->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seat Selection - RouteLink</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0f172a; --card-bg: #1e293b; --bus-chassis: #334155; 
            --text-main: #f8fafc; --text-muted: #94a3b8;
            --seat-avail: #10b981; --seat-booked: #ef4444; --seat-selected: #f59e0b; 
            --primary-btn: #3b82f6; --primary-hover: #2563eb;
            --transition: all 0.25s ease;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--bg-color); 
            color: var(--text-main); 
            min-height: 100vh; 
            display: flex; 
            /* THE FIX: Changed from 'center' to 'flex-start' so long 50-seat buses can scroll normally! */
            align-items: flex-start; 
            justify-content: center; 
            padding: 3rem 1rem; /* Added padding so it doesn't touch the very top of the screen */
        }

        /* Added height: fit-content so the container wraps the long bus properly */
        .main-container { display: grid; grid-template-columns: 1fr 400px; gap: 2.5rem; background: var(--card-bg); padding: 2.5rem; border-radius: 24px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); max-width: 1000px; width: 100%; height: fit-content;}
        
        .section-title { font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem; letter-spacing: -0.025em; }

        /* Bus UI */
        .bus-container { background: var(--bus-chassis); border: 8px solid #475569; border-radius: 60px 60px 20px 20px; padding: 40px 30px; width: fit-content; position: relative; margin: 0 auto; }
        .driver-area { display: flex; justify-content: flex-end; margin-bottom: 2rem; border-bottom: 2px dashed #64748b; padding-bottom: 1rem; }
        .steering-wheel { width: 35px; height: 35px; border: 4px solid #94a3b8; border-radius: 50%; position: relative; }
        .steering-wheel::after { content: ''; position: absolute; top: 50%; left: 0; right: 0; height: 3px; background: #94a3b8; }

        .seat-grid { display: grid; grid-template-columns: 45px 45px 40px 45px 45px; gap: 12px; }
        .seat { width: 45px; height: 48px; border-radius: 10px 10px 4px 4px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: var(--transition); box-shadow: inset 0 -3px 0 rgba(0,0,0,0.2); }
        .aisle { grid-column: 3; }
        .available { background: var(--seat-avail); }
        .booked { background: var(--seat-booked); opacity: 0.3; cursor: not-allowed; }
        .selected { background: var(--seat-selected); color: #000; transform: scale(1.05); }

        /* THE STICKY SIDEBAR FIX: This keeps the checkout form on screen while you scroll down the bus! */
        .sticky-sidebar {
            position: sticky;
            top: 2rem; /* Sticks 2rem from the top of the browser window */
        }

        /* Summary & Form */
        .summary-card { background: rgba(15, 23, 42, 0.4); padding: 1.5rem; border-radius: 16px; border: 1px solid #334155; margin-bottom: 1.5rem; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 0.8rem; font-size: 0.9rem; }
        .total-price { font-size: 1.2rem; color: var(--seat-selected); font-weight: 700; }
        
        .input-group { margin-bottom: 1rem; }
        .input-group label { display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.4rem; }
        .input-group input { width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid #334155; background: #0f172a; color: white; outline: none; transition: border-color 0.2s;}
        .input-group input:focus { border-color: var(--primary-btn); }
        .input-group input[readonly] { opacity: 0.7; cursor: not-allowed; }

        .btn-submit { width: 100%; background: var(--primary-btn); color: white; padding: 1rem; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: var(--transition); }
        .btn-submit:disabled { background: #334155; cursor: not-allowed; }
        
        .error-state { text-align: center; padding: 2rem; border: 1px dashed var(--seat-booked); border-radius: 15px; }

        @media (max-width: 850px) { 
            .main-container { grid-template-columns: 1fr; } 
            .sticky-sidebar { position: relative; top: 0; } /* Disables sticky on mobile */
        }
    </style>
</head>
<body>

<div class="main-container">
    <div class="layout-section">
        <h2 class="section-title">Select Your Seat</h2>

        <?php if (!$schedule_id): ?>
            <div class="error-state">
                <p style="color: var(--seat-booked); margin-bottom: 1rem;">Schedule not found for this date.</p>
                <a href="booking1page.php" style="color: var(--primary-btn);">← Back to Search</a>
            </div>
        <?php else: ?>
            <div class="bus-container">
                <div class="driver-area"><div class="steering-wheel"></div></div>
                <div class="seat-grid">
                    <?php
                    foreach ($bus_seats as $index => $seat) {
                        $s_id = $seat['seat_id'];
                        $s_num = htmlspecialchars($seat['seat_number']);
                        $is_booked = in_array($s_id, $booked_seats);
                        $class = $is_booked ? 'booked' : 'available';

                        echo "<div class='seat $class' data-id='$s_id' data-num='$s_num'>$s_num</div>";
                        
                        // 2-2 Layout logic (Aisle placement)
                        if (($index + 1) % 2 == 0 && ($index + 1) % 4 != 0) {
                            echo '<div class="aisle"></div>';
                        }
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="form-section">
        <div class="sticky-sidebar">
            <h2 class="section-title">Booking Details</h2>
            <div class="summary-card">
                <div class="summary-row"><span>Date:</span> <span><?= htmlspecialchars($travel_date) ?></span></div>
                <div class="summary-row"><span>Ticket Price:</span> <span>Rs. <?= number_format($ticket_fare, 2) ?></span></div>
                <div class="summary-row"><span>Seat(s) No:</span> <span id="display-seats" style="color:var(--seat-selected)">None</span></div>
                <hr style="border:0; border-top:1px solid #334155; margin: 10px 0;">
                <div class="summary-row"><span>Total:</span> <span class="total-price">Rs. <span id="display-total">0.00</span></span></div>
            </div>

            <form action="process_booking.php" method="POST">
                <input type="hidden" name="schedule_id" value="<?= htmlspecialchars($schedule_id) ?>">
                <input type="hidden" name="selected_seat_ids" id="input-seat-ids">
                <input type="hidden" name="total_amount" id="input-total-amount">

                <div class="input-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($auto_name) ?>" <?= $auto_name ? 'readonly' : 'required' ?>>
                </div>
                <div class="input-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($auto_email) ?>" <?= $auto_email ? 'readonly' : 'required' ?>>
                </div>

                <button type="submit" id="submit-btn" class="btn-submit" disabled>Proceed to Checkout</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const seats = document.querySelectorAll('.seat.available');
        const displaySeats = document.getElementById('display-seats');
        const displayTotal = document.getElementById('display-total');
        const inputSeatIds = document.getElementById('input-seat-ids');
        const inputTotal = document.getElementById('input-total-amount');
        const submitBtn = document.getElementById('submit-btn');
        
        const fare = <?= (float)$ticket_fare ?>;
        let selected = [];

        seats.forEach(seat => {
            seat.addEventListener('click', () => {
                const id = seat.dataset.id;
                // Parse the seat number as an integer so they sort correctly in the summary (e.g. 1, 2, 10 instead of 1, 10, 2)
                const num = parseInt(seat.dataset.num, 10);

                if (seat.classList.contains('selected')) {
                    seat.classList.remove('selected');
                    selected = selected.filter(s => s.id !== id);
                } else {
                    seat.classList.add('selected');
                    selected.push({ id, num });
                }

                // Sort numerically ascending
                const names = selected.map(s => s.num).sort((a, b) => a - b);
                const ids = selected.map(s => s.id);
                const total = selected.length * fare;

                displaySeats.textContent = names.length ? names.join(', ') : 'None';
                displayTotal.textContent = total.toLocaleString(undefined, {minimumFractionDigits: 2});
                inputSeatIds.value = ids.join(',');
                inputTotal.value = total;
                submitBtn.disabled = selected.length === 0;
            });
        });
    });
</script>
</body>
</html>