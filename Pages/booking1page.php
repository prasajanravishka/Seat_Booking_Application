<?php
// 1. Start the session at the very top
session_start();

// SECURITY LOCK: If there is no session ID, kick them out!
if (!isset($_SESSION['u_id'])) {
    header("Location: Pages/logging_page.php");
    exit();
}

// Include the database connection
require_once '../DB/db_connect.php'; 

// Fetch all available routes from the database
$route_sql = "SELECT route_id, departure_city, destination_city, distance_km FROM routes";
$route_result = $conn->query($route_sql);

// Extract routes into an array to populate our two separate dropdowns
$routes_data = [];
$departures = [];
$destinations = [];

if ($route_result && $route_result->num_rows > 0) {
    while($row = $route_result->fetch_assoc()) {
        $routes_data[] = $row;
        $departures[] = $row['departure_city'];
        $destinations[] = $row['destination_city'];
    }
}
// Remove duplicates
$departures = array_unique($departures);
$destinations = array_unique($destinations);

// Fetch all available buses
$bus_sql = "SELECT bus_id, bus_number, model_type, capacity FROM buses";
$bus_result = $conn->query($bus_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Bus Route</title>
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

        * { box-sizing: border-box; }

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
        
        .form-panel { background: var(--card); display: flex; flex-direction: column; overflow: hidden; }
        .tab-bar { display: grid; grid-template-columns: 1fr; border-bottom: 1px solid var(--border); }
        .tab-btn { padding: 20px; background: none; border: none; color: var(--muted); font-family: 'Syne', sans-serif; font-size: 14px; font-weight: 600; letter-spacing: 0.05em; cursor: pointer; position: relative; transition: color 0.25s; }
        
        .form-container { flex: 1; padding: 36px 40px; overflow-y: auto; }
        
        /* Error Message Styling */
        .error-banner {
            display: none;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--error);
            color: #ff9999;
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            line-height: 1.4;
            animation: shake 0.4s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }
        .field { margin-bottom: 14px; position: relative; }
        .field label { display: block; font-size: 11px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted); margin-bottom: 7px; }
        .field input, .field select {
            width: 100%; background: var(--surface); border: 1px solid var(--border);
            border-radius: 8px; padding: 11px 14px; font-family: 'Mulish', sans-serif;
            font-size: 13.5px; color: var(--text); outline: none; transition: all 0.2s;
        }
        .field input:focus, .field select:focus { border-color: var(--border-active); box-shadow: 0 0 0 3px var(--primary-glow); }

        /* ── Custom Calendar Trigger ── */
        .cal-trigger {
            width: 100%; background: var(--surface); border: 1px solid var(--border);
            border-radius: 8px; padding: 11px 14px; font-family: 'Mulish', sans-serif;
            font-size: 13.5px; color: var(--text); cursor: pointer;
            display: flex; align-items: center; justify-content: space-between;
            transition: all 0.2s; user-select: none;
        }
        .cal-trigger:hover { border-color: var(--border-active); box-shadow: 0 0 0 3px var(--primary-glow); }
        .cal-trigger.open  { border-color: var(--border-active); box-shadow: 0 0 0 3px var(--primary-glow); }
        .cal-trigger svg   { flex-shrink: 0; opacity: 0.45; }

        /* ── Calendar Popup ── */
        .cal-popup {
            display: none;
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            width: 100%;
            min-width: 260px;
            background: var(--surface);
            border: 1px solid var(--border-active);
            border-radius: 12px;
            padding: 16px;
            z-index: 999;
            box-shadow: 0 20px 60px rgba(0,0,0,0.6), 0 0 0 1px rgba(58,111,255,0.1);
            animation: calDrop 0.2s cubic-bezier(0.16,1,0.3,1) both;
        }
        .cal-popup.visible { display: block; }

        @keyframes calDrop {
            from { opacity: 0; transform: translateY(-8px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0)  scale(1); }
        }

        .cal-header {
            display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;
        }
        .cal-nav {
            background: var(--card); border: 1px solid var(--border); border-radius: 6px;
            width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;
            cursor: pointer; color: var(--muted); font-size: 17px; line-height: 1;
            transition: all 0.15s; flex-shrink: 0;
        }
        .cal-nav:hover { border-color: var(--primary); color: var(--primary-light); }

        .cal-month-label {
            font-family: 'Syne', sans-serif; font-size: 13px; font-weight: 700; letter-spacing: 0.02em;
        }

        .cal-grid {
            display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px;
        }
        .cal-day-name {
            text-align: center; font-size: 10px; font-weight: 700; letter-spacing: 0.08em;
            color: var(--muted); padding: 3px 0 8px; text-transform: uppercase;
        }
        .cal-day {
            text-align: center; font-size: 12px; padding: 6px 0;
            border-radius: 6px; cursor: pointer; color: var(--text); transition: all 0.12s;
        }
        .cal-day:hover:not(.disabled):not(.empty) {
            background: rgba(58,111,255,0.15); color: var(--primary-light);
        }
        .cal-day.selected {
            background: var(--primary); color: #fff;
            box-shadow: 0 0 14px var(--primary-glow); font-weight: 700;
        }
        .cal-day.today:not(.selected) {
            color: var(--primary-light); font-weight: 700;
            outline: 1px solid rgba(58,111,255,0.4); outline-offset: -1px;
            border-radius: 6px;
        }
        .cal-day.disabled { color: var(--muted2); cursor: not-allowed; }
        .cal-day.empty    { cursor: default; }

        /* ── Submit Button ── */
        .btn-primary {
            width: 100%; padding: 13px; background: var(--primary); border: none;
            border-radius: 8px; color: #fff; font-family: 'Syne', sans-serif;
            font-size: 14px; font-weight: 700; letter-spacing: 0.05em; cursor: pointer;
            transition: all 0.25s; box-shadow: 0 4px 20px var(--primary-glow); margin-top: 10px;
        }
        .btn-primary:hover { background: var(--primary-light); transform: translateY(-1px); }

        @media (max-width: 700px) {
          .auth-wrapper { grid-template-columns: 1fr; }
          .brand-panel  { display: none; }
        }
    </style>
