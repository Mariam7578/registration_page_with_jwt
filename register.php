<?php 
require 'vendor/autoload.php';

date_default_timezone_set('Asia/Yerevan');

use Firebase\JWT\JWT;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

$error = '';
$message = '';

?>

<!doctype html>
<html lang="en">
  	<head>
    	<!-- Required meta tags -->
    	<meta charset="utf-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1">

    	<!-- Bootstrap CSS -->
    	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
		</style>
    	<title>Registration Form</title>
  	</head>
  	<body>
    	<div class="container">
    		<h1 class="text-center mt-5 mb-5" >REGISTER NOW</h1>
    		<div class="row">
    			<div class="col-md-4">&nbsp;</div>
    			<div class="col-md-4">
				<?php
				if(isset($_POST['register']))
				{
		$connect = new PDO("mysql:host=localhost; dbname=testing", "root", "");
		$phoneNum = $_POST['phone'];
		$email = $_POST['email'];
		$pass = $_POST['password'];
		$cpass = $_POST['cpassword'];
	
		$result="SELECT user_id FROM user WHERE user_email = ?";
		$statement1 = $connect->prepare($result);
		$statement1->execute([$_POST["email"]]);
		$p = explode("@", $email);
				if(gethostbyname($p[1])==$p[1]){
					 $err[] = "No such email exists!";
					 
				}
				else if($statement1->rowCount() > 0)
				{
					$err[] = 'User Already Exists';
				}
				
		
		$result="SELECT user_id FROM user WHERE user_phone = ?";
		$statement2 = $connect->prepare($result);
		$statement2->execute([$_POST["phone"]]);
	
		$pattern = '/^\+374[0-9]{8}$/';
		if(!preg_match($pattern,$phoneNum)){
		   $err[] = 'Phone number is not valid';   
		}
		else if($statement2->rowCount() > 0)
		{
			$err[] = 'Phone number already Exists';
		}
	
		$result="SELECT user_id FROM user WHERE user_password = ?";
		$statement2 = $connect->prepare($result);
		$statement2->execute([$_POST["password"]]);
	
				$uppercase = preg_match('@[A-Z]@', $pass);
				$lowercase = preg_match('@[a-z]@', $pass);
				$number    = preg_match('@[0-9]@', $pass);
				$specialChars = preg_match('@[^\w]@', $pass);
	
				if(strlen($pass) < 12){
				   $err[]='Password is weak';
				}
				else if($uppercase && $lowercase && !$number && $specialChars && strlen($pass) >= 12){
				   $err[]='Password is medium';
				}
				else if($pass != $cpass){
				   $err[] = 'Password not matched!';}
	
				if((!($statement1->rowCount() > 0) && gethostbyname($p[1])!=$p[1]) && !($statement2->rowCount() > 0) && (preg_match($pattern,$phoneNum))&&  $pass== $cpass && strlen($pass) >= 12 && $uppercase && $lowercase && $number && $specialChars){
				$pass=trim($_POST['password']);
				$passHash=hash('sha256',$pass);
					$data = array(
					':user_name'		=>	trim($_POST['name']),
					':user_email'		=>	trim($_POST['email']),
					':user_phone'		=>  trim($_POST['phone']),
					':user_password'	=>	$passHash,
					':isActivated'	=>	'Not Verified'
				);
				
				

				$insertQuery = "INSERT INTO user (user_name,user_email, user_phone,user_password,  isActivated) VALUES ( :user_name,:user_email, :user_phone,:user_password, :isActivated)";
				$statement3 = $connect->prepare($insertQuery);
				if($statement3->execute($data))
				{	
					$key = '1a3LM3W966D6QTJ5BJb9opunkUcw_d09NCOIJb9QZTsrneqOICoMoeYUDcd_NfaQyR787PAH98Vhue5g938jdkiyIZyJICytKlbjNBtebaHljIR6-zf3A2h3uy6pCtUFl1UhXWnV6madujY4_3SyUViRwBUOP-UudUL4wnJnKYUGDKsiZePPzBGrF4_gxJMRwF9lIWyUCHSh-PRGfvT7s1mu4-5ByYlFvGDQraP4ZiG5bC1TAKO_CnPyd1hrpdzBzNW4SfjqGKmz7IvLAHmRD-2AMQHpTU-hN2vwoA-iQxwQhfnqjM0nnwtZ0urE6HjKl6GWQW-KLnhtfw5n_84IRQ';
					
					$payload = array(
						'email'		=>	trim($_POST['email']),
						'exp'		=>	time() + 200,
					);
					
					$token = JWT::encode($payload, $key, 'HS256');
					
	
					$verificationLink = 'http://localhost/jwt/verify.php?token='.$token;
	
					$mail = new PHPMailer(true);
				
					$mail->isSMTP();
					$mail->Host = 'smtp.gmail.com';
					$mail->SMTPAuth = true;
					$mail->Username = 'artandprogramming5@gmail.com';
					$mail->Password = 'lnut xxkf azpp jdgk'; 
					$mail->SMTPSecure = 'tls';
					$mail->Port = 587;
					$mail->setFrom('artandprogramming5@gmail.com', 'artandprogramming5@gmail.com');
					$mail->addAddress(trim($_POST['email']), trim($_POST['name']));
					$mail->isHTML(true);
					$mail->Subject = 'Verify Your Email Address';
					$mail->Body = '
					<p>Hi,</p>
					<p>Thank you for registering with us! To complete your registration and activate your account, please click on the following link:</p>
					<p><a href="'.$verificationLink.'">Click me</a></p>
					';
					$mail->send();
					$message = 'Verification email has been send! Go to activating it!';


					setcookie("token", $token, time() + 200, "/", "", true, true);
				}
					
			
		}
	}
	 if(isset($err)){
				foreach($err as $err){
					echo '<div class="card-body alert alert-danger " style="border: 2px; text-align: center;">'.$err.'</div>';
				};
			 }
	?>
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
			    					<label>Name</label>
			    					<input type="text" name="name" class="form-control" value="<?php if(isset($_POST['name'])) {echo htmlentities($_POST['name']);} ?>" required placeholder="enter your name" />
			    				</div>
			    				<div class="mb-3">
			    					<label>Email</label>
			    					<input type="email" name="email" class="form-control" value="<?php if(isset($_POST['email'])) {echo htmlentities($_POST['email']);} ?>" required placeholder="enter your email"/>
			    				</div>
								<div class="mb-3">
			    					<label>Phone</label>
			    					<input type="phone" name="phone" class="form-control" value="<?php if(isset($_POST['phone'])) {echo htmlentities($_POST['phone']);} ?>" required placeholder="enter your phone number"/>
			    				</div>
			    				<div class="mb-3">
			    					<label>Password</label>
			    					<input type="password" name="password"id="password" class="form-control" value="<?php if(isset($_POST['password'])) {echo htmlentities($_POST['password']);} ?>" required placeholder="enter your password"/>
			    				</div>
								<script>	
										function generatePassword(){
										
									const length=12;

									const upperCase="ABCDEFGHIJKLMNOPQRSTUVWXYZ";
									const lowerCase="abcdefghijklmnopqrstuvwxyz";
									const number="0123456789";
									const symbol="@#$%^&*()_+~|}{[]></-=";
										const allChairs=upperCase+lowerCase+number+symbol;
											let passwordArray=[];

											for(let i=0; i<length; i++){
												const randomIndex=Math.floor(Math.random()*allChairs.length);
												passwordArray.push(allChairs[randomIndex]);
											}

											const hasUppercase=/[A-Z]/.test(passwordArray.join(''));
											const hasLowercase=/[a-z]/.test(passwordArray.join(''));
											const hasNumber=/[0-9]/.test(passwordArray.join(''));
											const hasSymbol=/[^A-Za-z0-9]/.test(passwordArray.join(''));

											while(!hasUppercase || !hasLowercase || !hasNumber || !hasSymbol){
												const missingCharacters=[];
												if(!hasUppercase){
													missingCharacters.push(upperCase.charAt(Math.floor(Math.random()*upperCase.length)));
												}
												if(!hasLowercase){
													missingCharacters.push(lowerCase.charAt(Math.floor(Math.random()*lowerCase.length)));
												}
												if(!hasNumber){
													missingCharacters.push(number.charAt(Math.floor(Math.random()*number.length)));
												}
												if(!hasUppercase){
													missingCharacters.push(symbol.charAt(Math.floor(Math.random()*symbol.length)));
												}

												const randomIndex=Math.floor(Math.random()*passwordArray.length);
												passwordArray[randomIndex]=missingCharacters[Math.floor(Math.random()*missingCharacters.length)];
											
												const hasUppercase=/[A-Z]/.test(passwordArray.join(''));
												const hasLowercase=/[a-z]/.test(passwordArray.join(''));
												const hasNumber=/[0-9]/.test(passwordArray.join(''));
												const hasSymbol=/[^A-Za-z0-9]/.test(passwordArray.join(''));
											}
											
										
											const password=passwordArray.join('');
											
											document.getElementById("generatedPassword").innerText = password;
											
										
										}


								</script>
								<p id="message"><span id="strength"></span></p>
								<button onclick="generatePassword()">Generate Password</button>
								<span id="generatedPassword"></span>
								<div class="mb-3">
			    					<label>Confirm password</label>
			    					<input type="password" name="cpassword" class="form-control" value="<?php if(isset($_POST['cpassword'])) {echo htmlentities($_POST['cpassword']);} ?>" required placeholder="confirm your password"/>
			    				</div>
			    				<div class="text-center">
			    					<input type="submit" name="register" value="Register Now" class="form-control" style="background-color: black; color: white"/>
			    				</div>
								<p>already have an account? <a href="index.php" style="color:black; font-weight: bold;">login now</a></p>
	<script>
     var pass = document.getElementById("password");
    var msg = document.getElementById("message");
    var str = document.getElementById("strength");

    pass.addEventListener('input', () => {

      if(pass.value.length){
         msg.style.display="block";
      }
      else {
         msg.style.display="none";
      }
    })
    pass.addEventListener('input', () => {
      
    
      var length = pass.value.length;
      var numbers = /\d/.test(pass.value);
      var upper = /[A-Z]/.test(pass.value);
      var lower = /[a-z]/.test(pass.value);
      var symbol = /[^A-Za-z0-9]/.test(pass.value);
    
      if (length >= 12 && numbers && upper && lower && symbol) {
        str.innerHTML = "strong";
        pass.style.borderColor = "green";
        //msg.style.color = "green";
        msg.style.display="none";
      } else if (length >= 12 && upper && lower && symbol) {
        str.innerHTML = "medium strength password";
        pass.style.borderColor = "blue";
        msg.style.color = "blue";
      } else {
        str.innerHTML = "weak strength password";
        pass.style.borderColor = "red"
        msg.style.color = "red";
      }
    });
	</script>
	
		    				</form>
		    			</div>
		    		</div>
		    	</div>
	    	</div>
    	</div>
  	</body>
</html>
