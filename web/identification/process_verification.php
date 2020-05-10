<?php

if (isset($_POST['VerPas']) && !empty($_POST['VerPas'])) {
		
	include 'include/global.php';
	include 'include/function.php';
	
	$data 		= explode(";",$_POST['VerPas']);
	$user_id	= $data[0];
	$vStamp 	= $data[1];
	$time 		= $data[2];
	$sn 		= $data[3];
	
	$fingerData = getUserFinger($conn, $user_id);
	$device 	= getDeviceBySn($conn, $sn);
	$sql1 		= "SELECT * FROM eleve WHERE id='".$user_id."'";
	$result1 	= mysqli_query($conn, $sql1);
	$data 		= mysqli_fetch_array($result1);
	$matricule = $data['matricule'];
	$user_name	= $data['nom_fr']." ".$data['pnom_fr'];
		
	$salt = md5($sn.$fingerData[0]['finger_data'].$device[0]['vc'].$time.$user_id.$device[0]['vkey']);
	
	if (strtoupper($vStamp) == strtoupper($salt)) {
		
		$log = createLog($conn, $user_name, $time, $sn);
		
		if ($log == 1) {
		
			echo $base_path."messages.php?user_name=$user_name&time=$time&matricule=$matricule";
		
		} else {
		
			echo $base_path."messages.php?msg=$log";
			
		}
	
	} else {
		
		$msg = "Parameter invalid..";
		
		echo $base_path."messages.php?msg=$msg";
		
	}
}

?>