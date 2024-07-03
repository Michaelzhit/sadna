<?php
$servername = "localhost";
$username = "isrotemshr_root";
$password = 'Qk8r(HV-olG@';
$dbname = "isrotemshr_pet";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
