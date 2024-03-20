<?php
require 'vendor/autoload.php';
session_start();
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

date_default_timezone_set('Asia/Yerevan');
?>


<?php
$key = '1a3LM3W966D6QTJ5BJb9opunkUcw_d09NCOIJb9QZTsrneqOICoMoeYUDcd_NfaQyR787PAH98Vhue5g938jdkiyIZyJICytKlbjNBtebaHljIR6-zf3A2h3uy6pCtUFl1UhXWnV6madujY4_3SyUViRwBUOP-UudUL4wnJnKYUGDKsiZePPzBGrF4_gxJMRwF9lIWyUCHSh-PRGfvT7s1mu4-5ByYlFvGDQraP4ZiG5bC1TAKO_CnPyd1hrpdzBzNW4SfjqGKmz7IvLAHmRD-2AMQHpTU-hN2vwoA-iQxwQhfnqjM0nnwtZ0urE6HjKl6GWQW-KLnhtfw5n_84IRQ';

$message = '';
$error = '';
$maxAttempts=3;



if(isset($_GET['token']))
{
	$decoded = JWT::decode($_GET['token'], new Key($key, 'HS256'));
	$message = $decoded->msg;

}
if(isset($_COOKIE['token'])) {
        $decoded = JWT::decode($_COOKIE['token'], new Key($key, 'HS256'));
        if (isset($decoded->data->browser_name) && $decoded->exp >time()) {
            header('Location: welcome.php');
        }
}


if(isset($_POST["login"]))
{
	
	$connect = new PDO("mysql:host=localhost;dbname=testing", "root", "");

	if(empty($_POST["email"])){
		$error = 'Please Enter Email Details';
	} else if(empty($_POST["password"])){
		$error = 'Please Enter Password Details';
	} else {
		$query = "SELECT * FROM user WHERE user_email = ?";
		$statement = $connect->prepare($query);
		$statement->execute([$_POST["email"]]);
		$data = $statement->fetch(PDO::FETCH_ASSOC);
		if($data){
			if($data['isActivated']==='1'){
			if($data['user_password'] ===  hash('sha256',$_POST['password'])){

				$_SESSION[$data['user_email']]['login_attempts']=0;

				$userAgent = $_SERVER['HTTP_USER_AGENT'];
				$browser_name = '';
    			$browser_version = '';

   // Function to extract browser version from user agent string
function extractBrowserVersion($userAgent, $browser_name) {
    $startPos = strpos($userAgent, $browser_name);
    if ($startPos !== false) {
        $startPos += strlen($browser_name);
        $endPos = strpos($userAgent, ' ', $startPos);
        if ($endPos === false) {
            $endPos = strlen($userAgent);
        }
        return substr($userAgent, $startPos, $endPos - $startPos);
    }
    return null;
}

// Function to identify browser and extract version
function identifyBrowser($userAgent) {
    if (strpos($userAgent, 'Edg') !== false) {
        return array("Microsoft Edge", extractBrowserVersion($userAgent, 'Edg/'));
    } elseif (strpos($userAgent, 'Chrome') !== false) {
        return array("Google Chrome", extractBrowserVersion($userAgent, 'Chrome/'));
    } elseif (strpos($userAgent, 'Firefox') !== false) {
        return array("Mozilla Firefox", extractBrowserVersion($userAgent, 'Firefox/'));
    } elseif (strpos($userAgent, 'Safari') !== false && strpos($userAgent, 'Version') !== false) {
        return array("Apple Safari", extractBrowserVersion($userAgent, 'Version/'));
    } elseif (strpos($userAgent, 'OPR') !== false) {
        return array("Opera", extractBrowserVersion($userAgent, 'OPR/'));
    } elseif (strpos($userAgent, 'Trident') !== false) {
        return array("Internet Explorer", extractBrowserVersion($userAgent, 'rv:'));
    } else {
        return array("Unknown Browser", "Unknown Version");
    }
}

// Identify browser and extract version
list($browser_name, $browser_version) = identifyBrowser($userAgent);

				$expirationTime=time()+60*60;
				$token = JWT::encode(
					array(
						'iat'		=>	time(),
						'nbf'		=>	time(),
						'exp'		=>	$expirationTime,
						'data'	=> array(
							'user_id'	=>	$data['user_id'],
							'user_name'	=>	$data['user_name'],
							'user_email'=> $data['user_email'],
							'browser_name'=> $browser_name,
							'browser_version'=>$browser_version
						)
					),
					$key,
					'HS256'
				);
				
				setcookie("token", $token, $expirationTime, "/", "", true, true);
			
			
			$insertQuery = "INSERT INTO sessions (token, user_id,exp) VALUES (:token, :user_id,:exp)";
            $statement = $connect->prepare($insertQuery);
            $statement->bindParam(':token', $token);
            $statement->bindParam(':user_id', $data['user_id']);
			$statement->bindParam(':exp', $expirationTime);
            $statement->execute();
				
			
				
			header('location:welcome.php');


			} else {
				$_SESSION[$data['user_email']]['login_attempts'] = ( $_SESSION[$data['user_email']]['login_attempts'] ?? 0) + 1;

				if( $_SESSION[$data['user_email']]['login_attempts']>=$maxAttempts){
					$sleepDuration = pow(2,  $_SESSION[$data['user_email']]['login_attempts'] - $maxAttempts) * 30;
				}
			else {
				$error = 'Wrong password. Please try again.';
			}
				
			}
		}else {
			$error="You are not activated.Resend email!";
		}
		} else {
			$error = 'Email is not registered or is incorrect!';
		}
	}
}

