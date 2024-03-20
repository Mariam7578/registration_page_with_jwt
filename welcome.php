<?php
//session_start();
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

date_default_timezone_set('Asia/Yerevan');

$key = '1a3LM3W966D6QTJ5BJb9opunkUcw_d09NCOIJb9QZTsrneqOICoMoeYUDcd_NfaQyR787PAH98Vhue5g938jdkiyIZyJICytKlbjNBtebaHljIR6-zf3A2h3uy6pCtUFl1UhXWnV6madujY4_3SyUViRwBUOP-UudUL4wnJnKYUGDKsiZePPzBGrF4_gxJMRwF9lIWyUCHSh-PRGfvT7s1mu4-5ByYlFvGDQraP4ZiG5bC1TAKO_CnPyd1hrpdzBzNW4SfjqGKmz7IvLAHmRD-2AMQHpTU-hN2vwoA-iQxwQhfnqjM0nnwtZ0urE6HjKl6GWQW-KLnhtfw5n_84IRQ';

if(isset($_COOKIE['token'])){
    $decoded = JWT::decode($_COOKIE['token'], new Key($key, 'HS256'));
} else {
    header('location:index.php');
}

if(isset($_POST['delete'])){
    $sessionToken = $_POST['session_token'];
    // Delete session with this token from the database
    $connect = new PDO("mysql:host=localhost;dbname=testing", "root", "");
    $query = "DELETE FROM sessions WHERE token = ?";
    $stmt = $connect->prepare($query);                
    $stmt->bindParam(1, $sessionToken);
    $stmt->execute();
    // Redirect to refresh the page
    header("Location: $_SERVER[PHP_SELF]");
    exit();
}

?>

<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="style.css" rel="stylesheet">

        <title>Welcome User Page</title>
    </head>
    <body>
        <div class="container">
        <h1>Welcome <b><?php echo $decoded->data->user_name; ?></b></h1>
                    <p>This is a user page</p>
                    <a href="index.php" class='btn' >Login</a>
                    <a href="register.php" class='btn'>Register</a>
                    <a href="logout.php" class='btn'>Logout</a>    
        <?php
                    
                    $connect = new PDO("mysql:host=localhost;dbname=testing", "root", "");
                    $query = "SELECT exp, token FROM sessions WHERE user_id = ?";
                    $stmt = $connect->prepare($query);                
                    $stmt->bindParam(1, $decoded->data->user_id);
                    $stmt->execute();
                    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if ($sessions) {
                        echo "<h3>User's Active Sessions:</h3>";
                        foreach ($sessions as $session) {
                            if ($session['exp'] > time()) {
                                $sessionToken = $session['token'];
                               
                                echo "Expiration Time: " . date('Y-m-d H:i:s', $session['exp']) . "<br>";
                                echo "browser: ".$decoded->data->browser_name."  ".$decoded->data->browser_version;
                                echo "\n";
                                ?>
                                <form method="post">
                                    <input type="hidden" name="session_token" value="<?php echo $sessionToken; ?>">
                                    <input type="submit" name="delete" style="color:white; background-color:black;" value="Delete Session">
                                </form>
                                <?php
                            }
                        }
                    } else {
                        echo "<p>The only session still active is this one.</p>";
                    }
                   
                    

                   
        ?>
                    
        </div>
    </body>
</html>
