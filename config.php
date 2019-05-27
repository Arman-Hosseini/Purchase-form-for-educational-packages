<?php
/**
	In the name of God
	Written By: Arman Hosseini
 */

// Database Config
require_once( "db.php" );

try {
    $conn = new PDO("mysql:host=$servername;dbname=".$dbname.";charset=utf8", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e)
{
    exit( " خطا در برقراری ارتباط با بانک اطلاعاتی: " . $e->getMessage() . "<br />" . PHP_EOL );
}
//

// Init fields
require_once( "init.php" );

// Set head
$head   = '
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/fonts.css">
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <style>
    html, body{
        font-family: Vazir, "tahoma",sans-serif;
        font-size: 15px;
    }
    </style>
	';

// Set page header
$header = '
    <div class="page-header">
        <h2>خرید طرح ویژه</h2>      
    </div>
	';

// Define br tag
define("br", "<br />" . PHP_EOL);