?>



<!doctype html>
<html lang="en">
  	<head>
    	<!-- Required meta tags -->
    	<meta charset="utf-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1">

    	<!-- Bootstrap CSS -->
    	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    	<title>Login Form</title>
  	</head>
  	<body>
		
    	<div class="container" >
    		<h1  class="text-center mt-5 mb-5" >LOGIN NOW</h1>
    		<div class="row">
    			<div class="col-md-4">&nbsp;</div>
    			<div class="col-md-4">
    				<?php

    				if($error !== '')
    				{
    					echo '<div class="alert alert-danger">'.$error.'</div>';
    				}

    				if($message !== '')
    				{
    					echo '<div class="alert alert-info">'.$message.'</div>';
    				}
					
    				?>
		    		<div class="card">
		    			<div class="card-body">
		    				<form method="post">
		    					<div class="mb-3">
			    					<label>Email</label>
			    					<input type="email" id="email" name="email" class="form-control " value="<?php if(isset($_POST['email'])) {echo htmlentities($_POST['email']);} ?>" required placeholder="enter your email" <?php if(isset($_POST['email']) && isset($_SESSION[$_POST['email']]['login_attempts']) && $_SESSION[$_POST['email']]['login_attempts'] >= $maxAttempts) echo 'disabled'; ?>/>
			    				</div>
			    				<div class="mb-3">
			    					<label>Password</label>
			    					<input type="password" id="password" name="password" class="form-control" value="<?php if(isset($_POST['password'])) {echo htmlentities($_POST['password']);} ?>" required placeholder="enter your password" <?php if(isset($_POST['email']) && isset($_SESSION[$_POST['email']]['login_attempts']) && $_SESSION[$_POST['email']]['login_attempts'] >= $maxAttempts) echo 'disabled'; ?>/>
			    				</div>
			    				<div class="text-center">
			    					<input type="submit" name="login" id="login" class="form-control" style="color:white; background-color:black;"value="Login Now" <?php if(isset($_POST['email']) && isset($_SESSION[$_POST['email']]['login_attempts']) && $_SESSION[$_POST['email']]['login_attempts'] >= $maxAttempts) echo 'disabled'; ?>/>
			    				</div>
								<p>don't have an account? <a style="color: black; font-weight: bold;" href="register.php">register now</a> </p>
								<p1>didn't recieve your verification email? <a style="color: black; font-weight: bold;" href="resend-verification-email.php"> resend</a></p1>
							</form>
							 <!-- Empty span element to display remaining time -->
							 <span id="remainingTime"></span>
		    			</div>
		    		</div>
		    	</div>
	    	</div>
    	</div>
		<script>
    // Function to update remaining time dynamically
    function updateRemainingTime(remainingTime) {
        var message = (remainingTime > 0) ? "Try again in " + remainingTime + " seconds" : "";
        document.getElementById('remainingTime').innerText = message;
		document.getElementById('remainingTime').style.color = "red";
        if (remainingTime > 0) {
            setTimeout(function () {
                updateRemainingTime(remainingTime - 1);
            }, 1000); // Update every second
        } else {
            document.getElementById('email').removeAttribute('disabled');
            document.getElementById('password').removeAttribute('disabled');
            document.getElementById('login').removeAttribute('disabled');
        }
    }
    // Call the function with the initial remaining time
    updateRemainingTime(<?php echo $sleepDuration; ?>);
</script>
  	</body>
</html>




