<?php

if (isset($_POST['RegTemp']) && !empty($_POST['RegTemp'])) {
		
    	include 'include/global.php';
    	include 'include/function.php';
		
		$data 		= explode(";",$_POST['RegTemp']);
		$vStamp 	= $data[0];
		$sn 		= $data[1];
		$user_id	= $data[2];
		$regTemp 	= $data[3];
		
		$device = getDeviceBySn($conn, $sn);
		
		$salt = md5($device[0]['ac'].$device[0]['vkey'].$regTemp.$sn.$user_id);
		
		if (strtoupper($vStamp) == strtoupper($salt)) {
			
			$sql1 		= "SELECT MAX(finger_id) as fid FROM demo_finger WHERE user_id=".$user_id;
			$result1 	= mysqli_query($conn, $sql1);
			// if(!$result1){
			// 	printf("Error: %s\n", mysqli_error($conn));
			// 	exit();
			// }
			//$result1    = mysqli_result($result1);
			// error_log("Contenu de result ".$result1, 0);
			$data 		= mysqli_fetch_row($result1);
			$fid 		= $data['fid'];
			
			if ($fid <= 4) {
				$sq2 		= "INSERT INTO demo_finger SET user_id='".$user_id."', finger_id=".($fid+1).", finger_data='".$regTemp."' ";
				$result2	= mysqli_query($conn, $sq2);
				if ($result1 && $result2) {
					$res['result'] = true;				
				} else {
					$res['server'] = "Error insert registration data!";
				}
			} else {
				$res['result'] = false;
				$res['user_finger_'.$user_id] = "Template already exist.";
			}
			
			echo "empty";
			
		} else {
			
			$msg = "Parameter invalid..";
			
			echo $base_path."messages.php?msg=$msg";
			
		}

		
}

?>