</head>
<body>

    <div class="auth-wrapper">

        <!-- ── Brand / Left Panel ── -->
        <div class="brand-panel">
            <div>
                <div class="brand-logo">
                    <div class="logo-icon">🚌</div>
                    <div class="logo-name">Route<span>Link</span></div>
                </div>
                
                <div class="seat-deco">
                    <span class="taken"></span><span></span><span class="gold"></span><span></span><span></span>
                    <span></span><span class="taken"></span><span></span><span></span><span class="taken"></span>
                </div>

                <h1 class="brand-title">Your Journey <br><mark>Starts Here</mark></h1>
                <p class="brand-desc">Select your desired route and travel date to proceed.</p>
            </div>
        </div>

        <!-- ── Form / Right Panel ── -->
        <div class="form-panel">
            <div class="tab-bar">
                <button class="tab-btn">Find Your Bus</button>
            </div>
            
            <div class="form-container">
                <h2 style="font-family:'Syne';font-size:1.35rem;margin:0 0 6px;">Trip Details</h2>
                <p style="font-size:13px;color:var(--muted);margin:0 0 28px;">Where are you heading today?</p>
                
                <!-- Route validation error -->
                <div id="error-message" class="error-banner">
                    ⚠️ Sorry, there is no direct route available between these cities. Please try a different combination.
                </div>

                <form id="route-form" action="booking2page.php" method="GET">

                    <!-- Departure / Destination -->
                    <div class="field-row">
                        <div class="field">
                            <label for="departure">Departure <span style="color:var(--primary-light)">*</span></label>
                            <select id="departure" required>
                                <option value="" disabled selected>-- From --</option>
                                <?php foreach ($departures as $city): ?>
                                    <option value="<?= htmlspecialchars($city) ?>"><?= htmlspecialchars($city) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field">
                            <label for="destination">Destination <span style="color:var(--primary-light)">*</span></label>
                            <select id="destination" required>
                                <option value="" disabled selected>-- To --</option>
                                <?php foreach ($destinations as $city): ?>
                                    <option value="<?= htmlspecialchars($city) ?>"><?= htmlspecialchars($city) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Hidden route_id filled by JS -->
                    <input type="hidden" name="route_id" id="hidden_route_id" required>

                    <!-- Bus selector -->
                    <div class="field">
                        <label for="bus">Select Bus &amp; Type <span style="color:var(--primary-light)">*</span></label>
                        <select name="bus_id" id="bus" required>
                            <option value="" disabled selected>-- Choose a bus --</option>
                            <?php 
                            if ($bus_result && $bus_result->num_rows > 0) {
                                while ($bus_row = $bus_result->fetch_assoc()) {
                                    echo "<option value='{$bus_row['bus_id']}'>{$bus_row['model_type']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <!-- ── Custom Calendar Date Picker ── -->
                    <div class="field" id="dateField">
                        <label>Travel Date <span style="color:var(--primary-light)">*</span></label>

                        <!-- Clickable trigger -->
                        <div class="cal-trigger" id="calTrigger" role="button" tabindex="0" aria-haspopup="true" aria-expanded="false">
                            <span id="triggerText" style="color:var(--muted)">Select a date</span>
                            <svg width="15" height="15" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                                <rect x="2" y="4" width="12" height="10" rx="2"/>
                                <line x1="5"  y1="1.5" x2="5"  y2="5.5"/>
                                <line x1="11" y1="1.5" x2="11" y2="5.5"/>
                                <line x1="2"  y1="8"   x2="14" y2="8"/>
                            </svg>
                        </div>

                        <!-- Dropdown calendar -->
                        <div class="cal-popup" id="calPopup" role="dialog" aria-label="Date picker">
                            <div class="cal-header">
                                <div class="cal-nav" id="prevBtn" role="button" tabindex="0" title="Previous month">&#8249;</div>
                                <div class="cal-month-label" id="calMonthLabel"></div>
                                <div class="cal-nav" id="nextBtn" role="button" tabindex="0" title="Next month">&#8250;</div>
                            </div>
                            <div class="cal-grid" id="calGrid"></div>
                        </div>

                        <!-- Actual hidden input that gets submitted with the form -->
                        <input type="hidden" name="travel_date" id="travel_date" required>
                    </div>

                    <button type="submit" class="btn-primary">Continue to Seat Selection →</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {

        /* ─────────────────────────────────────────────
           1.  ROUTE VALIDATION (unchanged logic)
        ───────────────────────────────────────────── */
        const routesData = <?= json_encode(array_values($routes_data)); ?>;
        const depSelect  = document.getElementById('departure');
        const destSelect = document.getElementById('destination');
        const routeInput = document.getElementById('hidden_route_id');
        const errorMsg   = document.getElementById('error-message');
        const form       = document.getElementById('route-form');

        function validateRoute() {
            const dep  = depSelect.value;
            const dest = destSelect.value;
            if (dep && dest) {
                const matched = routesData.find(r => r.departure_city === dep && r.destination_city === dest);
                if (matched) {
                    routeInput.value       = matched.route_id;
                    errorMsg.style.display = 'none';
                } else {
                    routeInput.value       = '';
                    errorMsg.style.display = 'block';
                }
            }
        }

        depSelect.addEventListener('change',  validateRoute);
        destSelect.addEventListener('change', validateRoute);

        form.addEventListener('submit', function (e) {
            if (!routeInput.value) {
                e.preventDefault();
                errorMsg.style.display = 'block';
                errorMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });


        /* ─────────────────────────────────────────────
           2.  CUSTOM CALENDAR PICKER
        ───────────────────────────────────────────── */
        const MONTH_NAMES = [
            'January','February','March','April','May','June',
            'July','August','September','October','November','December'
        ];
        const DAY_NAMES = ['Su','Mo','Tu','We','Th','Fr','Sa'];

        // Minimum selectable date = tomorrow
        const today   = new Date(); today.setHours(0,0,0,0);
        const minDate = new Date(today); minDate.setDate(minDate.getDate() + 1);

        let viewYear     = minDate.getFullYear();
        let viewMonth    = minDate.getMonth();
        let selectedDate = null;

        const trigger      = document.getElementById('calTrigger');
        const popup        = document.getElementById('calPopup');
        const monthLabel   = document.getElementById('calMonthLabel');
        const grid         = document.getElementById('calGrid');
        const triggerText  = document.getElementById('triggerText');
        const hiddenInput  = document.getElementById('travel_date');
        const prevBtn      = document.getElementById('prevBtn');
        const nextBtn      = document.getElementById('nextBtn');

        function pad(n) { return String(n).padStart(2, '0'); }

        function formatDisplay(d) {
            return DAY_NAMES[d.getDay()] + ', '
                 + d.getDate() + ' '
                 + MONTH_NAMES[d.getMonth()].slice(0, 3) + ' '
                 + d.getFullYear();
        }

        function renderCalendar() {
            monthLabel.textContent = MONTH_NAMES[viewMonth] + ' ' + viewYear;
            grid.innerHTML = '';

            // Day-name headers
            DAY_NAMES.forEach(function (name) {
                const el = document.createElement('div');
                el.className   = 'cal-day-name';
                el.textContent = name;
                grid.appendChild(el);
            });

            // Blank cells before the 1st
            const firstWeekday  = new Date(viewYear, viewMonth, 1).getDay();
            const daysThisMonth = new Date(viewYear, viewMonth + 1, 0).getDate();

            for (let i = 0; i < firstWeekday; i++) {
                const el = document.createElement('div');
                el.className = 'cal-day empty';
                grid.appendChild(el);
            }

            // Day cells
            for (let d = 1; d <= daysThisMonth; d++) {
                const el      = document.createElement('div');
                el.className  = 'cal-day';
                el.textContent = d;

                const thisDate = new Date(viewYear, viewMonth, d);

                if (thisDate < minDate) {
                    // Past or today → disabled
                    el.classList.add('disabled');
                } else {
                    if (thisDate.toDateString() === today.toDateString()) {
                        el.classList.add('today');
                    }
                    if (selectedDate && thisDate.toDateString() === selectedDate.toDateString()) {
                        el.classList.add('selected');
                    }
                    el.addEventListener('click', function () { pickDate(thisDate); });
                }

                grid.appendChild(el);
            }
        }

        function pickDate(d) {
            selectedDate = d;
            hiddenInput.value      = d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
            triggerText.textContent = formatDisplay(d);
            triggerText.style.color = 'var(--text)';
            closeCalendar();
            renderCalendar(); // re-render to show selected highlight
        }

        function openCalendar() {
            renderCalendar();
            popup.classList.add('visible');
            trigger.classList.add('open');
            trigger.setAttribute('aria-expanded', 'true');
        }

        function closeCalendar() {
            popup.classList.remove('visible');
            trigger.classList.remove('open');
            trigger.setAttribute('aria-expanded', 'false');
        }

        function toggleCalendar() {
            popup.classList.contains('visible') ? closeCalendar() : openCalendar();
        }

        // Trigger click / keyboard
        trigger.addEventListener('click', toggleCalendar);
        trigger.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggleCalendar(); }
        });

        // Month navigation
        prevBtn.addEventListener('click', function () {
            viewMonth--;
            if (viewMonth < 0) { viewMonth = 11; viewYear--; }
            renderCalendar();
        });
        nextBtn.addEventListener('click', function () {
            viewMonth++;
            if (viewMonth > 11) { viewMonth = 0; viewYear++; }
            renderCalendar();
        });
        prevBtn.addEventListener('keydown', function (e) { if (e.key === 'Enter') prevBtn.click(); });
        nextBtn.addEventListener('keydown', function (e) { if (e.key === 'Enter') nextBtn.click(); });

        // Close when clicking outside
        document.addEventListener('click', function (e) {
            const field = document.getElementById('dateField');
            if (!field.contains(e.target)) closeCalendar();
        });

        // Close on Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeCalendar();
        });

    }); // end DOMContentLoaded
    </script>

</body>
</html>
<?php $conn->close(); ?>