<?php
include "db_connect.php";

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$table_name = "routes";
$check_table_query = "SHOW TABLES LIKE '$table_name'";
$table_exists = $conn->query($check_table_query);

if ($table_exists && $table_exists->num_rows > 0) {
  //  echo "Table '$table_name' already exists.<br>";
} else {
    $sql = "CREATE TABLE `$table_name` (
        `route_id` INT(10) NOT NULL AUTO_INCREMENT,
        `departure_city` VARCHAR(100) NOT NULL,
        `destination_city` VARCHAR(100) NOT NULL,
        `distance_km` DECIMAL(6,2) NOT NULL,
        PRIMARY KEY (`route_id`)
    ) ENGINE=InnoDB;";

    //if ($conn->query($sql) === TRUE) {
    //    echo "Table '$table_name' created successfully.<br>";
  //  } else {
  //      echo "Error creating table: " . $conn->error . "<br>";
   // }
}
?>