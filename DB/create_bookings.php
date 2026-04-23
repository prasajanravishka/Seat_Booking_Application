<?php


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$table_name = "bookings";
$check_table_query = "SHOW TABLES LIKE '$table_name'";
$table_exists = $conn->query($check_table_query);

if ($table_exists && $table_exists->num_rows > 0) {
    //echo "Table '$table_name' already exists.<br>";
} else {
    $sql = "CREATE TABLE `$table_name` (
        `booking_id` INT(10) NOT NULL AUTO_INCREMENT,
        `u_id` INT(10) NOT NULL,
        `schedule_id` INT(10) NOT NULL,
        `seat_id` INT(10) NOT NULL,
        `status` ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
        `booked_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`booking_id`),
        UNIQUE KEY `unique_seat_booking` (`schedule_id`, `seat_id`),
        FOREIGN KEY (`u_id`) REFERENCES `user`(`u_id`) ON DELETE CASCADE,
        FOREIGN KEY (`schedule_id`) REFERENCES `schedules`(`schedule_id`) ON DELETE CASCADE,
        FOREIGN KEY (`seat_id`) REFERENCES `seats`(`seat_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB;";

    //if ($conn->query($sql) === TRUE) {
  //      echo "Table '$table_name' created successfully.<br>";
  //  } else {
  //      echo "Error creating table: " . $conn->error . "<br>";
    //}
}
?>