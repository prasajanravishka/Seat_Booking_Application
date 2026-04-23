<?php
// db_connect.php
$db_server = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "bus_booking"; // Make sure this matches!

// Create connection
$conn = new mysqli($db_server, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>