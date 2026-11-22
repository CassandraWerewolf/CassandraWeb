<?php
// DELIBERATELY BAD - throwaway test for the security CI (branch test/security-bad).
// String-built SQL from user input: the php-taint job must fail on this.
// This branch is never merged; delete it after the test.
include_once "php/db.php";

dbConnect();
$result = dbGetResult("SELECT name FROM Users WHERE id=" . $_GET['id']);
