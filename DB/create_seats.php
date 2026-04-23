<?php


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$table_name = "seats";
$check_table_query = "SHOW TABLES LIKE '$table_name'";
$table_exists = $conn->query($check_table_query);

if ($table_exists && $table_exists->num_rows > 0) {
   // echo "Table '$table_name' already exists.<br>";
} else {
    $sql = "CREATE TABLE `$table_name` (
        `seat_id` INT(10) NOT NULL AUTO_INCREMENT,
        `bus_id` INT(10) NOT NULL,
        `seat_number` VARCHAR(10) NOT NULL,
        `is_window` BOOLEAN DEFAULT FALSE,
        PRIMARY KEY (`seat_id`),
        FOREIGN KEY (`bus_id`) REFERENCES `buses`(`bus_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB;";

  //  if ($conn->query($sql) === TRUE) {
   //     echo "Table '$table_name' created successfully.<br>";
   // } else {
  //      echo "Error creating table: " . $conn->error . "<br>";
   // }
}
?>