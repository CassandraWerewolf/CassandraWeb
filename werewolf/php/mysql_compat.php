<?php
// mysql_compat.php: TEMPORARY compatibility layer.
//
// The old mysql_* extension was removed in PHP 7. This file re-creates the 13
// mysql_* functions this app uses, on top of mysqli, so the existing code runs
// on PHP 8.5 unchanged. It keeps the old behavior: failures return false
// instead of throwing exceptions.
//
// It does NOT make the SQL safer. Queries are still built from strings.
// Plan: convert files to PDO prepared statements, then delete this file.

// (No "if function_exists" guard here: PHP declares this file's functions before
// running its first line, so such a guard would always skip the setup below.)

// PHP 8.1+ makes mysqli throw exceptions on errors; the old code expects false.
mysqli_report(MYSQLI_REPORT_OFF);

$GLOBALS['__mysql_compat_link'] = null;

function __mysql_compat_link($link = null) {
  return $link ?: $GLOBALS['__mysql_compat_link'];
}

function mysql_connect($host = null, $user = null, $password = null) {
  $link = mysqli_connect($host, $user, $password);
  if ($link) {
    $GLOBALS['__mysql_compat_link'] = $link;
  }
  return $link;
}

function mysql_select_db($database, $link = null) {
  $link = __mysql_compat_link($link);
  return $link ? mysqli_select_db($link, $database) : false;
}

function mysql_set_charset($charset, $link = null) {
  $link = __mysql_compat_link($link);
  return $link ? mysqli_set_charset($link, $charset) : false;
}

/** @psalm-taint-sink sql $query */
function mysql_query($query, $link = null) {
  $link = __mysql_compat_link($link);
  return $link ? mysqli_query($link, $query) : false;
}

function mysql_error($link = null) {
  $link = __mysql_compat_link($link);
  return $link ? mysqli_error($link) : 'No database connection';
}

/**
 * Deliberately NOT treated as SQL-safe by Psalm. Psalm's built-in stub marks
 * mysqli_real_escape_string() as an SQL escape, which would hide string-built
 * SQL; "unescape" puts the taint back. Escaping only protects quoted values,
 * so new code should use db_query() with ? placeholders instead.
 *
 * @psalm-taint-unescape sql
 */
function mysql_real_escape_string($string, $link = null) {
  $link = __mysql_compat_link($link);
  return mysqli_real_escape_string($link, (string) $string);
}

function mysql_insert_id($link = null) {
  $link = __mysql_compat_link($link);
  return $link ? mysqli_insert_id($link) : false;
}

// The functions below take a query result. The old extension returned false
// (with a warning) when given false from a failed query; mysqli would throw a
// TypeError instead, so check first.

function mysql_num_rows($result) {
  return ($result instanceof mysqli_result) ? mysqli_num_rows($result) : false;
}

function mysql_fetch_array($result, $result_type = MYSQLI_BOTH) {
  if (!($result instanceof mysqli_result)) {
    return false;
  }
  $row = mysqli_fetch_array($result, $result_type);
  return $row === null ? false : $row;
}

function mysql_fetch_assoc($result) {
  return mysql_fetch_array($result, MYSQLI_ASSOC);
}

function mysql_fetch_row($result) {
  return mysql_fetch_array($result, MYSQLI_NUM);
}

function mysql_free_result($result) {
  if ($result instanceof mysqli_result) {
    mysqli_free_result($result);
    return true;
  }
  return false;
}

// mysql_result($result, $row, $field): one value from one row.
// $field may be a column number or a column name ("name" or "table.name").
function mysql_result($result, $row = 0, $field = 0) {
  if (!($result instanceof mysqli_result) || !mysqli_data_seek($result, $row)) {
    return false;
  }
  $data = mysqli_fetch_array($result, MYSQLI_BOTH);
  if ($data === null) {
    return false;
  }
  if (is_string($field) && !array_key_exists($field, $data) && strpos($field, '.') !== false) {
    $field = substr($field, strrpos($field, '.') + 1);
  }
  return array_key_exists($field, $data) ? $data[$field] : false;
}
