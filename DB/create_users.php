<?php


if (!isset($conn)) {
    die("Connection not established!");
}

$table_name = "user";

$check_table_query = "SHOW TABLES LIKE '$table_name'";
$table_exists = $conn->query($check_table_query);

if ($table_exists && $table_exists->num_rows > 0) {
   // echo "Table '$table_name' already exists.<br>";
} else {
    $sql = "CREATE TABLE `$table_name` (
        `u_id` INT(10) NOT NULL AUTO_INCREMENT,
        `first_name` VARCHAR(100) NOT NULL,
        `last_name` VARCHAR(100) NOT NULL,
        `phone_number` VARCHAR(20) NOT NULL,
        `email` VARCHAR(100) NOT NULL,
        `address` VARCHAR(50) NOT NULL,
        `province` VARCHAR(50) NOT NULL,
        `country` VARCHAR(50) NOT NULL,
        `password` VARCHAR(250) NOT NULL,
        PRIMARY KEY (`u_id`)
    ) ENGINE=InnoDB;";

   // if ($conn->query($sql) === TRUE) {
  //      echo "Table '$table_name' created successfully.<br>";
  //  } else {
    //    echo "Error creating table: " . $conn->error . "<br>";
   // }
}
?>