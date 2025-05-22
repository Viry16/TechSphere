<?php
// Database connection parameters
$host = "localhost";
$username = "tech_sphere";
$password = "tech_sphere";
$dbname = "tech_sphere";

// Create connection
$conn = mysqli_connect($host, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to ensure proper language support
mysqli_set_charset($conn, "utf8");

// Function to safely escape input
function sanitize_input($conn, $data) {
    return mysqli_real_escape_string($conn, $data);
}

// Function to execute query and return result
function execute_query($conn, $query) {
    $result = mysqli_query($conn, $query);
    return $result;
}

// Function to get a single row
function get_row($conn, $query) {
    $result = execute_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return false;
}

// Function to get multiple rows
function get_rows($conn, $query) {
    $result = execute_query($conn, $query);
    $rows = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

// Function to execute insert query and return insert id
function insert_data($conn, $query) {
    if (mysqli_query($conn, $query)) {
        return mysqli_insert_id($conn);
    }
    return false;
}

// Function to update data
function update_data($conn, $query) {
    if (mysqli_query($conn, $query)) {
        return mysqli_affected_rows($conn);
    }
    return false;
}

// Function to delete data
function delete_data($conn, $query) {
    if (mysqli_query($conn, $query)) {
        return mysqli_affected_rows($conn);
    }
    return false;
}
?> 