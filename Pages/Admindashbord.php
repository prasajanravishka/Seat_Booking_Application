<?php
session_start();
require_once '../DB/db_connect.php';

$message = ""; 

// ==========================================
// 1. HANDLE DELETE & CANCEL OPERATIONS
// ==========================================

// Cancel Booking directly from the Seat Map
if (isset($_POST['cancel_booking'])) {
    $booking_id = intval($_POST['booking_id']);
    if ($conn->query("DELETE FROM bookings WHERE booking_id = $booking_id")) {
        $message = "<div class='alert success'>Booking successfully cancelled! The seat is now available.</div>";
    } else {
        $message = "<div class='alert error'>Error cancelling booking: " . $conn->error . "</div>";
    }
}

// Delete Route
if (isset($_GET['delete_route'])) {
    $id = intval($_GET['delete_route']);
    if ($conn->query("DELETE FROM routes WHERE route_id = $id")) {
        $message = "<div class='alert success'>Route deleted successfully!</div>";
    }
}

// Delete Bus
if (isset($_GET['delete_bus'])) {
    $id = intval($_GET['delete_bus']);
    $conn->query("DELETE FROM seats WHERE bus_id = $id"); 
    if ($conn->query("DELETE FROM buses WHERE bus_id = $id")) {
        $message = "<div class='alert success'>Bus and its seats deleted!</div>";
    }
}

// Delete Schedule
if (isset($_GET['delete_schedule'])) {
    $id = intval($_GET['delete_schedule']);
    if ($conn->query("DELETE FROM schedules WHERE schedule_id = $id")) {
        $message = "<div class='alert success'>Schedule removed!</div>";
    }
}

// ==========================================
// 2. HANDLE CREATE & UPDATE OPERATIONS
// ==========================================

// ---- ROUTES ----
if (isset($_POST['add_route'])) {
    $stmt = $conn->prepare("INSERT INTO routes (departure_city, destination_city, distance_km) VALUES (?, ?, ?)");
    $stmt->bind_param("ssd", $_POST['departure_city'], $_POST['destination_city'], $_POST['distance_km']);
    $message = $stmt->execute() ? "<div class='alert success'>Route added!</div>" : "<div class='alert error'>Error: ".$conn->error."</div>";
    $stmt->close();
}
if (isset($_POST['update_route'])) {
    $stmt = $conn->prepare("UPDATE routes SET departure_city=?, destination_city=?, distance_km=? WHERE route_id=?");
    $stmt->bind_param("ssdi", $_POST['departure_city'], $_POST['destination_city'], $_POST['distance_km'], $_POST['route_id']);
    $message = $stmt->execute() ? "<div class='alert success'>Route updated successfully!</div>" : "<div class='alert error'>Error: ".$conn->error."</div>";
    $stmt->close();
}

// ---- BUSES ----
if (isset($_POST['update_bus'])) {
    $bus_id = intval($_POST['bus_id']);
    $new_capacity = intval($_POST['capacity']);
    
    // 1. Update the bus details
    $stmt = $conn->prepare("UPDATE buses SET bus_number=?, model_type=?, capacity=? WHERE bus_id=?");
    $stmt->bind_param("ssii", $_POST['bus_number'], $_POST['model_type'], $new_capacity, $bus_id);
    
    if ($stmt->execute()) {
        // 2. Adjust the seats table to match the new capacity
        $current_seats_query = $conn->query("SELECT COUNT(*) as current_cap FROM seats WHERE bus_id = $bus_id");
        $current_cap = $current_seats_query->fetch_assoc()['current_cap'];
        
        if ($new_capacity > $current_cap) {
            // Add new seats to the database
            $seat_stmt = $conn->prepare("INSERT INTO seats (bus_id, seat_number) VALUES (?, ?)");
            for ($i = $current_cap + 1; $i <= $new_capacity; $i++) {
                $s_num = strval($i);
                $seat_stmt->bind_param("is", $bus_id, $s_num);
                $seat_stmt->execute();
            }
        } elseif ($new_capacity < $current_cap) {
            // Remove excess seats from the database
            $conn->query("DELETE FROM seats WHERE bus_id = $bus_id AND CAST(seat_number AS UNSIGNED) > $new_capacity");
        }
        
        $message = "<div class='alert success'>Bus details and Seat Capacity updated successfully!</div>";
    } else {
        $message = "<div class='alert error'>Error: ".$conn->error."</div>";
    }
    $stmt->close();
}

