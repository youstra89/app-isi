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
	$matricule  = $data['matricule'];
	$nom	    = $data['nom_fr'];
	$pnom    	= $data['pnom_fr'];
	$date_naissance    	= date('d-m-Y', strtotime($data['date_naissance']));
	$lieu_naissance    	= $data['lieu_naissance'];
	$sexe    	= $data['sexe'] == 1 ? "Masculin" : "Fémininé";
	$nom_pere    	= $data['nom_pere'];
	$situation_pere    	= $data['situation_pere'] == 1 ? "Vivant" : "Décédé";
	$contact_pere    	= $data['contact_pere'];
	$profession_pere    	= $data['profession_pere'];
	$nom_mere    	= $data['nom_mere'];
	$contact_mere    	= $data['contact_mere'];
	$situation_mere    	= $data['situation_mere'] == 1 ? "Vivante" : "Décédée";
	$profession_mere    	= $data['profession_mere'];
	$renvoye    	= $data['renvoye'] == 0 ? "Non" : "Oui";
	$regime    	= $data['regime'] == "A" ? "Académie" : "Formation";
	$nom_tuteur    	= $data['nom_tuteur'];
	$contact_tuteur    	= $data['contact_tuteur'];
	$profession_tuteur    	= $data['profession_tuteur'];
	$user_name  = $nom." ".$pnom;
	
	$req_fre 		= "SELECT c.libelle_fr AS classe  FROM frequenter f JOIN classe c ON c.id = f.classe_id WHERE f.annee_id = 4 AND f.eleve_id='".$user_id."'";
	$res_fre 	= mysqli_query($conn, $req_fre);
	$data_fre	= mysqli_fetch_array($res_fre);
	$classe     = $data_fre['classe'];
		
	$salt = md5($sn.$fingerData[0]['finger_data'].$device[0]['vc'].$time.$user_id.$device[0]['vkey']);
	
	if (strtoupper($vStamp) == strtoupper($salt)) {
		
		$log = createLog($conn, $user_name, $time, $sn);
		
		if ($log == 1) {
		
			echo $base_path."messages.php?nom=$nom&pnom=$pnom&time=$time&matricule=$matricule&classe=$classe&sexe=$sexe&date_naissance=$date_naissance&lieu_naissance=$lieu_naissance&nom_pere=$nom_pere&situation_pere=$situation_pere&contact_pere=$contact_pere&profession_pere=$profession_pere&nom_mere=$nom_mere&situation_mere=$situation_mere&contact_mere=$contact_mere&profession_mere=$profession_mere&nom_tuteur=$nom_tuteur&contact_tuteur=$contact_tuteur&profession_tuteur=$profession_tuteur&renvoye=$renvoye&regime=$regime";
		
		} else {
		
			echo $base_path."messages.php?msg=$log";
			
		}
	
	} else {
		
		$msg = "Parameter invalid...";
		
		echo $base_path."messages.php?msg=$msg";
		
	}
}

?>