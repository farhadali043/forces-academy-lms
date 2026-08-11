<?php
// EXAMPLE database config — copy to config/db.php and fill in real values.
// The real db.php is in .gitignore so credentials are never pushed.

$host     = 'DB_HOST';
$user     = 'DB_USERNAME';
$password = 'DB_PASSWORD';
$database = 'DB_NAME';

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}
?>