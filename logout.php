<?php
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

date_default_timezone_set('Asia/Yerevan');

$key = '1a3LM3W966D6QTJ5BJb9opunkUcw_d09NCOIJb9QZTsrneqOICoMoeYUDcd_NfaQyR787PAH98Vhue5g938jdkiyIZyJICytKlbjNBtebaHljIR6-zf3A2h3uy6pCtUFl1UhXWnV6madujY4_3SyUViRwBUOP-UudUL4wnJnKYUGDKsiZePPzBGrF4_gxJMRwF9lIWyUCHSh-PRGfvT7s1mu4-5ByYlFvGDQraP4ZiG5bC1TAKO_CnPyd1hrpdzBzNW4SfjqGKmz7IvLAHmRD-2AMQHpTU-hN2vwoA-iQxwQhfnqjM0nnwtZ0urE6HjKl6GWQW-KLnhtfw5n_84IRQ';

// Start session and verify if token exists
if(isset($_COOKIE['token'])){
    $decoded = JWT::decode($_COOKIE['token'], new Key($key, 'HS256'));
} else {
    // Redirect to index.php if no token found
    header('location:index.php');
    exit();
}

// Delete token from database
$connect = new PDO("mysql:host=localhost;dbname=testing", "root", "");
$query = "DELETE FROM sessions WHERE token = ?";
$stmt = $connect->prepare($query);
$stmt->bindParam(1, $_COOKIE['token']);
$stmt->execute();

// Unset the cookie
setcookie("token", "", time() - 3600,  "/", "", true, true);

// Redirect to index.php after logout
header('location:index.php');
exit();
?>
