<?php
/**
 * MySQL to MySQLi compatibility layer
 * This file provides backward compatibility functions for older code that uses mysql_* functions
 * For use during the migration from PHP 5 to PHP 8
 */

// Global connection variable used by compatibility functions
global $dbcnx;

/**
 * Compatibility function for mysql_connect
 */
function mysql_connect($host, $username, $password) {
    global $dbcnx;
    if (!isset($dbcnx)) {
        $dbcnx = mysqli_connect($host, $username, $password);
    }
    return $dbcnx;
}

/**
 * Compatibility function for mysql_select_db
 */
function mysql_select_db($database, $link = null) {
    global $dbcnx;
    $conn = $link ?: $dbcnx;
    return mysqli_select_db($conn, $database);
}

/**
 * Compatibility function for mysql_query
 */
function mysql_query($query, $link = null) {
    global $dbcnx;
    $conn = $link ?: $dbcnx;
    return mysqli_query($conn, $query);
}

/**
 * Compatibility function for mysql_fetch_array
 */
function mysql_fetch_array($result, $result_type = MYSQLI_BOTH) {
    return mysqli_fetch_array($result, $result_type);
}

/**
 * Compatibility function for mysql_fetch_row
 */
function mysql_fetch_row($result) {
    return mysqli_fetch_row($result);
}

/**
 * Compatibility function for mysql_num_rows
 */
function mysql_num_rows($result) {
    return mysqli_num_rows($result);
}

/**
 * Compatibility function for mysql_result
 * This function doesn't exist in MySQLi, so we need to reimplement it
 */
function mysql_result($result, $row, $field = 0) {
    mysqli_data_seek($result, $row);
    $data = mysqli_fetch_array($result);
    return $data[$field];
}

/**
 * Compatibility function for mysql_insert_id
 */
function mysql_insert_id($link = null) {
    global $dbcnx;
    $conn = $link ?: $dbcnx;
    return mysqli_insert_id($conn);
}

/**
 * Compatibility function for mysql_affected_rows
 */
function mysql_affected_rows($link = null) {
    global $dbcnx;
    $conn = $link ?: $dbcnx;
    return mysqli_affected_rows($conn);
}

/**
 * Compatibility function for mysql_real_escape_string
 */
function mysql_real_escape_string($string, $link = null) {
    global $dbcnx;
    $conn = $link ?: $dbcnx;
    return mysqli_real_escape_string($conn, $string);
}

/**
 * Compatibility function for mysql_close
 */
function mysql_close($link = null) {
    global $dbcnx;
    $conn = $link ?: $dbcnx;
    return mysqli_close($conn);
}

// Note: quote_smart() function is defined in db.php, so we don't duplicate it here
?>