// ---- SCHEDULES ----
if (isset($_POST['add_schedule'])) {
    $stmt = $conn->prepare("INSERT INTO schedules (bus_id, route_id, departure_time, arrival_time, fare) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $_POST['bus_id'], $_POST['route_id'], $_POST['departure_time'], $_POST['arrival_time'], $_POST['fare']);
    $message = $stmt->execute() ? "<div class='alert success'>Schedule created!</div>" : "<div class='alert error'>Error: ".$conn->error."</div>";
    $stmt->close();
}
if (isset($_POST['update_schedule'])) {
    $stmt = $conn->prepare("UPDATE schedules SET bus_id=?, route_id=?, departure_time=?, arrival_time=?, fare=? WHERE schedule_id=?");
    $stmt->bind_param("iisssi", $_POST['bus_id'], $_POST['route_id'], $_POST['departure_time'], $_POST['arrival_time'], $_POST['fare'], $_POST['schedule_id']);
    $message = $stmt->execute() ? "<div class='alert success'>Schedule updated!</div>" : "<div class='alert error'>Error: ".$conn->error."</div>";
    $stmt->close();
}

// ==========================================
// 3. FETCH STATS
// ==========================================
$totalBookings = $conn->query("SELECT COUNT(*) as total FROM bookings")->fetch_assoc()['total'] ?? 0;
$totalBuses = $conn->query("SELECT COUNT(*) as total FROM buses")->fetch_assoc()['total'] ?? 0;
$totalRoutes = $conn->query("SELECT COUNT(*) as total FROM routes")->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RouteLink | Full Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        :root {
            --primary: #2563eb; --primary-dark: #1d4ed8; --bg-color: #f1f5f9;
            --surface: #ffffff; --text-main: #0f172a; --text-muted: #64748b;
            --border: #e2e8f0; --success: #10b981; --danger: #ef4444; --warning: #f59e0b; --sidebar-width: 260px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: var(--bg-color); color: var(--text-main); display: flex; min-height: 100vh; scroll-behavior: smooth; }

        /* Sidebar */
        .sidebar { width: var(--sidebar-width); background: var(--surface); height: 100vh; position: fixed; border-right: 1px solid var(--border); padding: 24px 20px; z-index: 100; }
        .brand { display: flex; align-items: center; gap: 12px; margin-bottom: 40px; }
        .brand-icon { background: var(--primary); color: white; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .nav-links a { display: flex; align-items: center; gap: 12px; padding: 12px; color: var(--text-muted); text-decoration: none; border-radius: 8px; font-size: 14px; margin-bottom: 4px; transition: 0.2s; }
        .nav-links a:hover, .nav-links a.active { background: var(--primary); color: white; }

        /* Main Content */
        .main-content { margin-left: var(--sidebar-width); flex: 1; padding: 32px 40px; width: calc(100% - var(--sidebar-width)); }
        .section-panel { background: var(--surface); border-radius: 12px; border: 1px solid var(--border); padding: 24px; margin-bottom: 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 10px; }

        /* Stats */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; margin-bottom: 32px; }
        .stat-card { background: var(--surface); padding: 24px; border-radius: 12px; border: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; }

        /* Tables & Forms */
        .inline-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px; align-items: end; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid var(--border);}
        .form-control { padding: 10px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px; width: 100%; outline: none; }
        .form-control[readonly] { background-color: #e2e8f0; cursor: not-allowed; color: var(--text-muted); }
        .form-label { font-size: 12px; color: var(--text-muted); margin-bottom: 4px; display: block; font-weight: 500;} 
        .btn-primary { background: var(--primary); color: white; border: none; padding: 10px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; }
        .btn-update { background: var(--warning); color: white; border: none; padding: 10px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; }
        .btn-cancel { background: white; color: var(--text-main); border: 1px solid var(--border); padding: 10px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; text-decoration: none; display: inline-block; text-align: center;}
        
        table { width: 100%; border-collapse: collapse; margin-top: 25px; }
        th { text-align: left; padding: 12px; background: #f8fafc; font-size: 12px; color: var(--text-muted); }
        td { padding: 12px; border-bottom: 1px solid var(--border); font-size: 14px; }
        .btn-del { color: var(--danger); font-size: 20px; text-decoration: none; }
        .btn-edit { color: var(--primary); font-size: 20px; text-decoration: none; margin-right: 15px; }

        /* Seats */
        .seat-grid { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; padding: 20px; background: #f8fafc; border-radius: 8px; }
        .seat { width: 45px; height: 45px; border-radius: 6px; border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 13px; font-family: 'Inter', sans-serif;}
        .available { background: white; color: var(--text-main); }
        button.seat.booked { background: var(--danger); color: white; border-color: var(--danger); cursor: pointer; transition: 0.2s; }
        button.seat.booked:hover { background: #b91c1c; transform: scale(1.05); box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3); }

        .alert { padding: 15px; border-radius: 8px; margin-bottom: 24px; font-weight: 500; }
        .alert.success { background: #d1fae5; color: #065f46; border: 1px solid var(--success); }
        .alert.error { background: #fee2e2; color: #991b1b; border: 1px solid var(--danger); }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="brand">
            <div class="brand-icon"><i class='bx bx-bus'></i></div>
            <h2 style="font-size: 18px; font-weight: 700;">RouteLink Admin</h2>
        </div>
        <nav class="nav-links">
            <a href="#" class="active"><i class='bx bx-grid-alt'></i> Dashboard</a>
            <a href="#manage-routes"><i class='bx bx-map'></i> Routes</a>
            <a href="#manage-buses"><i class='bx bx-bus'></i> Buses</a>
            <a href="#manage-schedules"><i class='bx bx-time'></i> Schedules</a>
            <a href="#live-seats"><i class='bx bx-chair'></i> Live Seat Map</a>
        </nav>
    </aside>

    <main class="main-content">
        <header style="margin-bottom: 30px;">
            <h1>Overview Dashboard</h1>
            <p style="color: var(--text-muted);">Manage routes, fleet, and monitor live bookings.</p>
        </header>

        <?= $message; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div><p style="color:var(--text-muted); font-size: 14px;">Total Bookings</p><h3><?= $totalBookings ?></h3></div>
                <div class="stat-icon" style="background:#eff6ff; color:var(--primary);"><i class='bx bx-receipt'></i></div>
            </div>
            <div class="stat-card">
                <div><p style="color:var(--text-muted); font-size: 14px;">Active Buses</p><h3><?= $totalBuses ?></h3></div>
                <div class="stat-icon" style="background:#f0fdf4; color:var(--success);"><i class='bx bx-bus'></i></div>
            </div>
            <div class="stat-card">
                <div><p style="color:var(--text-muted); font-size: 14px;">Total Routes</p><h3><?= $totalRoutes ?></h3></div>
                <div class="stat-icon" style="background:#faf5ff; color:purple;"><i class='bx bx-map-alt'></i></div>
            </div>
        </div>

        <section id="manage-routes" class="section-panel">
            <div class="section-header"><h2>Manage Routes</h2></div>
            
            <?php 
            $edit_route_id = $_GET['edit_route'] ?? null;
            $edit_route_data = null;
            if ($edit_route_id) {
                $edit_route_data = $conn->query("SELECT * FROM routes WHERE route_id = " . intval($edit_route_id))->fetch_assoc();
            }
            ?>
            
            <form method="POST" class="inline-form" action="#manage-routes">
                <?php if($edit_route_id): ?>
                    <input type="hidden" name="route_id" value="<?= $edit_route_id ?>">
                <?php endif; ?>
                <div>
                    <label class="form-label">Departure City</label>
                    <input type="text" name="departure_city" placeholder="e.g. Colombo" class="form-control" value="<?= $edit_route_id ? $edit_route_data['departure_city'] : '' ?>" required>
                </div>
                <div>
                    <label class="form-label">Destination City</label>
                    <input type="text" name="destination_city" placeholder="e.g. Kandy" class="form-control" value="<?= $edit_route_id ? $edit_route_data['destination_city'] : '' ?>" required>
                </div>
                <div>
                    <label class="form-label">Distance (km)</label>
                    <input type="number" step="0.1" name="distance_km" placeholder="e.g. 115" class="form-control" value="<?= $edit_route_id ? $edit_route_data['distance_km'] : '' ?>" required>
                </div>
                
                <?php if($edit_route_id): ?>
                    <button type="submit" name="update_route" class="btn-update">Update Route</button>
                    <a href="Admindashbord.php#manage-routes" class="btn-cancel">Cancel</a>
                <?php else: ?>
                    <button type="submit" name="add_route" class="btn-primary">Add Route</button>
                <?php endif; ?>
            </form>
            
            <table>
                <thead><tr><th>From</th><th>To</th><th>Distance</th><th>Action</th></tr></thead>
                <tbody>
                    <?php $res = $conn->query("SELECT * FROM routes");
                    while($r = $res->fetch_assoc()): ?>
                    <tr>
                        <td><?= $r['departure_city'] ?></td><td><?= $r['destination_city'] ?></td><td><?= $r['distance_km'] ?> km</td>
                        <td>
                            <a href="?edit_route=<?= $r['route_id'] ?>#manage-routes" class="btn-edit"><i class='bx bx-edit'></i></a>
                            <a href="?delete_route=<?= $r['route_id'] ?>#manage-routes" onclick="return confirm('Delete route?')" class="btn-del"><i class='bx bx-trash'></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <section id="manage-buses" class="section-panel">
            <div class="section-header"><h2>Fleet Management</h2></div>
            
            <?php 
            $edit_bus_id = $_GET['edit_bus'] ?? null;
            $edit_bus_data = null;
            if ($edit_bus_id) {
                $edit_bus_data = $conn->query("SELECT * FROM buses WHERE bus_id = " . intval($edit_bus_id))->fetch_assoc();
            }
            ?>

            <form method="POST" class="inline-form" action="#manage-buses">
                <?php if($edit_bus_id): ?>
                    <input type="hidden" name="bus_id" value="<?= $edit_bus_id ?>">
                <?php endif; ?>
                <div>
                    <label class="form-label">Bus Plate Number</label>
                    <input type="text" name="bus_number" placeholder="Plate No." class="form-control" value="<?= $edit_bus_id ? $edit_bus_data['bus_number'] : '' ?>" required>
                </div>
                <div>
                    <label class="form-label">Model Type</label>
                    <select name="model_type" class="form-control">
                        <option <?= ($edit_bus_id && $edit_bus_data['model_type'] == 'Standard Non-AC') ? 'selected' : '' ?>>Standard Non-AC</option>
                        <option <?= ($edit_bus_id && $edit_bus_data['model_type'] == 'Luxury AC') ? 'selected' : '' ?>>Luxury AC</option>
                        <option <?= ($edit_bus_id && $edit_bus_data['model_type'] == 'Sleeper') ? 'selected' : '' ?>>Sleeper</option>
                    </select>
                </div>
                <div>
                      <label class="form-label">Total Seats</label>
                     <input type="number" name="capacity" placeholder="Capacity" class="form-control" value="<?= $edit_bus_id ? $edit_bus_data['capacity'] : '' ?>" required>
                </div>
                <?php if($edit_bus_id): ?>
                    <button type="submit" name="update_bus" class="btn-update">Update Bus</button>
                    <a href="Admindashbord.php#manage-buses" class="btn-cancel">Cancel</a>
                <?php else: ?>
                    <button type="submit" name="add_bus" class="btn-primary">Register Bus</button>
                <?php endif; ?>
            </form>
            
            <table>
                <thead><tr><th>Plate No.</th><th>Type</th><th>Seats</th><th>Action</th></tr></thead>
                <tbody>
                    <?php $res = $conn->query("SELECT * FROM buses");
                    while($b = $res->fetch_assoc()): ?>
                    <tr>
                        <td><?= $b['bus_number'] ?></td><td><?= $b['model_type'] ?></td><td><?= $b['capacity'] ?></td>
                        <td>
                            <a href="?edit_bus=<?= $b['bus_id'] ?>#manage-buses" class="btn-edit"><i class='bx bx-edit'></i></a>
                            <a href="?delete_bus=<?= $b['bus_id'] ?>#manage-buses" onclick="return confirm('Delete bus and all associated seats?')" class="btn-del"><i class='bx bx-trash'></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <section id="manage-schedules" class="section-panel">
            <div class="section-header"><h2>Trip Schedules</h2></div>
            
            <?php 
            $edit_sch_id = $_GET['edit_schedule'] ?? null;
            $edit_sch_data = null;
            if ($edit_sch_id) {
                $edit_sch_data = $conn->query("SELECT * FROM schedules WHERE schedule_id = " . intval($edit_sch_id))->fetch_assoc();
            }
            ?>

            <form method="POST" class="inline-form" action="#manage-schedules" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
                <?php if($edit_sch_id): ?>
                    <input type="hidden" name="schedule_id" value="<?= $edit_sch_id ?>">
                <?php endif; ?>
                <div>
                    <label class="form-label">Assign Bus</label>
                    <select name="bus_id" class="form-control" required>
                        <option value="">Select Bus</option>
                        <?php $buses = $conn->query("SELECT * FROM buses");
                        while($bus = $buses->fetch_assoc()) {
                            $sel = ($edit_sch_id && $edit_sch_data['bus_id'] == $bus['bus_id']) ? "selected" : "";
                            echo "<option value='{$bus['bus_id']}' $sel>{$bus['bus_number']}</option>";
                        } ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Select Route</label>
                    <select name="route_id" class="form-control" required>
                        <option value="">Select Route</option>
                        <?php $routes = $conn->query("SELECT * FROM routes");
                        while($rt = $routes->fetch_assoc()) {
                            $sel = ($edit_sch_id && $edit_sch_data['route_id'] == $rt['route_id']) ? "selected" : "";
                            echo "<option value='{$rt['route_id']}' $sel>{$rt['departure_city']} → {$rt['destination_city']}</option>"; 
                        } ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Departure Date & Time</label>
                    <input type="datetime-local" name="departure_time" class="form-control" value="<?= $edit_sch_id ? date('Y-m-d\TH:i', strtotime($edit_sch_data['departure_time'])) : '' ?>" required>
                </div>
                <div>
                    <label class="form-label">Arrival Date & Time</label>
                    <input type="datetime-local" name="arrival_time" class="form-control" value="<?= $edit_sch_id && !empty($edit_sch_data['arrival_time']) ? date('Y-m-d\TH:i', strtotime($edit_sch_data['arrival_time'])) : '' ?>" required>
                </div>
                <div>
                    <label class="form-label">Ticket Fare (Rs)</label>
                    <input type="number" name="fare" placeholder="Fare (Rs)" class="form-control" value="<?= $edit_sch_id ? $edit_sch_data['fare'] : '' ?>" required>
                </div>
                
                <?php if($edit_sch_id): ?>
                    <button type="submit" name="update_schedule" class="btn-update">Update Schedule</button>
                    <a href="Admindashbord.php#manage-schedules" class="btn-cancel">Cancel</a>
                <?php else: ?>
                    <button type="submit" name="add_schedule" class="btn-primary">Add Schedule</button>
                <?php endif; ?>
            </form>
            
            <table>
                <thead>
                    <tr>
                        <th>Bus</th>
                        <th>Route</th>
                        <th>Departure</th>
                        <th>Arrival</th>
                        <th>Fare</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $res = $conn->query("SELECT s.*, b.bus_number, r.departure_city, r.destination_city 
                                         FROM schedules s 
                                         JOIN buses b ON s.bus_id = b.bus_id 
                                         JOIN routes r ON s.route_id = r.route_id
                                         ORDER BY s.departure_time ASC");
                    while($s = $res->fetch_assoc()): ?>
                    <tr>
                        <td><?= $s['bus_number'] ?></td>
                        <td><?= $s['departure_city'] ?> → <?= $s['destination_city'] ?></td>
                        <td><?=   strtotime($s['departure_time']) && !empty($s['departure_time']) ? date('M d, Y h:i A', strtotime($s['departure_time'])) : 'N/A' ?></td>
                        <td><?= isset($s['arrival_time']) && !empty($s['arrival_time']) ? date('M d, Y h:i A', strtotime($s['arrival_time'])) : 'N/A' ?></td>
                        <td>Rs.<?= number_format($s['fare'], 2) ?></td>
                        <td>
                            <a href="?edit_schedule=<?= $s['schedule_id'] ?>#manage-schedules" class="btn-edit"><i class='bx bx-edit'></i></a>
                            <a href="?delete_schedule=<?= $s['schedule_id'] ?>#manage-schedules" onclick="return confirm('Delete this schedule?')" class="btn-del"><i class='bx bx-trash'></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <section id="live-seats" class="section-panel">
            <div class="section-header">
                <h2>Live Seat Map</h2>
                <form method="GET" style="display: flex; gap: 10px;">
                    <select name="schedule_id" class="form-control" onchange="this.form.submit()">
                        <option value="">Select Schedule to View</option>
                        <?php 
                        $sch_list = $conn->query("SELECT s.schedule_id, s.departure_time, r.departure_city, r.destination_city FROM schedules s JOIN routes r ON s.route_id = r.route_id");
                        while($sl = $sch_list->fetch_assoc()) {
                            $sel = (isset($_GET['schedule_id']) && $_GET['schedule_id'] == $sl['schedule_id']) ? "selected" : "";
                            $date_fmt = date('M d, h:i A', strtotime($sl['departure_time']));
                            echo "<option value='{$sl['schedule_id']}' $sel>{$sl['departure_city']} to {$sl['destination_city']} ($date_fmt)</option>";
                        }
                        ?>
                    </select>
                </form>
            </div>

            <?php 
            $current_sch = $_GET['schedule_id'] ?? null;
            if($current_sch):
                $sch_data = $conn->query("SELECT bus_id FROM schedules WHERE schedule_id = " . intval($current_sch))->fetch_assoc();
                $bid = $sch_data['bus_id'];
                
                $seats = $conn->query("SELECT st.seat_id, st.seat_number, bk.booking_id, 
                                     IF(bk.booking_id IS NULL, 'available', 'booked') as status 
                                     FROM seats st LEFT JOIN bookings bk ON st.seat_id = bk.seat_id AND bk.schedule_id = " . intval($current_sch) . " 
                                     WHERE st.bus_id = $bid ORDER BY CAST(st.seat_number AS UNSIGNED) ASC");
                
                echo '<div class="seat-grid">';
                while($st = $seats->fetch_assoc()) {
                    if ($st['status'] == 'booked') {
                        echo "<form method='POST' action='?schedule_id={$current_sch}#live-seats' style='margin:0;'>
                                <input type='hidden' name='booking_id' value='{$st['booking_id']}'>
                                <button type='submit' name='cancel_booking' class='seat booked' onclick=\"return confirm('Are you sure you want to cancel the booking for Seat {$st['seat_number']}?')\" title='Click to Cancel Booking'>
                                    {$st['seat_number']}
                                </button>
                              </form>";
                    } else {
                        echo "<div class='seat available'>{$st['seat_number']}</div>";
                    }
                }
                echo '</div>';
                
                echo '<div style="display: flex; gap: 15px; margin-top: 20px; justify-content: center; font-size: 13px; color: var(--text-muted);">
                        <span style="display: flex; align-items: center; gap: 5px;"><div style="width:15px; height:15px; background:white; border:1px solid #ccc; border-radius:3px;"></div> Available</span>
                        <span style="display: flex; align-items: center; gap: 5px;"><div style="width:15px; height:15px; background:var(--danger); border-radius:3px;"></div> Booked (Click to Cancel)</span>
                      </div>';
            else:
                echo "<p style='text-align:center; color:var(--text-muted)'>Please select a schedule from the dropdown to see live seat status.</p>";
            endif;
            ?>
        </section>
    </main>

</body>
</html>