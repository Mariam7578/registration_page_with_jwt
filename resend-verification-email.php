<?php

//index.php

date_default_timezone_set('Asia/Yerevan');

require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PHPMailer\PHPMailer\PHPMailer;

$message = '';
$error='';
?>

<?php


 
 if(isset($_POST['resend_email']))
{	$connect = new PDO("mysql:host=localhost; dbname=testing", "root", "");
	
	if(!empty($_POST["email"])){

		$email=$_POST["email"];
		$checkQuery = 'SELECT isActivated FROM user WHERE user_email = "'.$email.'"';
		$result = $connect->query($checkQuery);
		
		if($result->rowCount() > 0)
		{
			foreach($result as $row)
	{
			if($row['isActivated'] === 'Not Verified'){

				$key = '1a3LM3W966D6QTJ5BJb9opunkUcw_d09NCOIJb9QZTsrneqOICoMoeYUDcd_NfaQyR787PAH98Vhue5g938jdkiyIZyJICytKlbjNBtebaHljIR6-zf3A2h3uy6pCtUFl1UhXWnV6madujY4_3SyUViRwBUOP-UudUL4wnJnKYUGDKsiZePPzBGrF4_gxJMRwF9lIWyUCHSh-PRGfvT7s1mu4-5ByYlFvGDQraP4ZiG5bC1TAKO_CnPyd1hrpdzBzNW4SfjqGKmz7IvLAHmRD-2AMQHpTU-hN2vwoA-iQxwQhfnqjM0nnwtZ0urE6HjKl6GWQW-KLnhtfw5n_84IRQ';

				
	
				$token = JWT::encode(
					array(
						'email'		=>	trim($_POST['email']),
						'exp'		=>	time() + 40
					),
					$key,
					'HS256'
				);
			
	
				$verificationLink = 'http://localhost/jwt/verify.php?token='.$token;
	
				$mail = new PHPMailer(true);
			
				$mail->isSMTP();
				$mail->Host = 'smtp.gmail.com';
				$mail->SMTPAuth = true;
				$mail->Username = 'artandprogramming5@gmail.com';
				$mail->Password = 'lnut xxkf azpp jdgk'; 
				$mail->SMTPSecure = 'ssl';
				$mail->Port = 465;
				$mail->setFrom('artandprogramming5@gmail.com', 'artandprogramming5@gmail.com');
				$mail->addAddress(trim($_POST['email']));
				$mail->isHTML(true);
				$mail->Subject = 'Verify Your Email Address';
				$mail->Body = '
						<p>Hi,</p>
						<p>Thank you for registering with us! To complete your registration and activate your account, please click on the following link:</p>
						<p><a href="'.$verificationLink.'">Click me</a></p>
						';
						$mail->send();
						$message = 'Verification eMail has been send! Go to activating it!';
						setcookie("token", $token, time() + 40, "/", "", true, true);
			}
			else $error="Email already verified. Go to Login";
		}}	
		else $error="Email is not registered";	
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
    	<title>Resend Email Form</title>
  	</head>
  	<body>
		
    	<div class="container" >
    		<h1  class="text-center mt-5 mb-5" >Resend email verifiaction</h1>
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
	echo '<div class="alert alert-success">'.$message.'</div>';
}
?>
		    		<div class="card">
		    			<div class="card-body">
		    				<form method="post">
		    					<div class="mb-3">
			    					<label>Email address</label>
			    					<input type="email" name="email" class="form-control " value="<?php if(isset($_POST['email'])) {echo htmlentities($_POST['email']);} ?>" required placeholder="enter your email" />
			    				</div>
			    				<div class="text-center">
			    					<input type="submit" name="resend_email" class="form-control" style="color:white; background-color:black;"value="Resend" />
			    				</div>
								<p>you are already activated? <a style="color: black; font-weight: bold;" href="index.php">login now</a> </p>
							</form>
		    			</div>
		    		</div>
		    	</div>
	    	</div>
    	</div>
  	</body>
</html>





