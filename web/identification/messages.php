<?php
	
if (isset($_GET['msg']) && !empty($_GET['msg'])) {
	
	echo $_GET['msg'];

} elseif (isset($_GET['nom']) && !empty($_GET['nom']) && isset($_GET['time']) && !empty($_GET['time'])) {
	
	include 'include/global.php';
	include 'include/function.php';
	
	$nom	= $_GET['nom'];
	$pnom	= $_GET['pnom'];
	$matricule	= $_GET['matricule'];
	$nom_pere	= $_GET['nom_pere'];
	$nom_mere	= $_GET['nom_mere'];
	$nom_tuteur	= $_GET['nom_tuteur'];
	$sexe	= $_GET['sexe'];
	$regime	= $_GET['regime'];
	$renvoye	= $_GET['renvoye'];
	$date_naissance	= $_GET['date_naissance'];
	$profession_pere	= $_GET['profession_pere'];
	$profession_mere	= $_GET['profession_mere'];
	$profession_tuteur	= $_GET['profession_tuteur'];
	$contact_pere	= $_GET['contact_pere'];
	$contact_mere	= $_GET['contact_mere'];
	$contact_tuteur	= $_GET['contact_tuteur'];
	$situation_mere	= $_GET['situation_mere'];
	$situation_pere	= $_GET['situation_pere'];
	$lieu_naissance	= $_GET['lieu_naissance'];
	$classe	= $_GET['classe'];
	$time		= date('Y-m-d H:i:s', strtotime($_GET['time']));
	// echo "<strong>".$user_name."</strong> avec le matricule <strong>".$matricule."</strong> authentifié(e) avec succès ".date('d-m-Y H:i:s', strtotime($time));
	
	
} else {
		
	$msg = "Parameter invalid..";
	
	echo "$msg";
	
}

	
?>

<!DOCTYPE>
<html>
	<head>
		<title>Authentification de <?= $nom." ".$pnom." --- ".$matricule; ?></title>
		<link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
		<link href="assets/css/custom-style.css" rel="stylesheet">
	</head>
	<body>
		<div class="container emp-profile">
            <form method="post">
                <div class="row">
                    <div class="col-md-4">
                        <div class="profile-img">
                            <!-- <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS52y5aInsxSm31CvHOFHWujqUx_wWTS9iM6s7BAm21oEN_RiGoog" alt=""/> -->
                            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn%3AANd9GcQ-RXhCCN2eoAC_SjP4ciVDB462EdAMSj_8ipsamW_GWLMPZHi4&usqp=CAU" alt=""/>
                            <div class="file btn btn-lg btn-primary">
                                Change Photo
                                <input type="" name="file"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="profile-head">
                                    <h5>
                                        <?= $pnom." ".$nom; ?>
                                    </h5>
                                    <h6>
                                        <?= $regime; ?>
                                    </h6>
                            <ul class="nav nav-tabs" id="myTab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Informations</a>
                                </li>
                                <li class="nav-item">
                                    <!--<a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Timeline</a>-->
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <input type="submit" class="profile-edit-btn" name="btnAddMore" value="Edit Profile"/>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="profile-work">
						<p><?= date('d-m-Y H:i:s', strtotime($time)); ?></p>
                            <!--<p>WORK LINK</p>
                            <a href="">Website Link</a><br/>
                            <a href="">Bootsnipp Profile</a><br/>
                            <a href="">Bootply Profile</a>
                            <p>SKILLS</p>
                            <a href="">Web Designer</a><br/>
                            <a href="">Web Developer</a><br/>
                            <a href="">WordPress</a><br/>
                            <a href="">WooCommerce</a><br/>
                            <a href="">PHP, .Net</a><br/>-->
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="tab-content profile-tab" id="myTabContent">
                            <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
								<div class="row">
									<div class="col-md-6">
										<label>Matricule</label>
									</div>
									<div class="col-md-6">
										<p><?= $matricule; ?></p>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6">
										<label>Nom</label>
									</div>
									<div class="col-md-6">
										<p><?= $nom; ?></p>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6">
										<label>Prénom</label>
									</div>
									<div class="col-md-6">
										<p><?= $pnom; ?></p>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6">
										<label>Classe</label>
									</div>
									<div class="col-md-6">
										<p><?= $classe; ?></p>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6">
										<label>Sexe</label>
									</div>
									<div class="col-md-6">
										<p><?= $sexe; ?></p>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6">
										<label>Date de naissance</label>
									</div>
									<div class="col-md-6">
										<p><?= $date_naissance; ?></p>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6">
										<label>Lieu de naissance</label>
									</div>
									<div class="col-md-6">
										<p><?= $lieu_naissance; ?></p>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6">
										<label>Nom du père</label>
									</div>
									<div class="col-md-6">
										<p><?= $nom_pere; ?></p>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6">
										<label>Contact du père</label>
									</div>
									<div class="col-md-6">
										<p><?= $contact_pere; ?></p>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6">
										<label>Profession du père</label>
									</div>
									<div class="col-md-6">
										<p><?= $profession_pere; ?></p>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6">
										<label>Situation du père</label>
									</div>
									<div class="col-md-6">
										<p><?= $situation_pere; ?></p>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6">
										<label>Nom de la mère</label>
									</div>
									<div class="col-md-6">
										<p><?= $nom_mere; ?></p>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6">
										<label>Contact de la mère</label>
									</div>
									<div class="col-md-6">
										<p><?= $contact_mere; ?></p>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6">
										<label>Profession de la mère</label>
									</div>
									<div class="col-md-6">
										<p><?= $profession_mere; ?></p>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6">
										<label>Situation de la mère</label>
									</div>
									<div class="col-md-6">
										<p><?= $situation_mere; ?></p>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6">
										<label>Nom du tuteur</label>
									</div>
									<div class="col-md-6">
										<p><?= $nom_tuteur; ?></p>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6">
										<label>Profession du tuteur</label>
									</div>
									<div class="col-md-6">
										<p><?= $nom_tuteur; ?></p>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6">
										<label>Contact du tuteur</label>
									</div>
									<div class="col-md-6">
										<p><?= $nom_tuteur; ?></p>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6">
										<label>Renvoyé</label>
									</div>
									<div class="col-md-6">
										<p><?= $renvoye; ?></p>
									</div>
								</div>
                            </div>
                            <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
								<div class="row">
									<div class="col-md-6">
										<label>Experience</label>
									</div>
									<div class="col-md-6">
										<p>Expert</p>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6">
										<label>Hourly Rate</label>
									</div>
									<div class="col-md-6">
										<p>10$/hr</p>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6">
										<label>Total Projects</label>
									</div>
									<div class="col-md-6">
										<p>230</p>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6">
										<label>English Level</label>
									</div>
									<div class="col-md-6">
										<p>Expert</p>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6">
										<label>Availability</label>
									</div>
									<div class="col-md-6">
										<p>6 months</p>
									</div>
								</div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <label>Your Bio</label><br/>
                                        <p>Your detail description</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>           
        </div>
		<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	</body>
</html>