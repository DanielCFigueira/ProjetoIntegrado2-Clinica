<?php
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "clinica_prev_dentista";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
  echo "Database created successfully";
} else {
  echo "Error creating database: " . $conn->error;
}

$conn->close();

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Read sql file
$sql = file_get_contents('database/clinica_prev_dentistas.sql');

// Execute multi query
if ($conn->multi_query($sql)) {
    echo "Database setup successfully";
} else {
    echo "Error setting up database: " . $conn->error;
}

$conn->close();
