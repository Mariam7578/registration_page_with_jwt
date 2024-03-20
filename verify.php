<?php

//verify.php

date_default_timezone_set('Asia/Yerevan');

require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$key = '1a3LM3W966D6QTJ5BJb9opunkUcw_d09NCOIJb9QZTsrneqOICoMoeYUDcd_NfaQyR787PAH98Vhue5g938jdkiyIZyJICytKlbjNBtebaHljIR6-zf3A2h3uy6pCtUFl1UhXWnV6madujY4_3SyUViRwBUOP-UudUL4wnJnKYUGDKsiZePPzBGrF4_gxJMRwF9lIWyUCHSh-PRGfvT7s1mu4-5ByYlFvGDQraP4ZiG5bC1TAKO_CnPyd1hrpdzBzNW4SfjqGKmz7IvLAHmRD-2AMQHpTU-hN2vwoA-iQxwQhfnqjM0nnwtZ0urE6HjKl6GWQW-KLnhtfw5n_84IRQ';

$token = '';
$payload = array();

if(isset($_GET['token']))
{
	$connect = new PDO("mysql:host=localhost; dbname=testing", "root", "");
	
	try {
        $decoded = JWT::decode($_GET['token'], new Key($key, 'HS256'));
        
        // Check token expiration
        if (isset($decoded->exp) && time() > $decoded->exp) {
            throw new Exception('Token has expired');
        }
	$checkQuery = 'SELECT isActivated FROM user WHERE user_email = "'.$decoded->email.'"';
	$result = $connect->query($checkQuery);
	foreach($result as $row)
	{	
		if($row['isActivated'] === '1')
		{
			
			$payload = array(
				'msg'	=>	'Your Email Already Verified, You can login'
				
			);
		}
		else
		{
			$query = 'UPDATE user SET isActivated ="1" WHERE user_email = "'.$decoded->email.'"';
			$statement = $connect->prepare($query);
			$statement->execute();
			$payload = array(
				'msg'	=>	'Email Successfully verify, now you can login'
				
			);
		}
		
		$token = JWT::encode($payload, $key, 'HS256');
		
		header('location:index.php?token='.$token);
	}
} catch (Exception $e) {
	

	echo $e->getMessage();

}
}
