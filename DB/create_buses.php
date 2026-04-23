<?php
include "db_connect.php";

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$table_name = "buses";
$check_table_query = "SHOW TABLES LIKE '$table_name'";
$table_exists = $conn->query($check_table_query);

if ($table_exists && $table_exists->num_rows > 0) {
    //echo "Table '$table_name' already exists.<br>";
} else {
    $sql = "CREATE TABLE `$table_name` (
        `bus_id` INT(10) NOT NULL AUTO_INCREMENT,
        `bus_number` VARCHAR(50) NOT NULL UNIQUE,
        `model_type` VARCHAR(50) NOT NULL,
        `capacity` INT(10) NOT NULL,
        PRIMARY KEY (`bus_id`)
    ) ENGINE=InnoDB;";

  //  if ($conn->query($sql) === TRUE) {
   //     echo "Table '$table_name' created successfully.<br>";
   // } else {
   //     echo "Error creating table: " . $conn->error . "<br>";
  //  }
}
?>