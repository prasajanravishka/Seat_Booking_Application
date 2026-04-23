<?php
session_start();
require_once '../DB/db_connect.php';

$message = ""; // Variable to hold success/error messages

// ==========================================
// HANDLE FORM SUBMISSIONS (Database Inserts)
// ==========================================

// 1. Handle Add Route
if (isset($_POST['add_route'])) {
    $dep_city = $_POST['departure_city'];
    $dest_city = $_POST['destination_city'];
    $distance = $_POST['distance_km'];

    $stmt = $conn->prepare("INSERT INTO routes (departure_city, destination_city, distance_km) VALUES (?, ?, ?)");
    $stmt->bind_param("ssd", $dep_city, $dest_city, $distance);
    
    if ($stmt->execute()) {
        $message = "<div class='alert success'>Route added successfully!</div>";
    } else {
        $message = "<div class='alert error'>Error adding route: " . $conn->error . "</div>";
    }
    $stmt->close();
}

// 2. Handle Add Bus (and Auto-generate Seats)
if (isset($_POST['add_bus'])) {
    $bus_num = $_POST['bus_number'];
    $model = $_POST['model_type'];
    $capacity = $_POST['capacity'];

    $stmt = $conn->prepare("INSERT INTO buses (bus_number, model_type, capacity) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $bus_num, $model, $capacity);
    
    if ($stmt->execute()) {
        $new_bus_id = $conn->insert_id; // Get the ID of the bus we just created
        
        // Auto-generate seats for this bus in the 'seats' table
        $seat_stmt = $conn->prepare("INSERT INTO seats (bus_id, seat_number) VALUES (?, ?)");
        for ($i = 1; $i <= $capacity; $i++) {
            $seat_num = strval($i);
            $seat_stmt->bind_param("is", $new_bus_id, $seat_num);
            $seat_stmt->execute();
        }
        $seat_stmt->close();

        $message = "<div class='alert success'>Bus added and {$capacity} seats generated successfully!</div>";
    } else {
        $message = "<div class='alert error'>Error adding bus: " . $conn->error . "</div>";
    }
    $stmt->close();
}

// 3. Handle Add Schedule
if (isset($_POST['add_schedule'])) {
    $bus_id = $_POST['bus_id'];
    $route_id = $_POST['route_id'];
    $dep_time = $_POST['departure_time'];
    $arr_time = $_POST['arrival_time'];
    $fare = $_POST['fare'];

    $stmt = $conn->prepare("INSERT INTO schedules (bus_id, route_id, departure_time, arrival_time, fare) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $bus_id, $route_id, $dep_time, $arr_time, $fare);
    
    if ($stmt->execute()) {
        $message = "<div class='alert success'>Schedule added successfully!</div>";
    } else {
        $message = "<div class='alert error'>Error adding schedule: " . $conn->error . "</div>";
    }
    $stmt->close();
}

// ==========================================
// FETCH STATS FOR DASHBOARD
// ==========================================
$totalBookings = $conn->query("SELECT COUNT(*) as total FROM bookings")->fetch_assoc()['total'] ?? 0;
$totalBuses = $conn->query("SELECT COUNT(*) as total FROM buses")->fetch_assoc()['total'] ?? 0;
// Note: Assuming your routes table is 'bus_routes' based on previous context
$totalRoutes = $conn->query("SELECT COUNT(*) as total FROM routes")->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RouteLink | Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <style>
        /* CSS Reset & Variables */
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #f8fafc;
            --bg-color: #f1f5f9;
            --surface: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --success: #10b981;
            --danger: #ef4444;
            --sidebar-width: 260px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-main); display: flex; min-height: 100vh; overflow-x: hidden; scroll-behavior: smooth; }

        /* --- Sidebar Navigation --- */
        .sidebar { width: var(--sidebar-width); background: var(--surface); height: 100vh; position: fixed; top: 0; left: 0; border-right: 1px solid var(--border); padding: 24px 20px; display: flex; flex-direction: column; z-index: 100; }
        .brand { display: flex; align-items: center; gap: 12px; margin-bottom: 40px; padding: 0 10px; }
        .brand-icon { background: var(--primary); color: white; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .brand h2 { font-size: 20px; font-weight: 700; color: var(--text-main); letter-spacing: -0.5px; }
        .nav-label { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); font-weight: 600; margin-bottom: 12px; padding: 0 10px; }
        .nav-links { display: flex; flex-direction: column; gap: 4px; }
        .nav-links a { display: flex; align-items: center; gap: 12px; padding: 12px 16px; color: var(--text-muted); text-decoration: none; border-radius: 8px; font-weight: 500; font-size: 14px; transition: all 0.2s; }
        .nav-links a i { font-size: 20px; }
        .nav-links a:hover, .nav-links a.active { background: var(--primary); color: white; }

        /* --- Main Content Area --- */
        .main-content { margin-left: var(--sidebar-width); flex: 1; padding: 32px 40px; max-width: 1400px; }
        .top-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
        .page-title h1 { font-size: 24px; font-weight: 700; color: var(--text-main); margin-bottom: 4px;}
        .page-title p { font-size: 14px; color: var(--text-muted); }
        .admin-profile { display: flex; align-items: center; gap: 12px; background: var(--surface); padding: 8px 16px; border-radius: 30px; border: 1px solid var(--border); }

        /* Alerts */
        .alert { padding: 14px; border-radius: 8px; margin-bottom: 24px; font-size: 14px; font-weight: 500; }
        .alert.success { background-color: #d1fae5; color: #065f46; border: 1px solid #10b981; }
        .alert.error { background-color: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }

        /* --- Stat Cards --- */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; margin-bottom: 32px; }
        .stat-card { background: var(--surface); padding: 24px; border-radius: 12px; border: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .stat-info h3 { font-size: 14px; font-weight: 600; color: var(--text-muted); margin-bottom: 8px; }
        .stat-info p { font-size: 28px; font-weight: 700; color: var(--text-main); }
        .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
        .icon-blue { background: #eff6ff; color: var(--primary); }
        .icon-green { background: #f0fdf4; color: var(--success); }
        .icon-purple { background: #faf5ff; color: #a855f7; }

        /* --- Content Sections --- */
        .section-panel { background: var(--surface); border-radius: 12px; border: 1px solid var(--border); padding: 24px; margin-bottom: 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--border); }
        .section-header h2 { font-size: 18px; font-weight: 600; }

        .inline-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: end; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 13px; font-weight: 600; color: var(--text-muted); }
        .form-control { padding: 10px 14px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px; color: var(--text-main); outline: none; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }

        .btn { padding: 10px 16px; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); }
        
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 14px 16px; font-size: 13px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; border-bottom: 1px solid var(--border); background: var(--secondary); }
        td { padding: 16px; font-size: 14px; border-bottom: 1px solid var(--border); }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: capitalize; }
        .badge.booked { background: #fef2f2; color: var(--danger); border: 1px solid #fecaca; }
        .badge.available { background: #f0fdf4; color: var(--success); border: 1px solid #bbf7d0; }

        .seat-grid { display: flex; flex-wrap: wrap; gap: 12px; background: var(--secondary); padding: 24px; border-radius: 8px; border: 1px solid var(--border); justify-content: center;}
        .seat { width: 50px; height: 50px; border-radius: 8px; border: 1px solid rgba(0,0,0,0.1); font-weight: 600; font-size: 14px; display: flex; align-items: center; justify-content: center; cursor: pointer; }
        .seat.available { background: var(--surface); color: var(--text-main); border-color: var(--border);}
        .seat.booked { background: var(--danger); color: white; border-color: var(--danger); cursor: not-allowed; opacity: 0.8;}
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="brand">
            <div class="brand-icon"><i class='bx bx-bus'></i></div>
            <h2>RouteLink Admin</h2>
        </div>

        <div class="nav-label">Main Menu</div>
        <nav class="nav-links">
            <a href="#" class="active"><i class='bx bx-grid-alt'></i> Dashboard</a>
            <a href="#add-route"><i class='bx bx-map-pin'></i> Add Route</a>
            <a href="#add-bus"><i class='bx bx-bus'></i> Add Bus</a>
            <a href="#schedule"><i class='bx bx-calendar-plus'></i> Add Schedule</a>
            <a href="#bookings"><i class='bx bx-receipt'></i> Bookings</a>
            <a href="#seats"><i class='bx bx-chair'></i> Live Seat Map</a>
        </nav>
    </aside>

    <main class="main-content">
        
        <header class="top-header">
            <div class="page-title">
                <h1>Overview Dashboard</h1>
                <p>Welcome back, Admin. Manage your fleet and routes here.</p>
            </div>
            <div class="admin-profile">
                <span style="font-size: 14px; font-weight: 600;">System Admin</span>
            </div>
        </header>

        <?= $message; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Total Bookings</h3>
                    <p><?= $totalBookings ?></p>
                </div>
                <div class="stat-icon icon-blue"><i class='bx bx-ticket'></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Active Buses</h3>
                    <p><?= $totalBuses ?></p>
                </div>
                <div class="stat-icon icon-green"><i class='bx bx-bus'></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Active Routes</h3>
                    <p><?= $totalRoutes ?></p>
                </div>
                <div class="stat-icon icon-purple"><i class='bx bx-map-alt'></i></div>
            </div>
        </div>

        <div id="add-route" class="section-panel">
            <div class="section-header">
                <h2>Create New Route</h2>
            </div>
            <form method="POST" class="inline-form">
                <div class="form-group">
                    <label>Departure City</label>
                    <input type="text" name="departure_city" class="form-control" placeholder="e.g. New York" required>
                </div>
                <div class="form-group">
                    <label>Destination City</label>
                    <input type="text" name="destination_city" class="form-control" placeholder="e.g. Boston" required>
                </div>
                <div class="form-group">
                    <label>Distance (km)</label>
                    <input type="number" step="0.01" name="distance_km" class="form-control" placeholder="350.50" required>
                </div>
                <div class="form-group" style="justify-content: flex-end;">
                    <button type="submit" name="add_route" class="btn btn-primary"><i class='bx bx-map'></i> Save Route</button>
                </div>
            </form>
        </div>

        <div id="add-bus" class="section-panel">
            <div class="section-header">
                <h2>Register New Bus</h2>
            </div>
            <form method="POST" class="inline-form">
                <div class="form-group">
                    <label>Bus Number (License Plate)</label>
                    <input type="text" name="bus_number" class="form-control" placeholder="e.g. NY-1234" required>
                </div>
                <div class="form-group">
                    <label>Model Type</label>
                    <select name="model_type" class="form-control" required>
                        <option value="Standard Non-AC">Standard Non-AC</option>
                        <option value="Volvo AC">Volvo AC</option>
                        <option value="AC Sleeper">AC Sleeper</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Seating Capacity</label>
                    <input type="number" name="capacity" class="form-control" placeholder="e.g. 40" required>
                </div>
                <div class="form-group" style="justify-content: flex-end;">
                    <button type="submit" name="add_bus" class="btn btn-primary"><i class='bx bx-bus'></i> Register Bus</button>
                </div>
            </form>
        </div>

        <div id="schedule" class="section-panel">
            <div class="section-header">
                <h2>Create Trip Schedule</h2>
            </div>
            <form method="POST" class="inline-form">
                <div class="form-group">
                    <label>Assign Bus</label>
                    <select name="bus_id" class="form-control" required>
                        <option value="" disabled selected>Select Bus</option>
                        <?php
                        $buses = $conn->query("SELECT * FROM buses");
                        if($buses) {
                            while($bus = $buses->fetch_assoc()){
                                echo "<option value='{$bus['bus_id']}'>{$bus['bus_number']} ({$bus['capacity']} seats)</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Assign Route</label>
                    <select name="route_id" class="form-control" required>
                        <option value="" disabled selected>Select Route</option>
                        <?php
                        $routes = $conn->query("SELECT * FROM routes");
                        if($routes) {
                            while($route = $routes->fetch_assoc()){
                                echo "<option value='{$route['route_id']}'>{$route['departure_city']} → {$route['destination_city']}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Departure Time</label>
                    <input type="datetime-local" name="departure_time" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Arrival Time</label>
                    <input type="datetime-local" name="arrival_time" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Base Fare (Rs)</label>
                    <input type="number" step="0.01" name="fare" class="form-control" placeholder="1500.00" required>
                </div>
                <div class="form-group" style="justify-content: flex-end;">
                    <button type="submit" name="add_schedule" class="btn btn-primary"><i class='bx bx-plus-circle'></i> Add Schedule</button>
                </div>
            </form>
        </div>

        <div id="bookings" class="section-panel">
            <div class="section-header">
                <h2>Recent Bookings</h2>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Passenger Name</th>
                            <th>Seat No.</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $bookings_query = "
                        SELECT bk.passenger_name, st.seat_number, bk.status
                        FROM bookings bk
                        JOIN seats st ON bk.seat_id = st.seat_id LIMIT 10
                        ";
                        $bookings = $conn->query($bookings_query);

                        if($bookings && $bookings->num_rows > 0) {
                            while($bk = $bookings->fetch_assoc()){
                                $statusClass = strtolower($bk['status']) == 'booked' ? 'booked' : 'available';
                                echo "<tr>
                                    <td><strong>{$bk['passenger_name']}</strong></td>
                                    <td>Seat {$bk['seat_number']}</td>
                                    <td><span class='badge {$statusClass}'>{$bk['status']}</span></td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3' style='text-align:center; color: var(--text-muted);'>No recent bookings found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="seats" class="section-panel">
            <div class="section-header">
                <h2>Live Seat Management</h2>
                <div style="display: flex; gap: 10px; font-size: 13px; color: var(--text-muted);">
                    <span style="display: flex; align-items: center; gap: 4px;"><div style="width: 12px; height: 12px; background: var(--surface); border: 1px solid var(--border); border-radius: 3px;"></div> Available</span>
                    <span style="display: flex; align-items: center; gap: 4px;"><div style="width: 12px; height: 12px; background: var(--danger); border-radius: 3px;"></div> Booked</span>
                </div>
            </div>
            
            <?php
            $schedule_id = $_GET['schedule_id'] ?? 1; // You can dynamically pass this via URL, e.g., admin.php?schedule_id=2
            
            $schedule_data = $conn->query("SELECT bus_id FROM schedules WHERE schedule_id=$schedule_id");
            $bus_id = ($schedule_data && $schedule_data->num_rows > 0) ? $schedule_data->fetch_assoc()['bus_id'] : 0;

            if ($bus_id > 0) {
                $seats_query = "
                SELECT st.seat_id, st.seat_number,
                IF(bk.status IS NULL, 'available', 'booked') as status
                FROM seats st
                LEFT JOIN bookings bk ON st.seat_id = bk.seat_id AND bk.schedule_id = $schedule_id
                WHERE st.bus_id = $bus_id
                ORDER BY CAST(st.seat_number AS UNSIGNED) ASC
                ";
                
                $seats = $conn->query($seats_query);
                
                echo '<div class="seat-grid">';
                if($seats && $seats->num_rows > 0) {
                    while($seat = $seats->fetch_assoc()): 
                        $disabled = ($seat['status'] == 'booked') ? 'disabled' : '';
                ?>
                        <form method="POST" style="margin: 0;">
                            <input type="hidden" name="seat_id" value="<?= $seat['seat_id'] ?>">
                            <input type="hidden" name="schedule_id" value="<?= $schedule_id ?>">
                            <button type="submit" name="toggle_seat" class="seat <?= $seat['status'] ?>" <?= $disabled ?> title="Seat <?= $seat['seat_number'] ?>">
                                <?= $seat['seat_number'] ?>
                            </button>
                        </form>
                <?php 
                    endwhile; 
                } else {
                    echo "<p style='color: var(--text-muted);'>No seats configured for this bus yet.</p>";
                }
                echo '</div>';
            } else {
                echo "<p style='text-align:center; padding: 20px; color: var(--text-muted); background: var(--secondary); border-radius: 8px;'>No active schedule found. Add a schedule first to view seats.</p>";
            }
            ?>
        </div>

    </main>

</body>
</html>