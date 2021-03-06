<?php
    	include 'include/global.php';
    	include 'include/function.php';

	if (isset($_GET['action']) && $_GET['action'] == 'index') {
		$regime = $_GET['regime'];
?>
		<script type="text/javascript">

			$('title').html('User');

			function user_delete(user_id, user_name) {

				var r = confirm("Delete user "+user_name+" ?");

				if (r == true) {

					push('user.php?action=delete&user_id='+user_id);

				}
			}
			
			function user_register(user_id, user_name, regime) {
				
				$('body').ajaxMask();
			
				regStats = 0;
				regCt = -1;
				try
				{
					timer_register.stop();
				}
				catch(err)	
				{
					console.log('Registration timer has been init');
				}
				
				
				var limit = 4;
				var ct = 1;
				var timeout = 5000;
				
				timer_register = $.timer(timeout, function() {					
					console.log("'"+user_name+"' registration checking...");
					user_checkregister(user_id,$("#user_finger_"+user_id).html());
					if (ct>=limit || regStats==1) 
					{
						timer_register.stop();
						console.log("'"+user_name+"' registration checking end");
						if (ct>=limit && regStats==0)
						{
							alert("'"+user_name+"' registration fail!");
							$('body').ajaxMask({ stop: true });
						}						
						if (regStats==1)
						{
							$("#user_finger_"+user_id).html(regCt);
							alert("'"+user_name+"' registration success!");
							$('body').ajaxMask({ stop: true });
							load('user.php?action=index&regime=' + regime);
						}
					}
					ct++;
				});
			}
			
			function user_checkregister(user_id, current) {
				$.ajax({
					url			:	"user.php?action=checkreg&user_id="+user_id+"&current="+current,
					type		:	"GET",
					success		:	function(data)
									{
										try
										{
											var res = jQuery.parseJSON(data);	
											if (res.result)
											{
												regStats = 1;
												$.each(res, function(key, value){
													if (key=='current')
													{														
														regCt = value;
													}
												});
											}
										}
										catch(err)
										{
											alert(err.message);
										}
									}
				});
			}

		</script>
		<br>

<?php
		include 'include/global.php';
		$user = getUser($conn, $regime);

		if (count($user) > 0) {

			echo	"<div class='row'>"
					."<div class='col-md-12'>"
					."<div class='table-responsive'>"
						."<table class='table table-bordered table-hover' id='dataTable' cellspacing='0'>"
								."<thead>"
									."<tr>"
										."<th width='5%'>Id</th>"
										."<th>Matricule</th>"
										."<th>Nom</th>"
										."<th>Prénom</th>"
										."<th>Né le</th>"
										."<th>Nbr Empr</th>"
										."<th>Action</th>"
									."</tr>"
								."</thead>"
								."<tbody>";
			$i = 0;
			foreach ($user as $row) {
				$i++;
				$finger 		  = getUserFinger($conn, $row['user_id']);
				$register		  = '';
				$verification	  = '';
				$nom = $row['user_nom_fr'];
				$pnom = $row['user_pnom_fr'];
				$url_register	  = base64_encode($base_path."register.php?user_id=".$row['user_id']);
				$url_verification = base64_encode($base_path."verification.php?user_id=".$row['user_id']);

				if (count($finger) <= 4) {

					$register     = "<a href='finspot:FingerspotReg;$url_register' title='Enregistrer l empreinte' class='btn btn-xs btn-primary' onclick=\"user_register('".$row['user_id']."','".$row['user_name']."','".$regime.")\">Sauver</a>";
					$verification = "<a href='finspot:FingerspotVer;$url_verification' title='Vérifier l empreinte' class='btn btn-xs btn-success'>Vérifier</a>";
				} 
				elseif (count($finger) == 5) {
					$verification = "<a href='finspot:FingerspotVer;$url_verification' class='btn btn-xs btn-success'>Login</a>";
				}

				echo			"<tr>"
				 					."<td>".$i."</td>"
				 					."<td>".$row['user_matricule']."</td>"
				 					."<td>".$row['user_nom_fr']."</td>"
				 					."<td>".$row['user_pnom_fr']."</td>"
				 					."<td>".$row['user_date']."</td>"
				 					."<td><code id='user_finger_".$row['user_id']."'>".count($finger)."</code></td>"
				 					."<td>"
										// ."<button type='button' class='btn btn-xs btn-danger' onclick=\"user_delete('".$row['user_id']."','".$row['user_name']."')\">Delete</button>"
										// ."&nbsp"
										."$register"
										."&nbsp"
										."$verification"
									."</td>"
				 				."</tr>";

			}

			echo
								"</tbody>"
								."<tfoot>"
								."<tr>"
									."<th width='5%'>Id</th>"
									."<th>Matricule</th>"
									."<th>Nom</th>"
									."<th>Prénom</th>"
									."<th>Né le</th>"
									."<th>Nbr Empr</th>"
									."<th>Action</th>"
								."</tr>"
							."</tfoot>"
						."</table>"
					."</div>"
					."</div>"
				."</div>";

		} else {

			echo 'User Empty';

		}

		?>
		<script>

			// $(document).ready( function () {
			// 	$('#dataTable').DataTable();
			// } );
			$(document).ready(function() {
				// Setup - add a text input to each footer cell
				$('#dataTable tfoot th').each( function () {
					var title = $(this).text();
					$(this).html( '<input type="text" placeholder="Search '+title+'" />' );
				} );
			
				// DataTable
				var table = $('#dataTable').DataTable();
			
				// Apply the search
				table.columns().every( function () {
					var that = this;
			
					$( 'input', this.footer() ).on( 'keyup change clear', function () {
						if ( that.search() !== this.value ) {
							that
								.search( this.value )
								.draw();
						}
					} );
				} );
			} );
		</script>
<?php


	} elseif (isset($_GET['action']) && $_GET['action'] == 'create') {
?>

		<script type="text/javascript">

			$('title').html('Add user');

			function user_store() {

				user_name	= $('#user_name').val();

				push('user.php?action=store&user_name='+user_name);

			}

		</script>

		<div class="row">
			<div class="col-md-4">

			</div>
			<div class="col-md-4">
				<div class="form-group">
					<label for="user_name">Username</label>
					<input type="text"  id="user_name" class="form-control" placeholder="Enter Username">
				</div>
				<a class="btn btn-default" onclick="load('<?php echo $base_path?>user.php?action=index')">Back</a>
				<button type="submit" class="btn btn-success" onclick="user_store()">Save</button>
			</div>
			<div class="col-md-4">

			</div>
		</div>

<?php
	} elseif (isset($_GET['action']) && $_GET['action'] == 'store') {

		$res 		= array();
        		$res['result'] 	= false;

		if ($_GET['user_name'] == '' || !isset($_GET['user_name']) || empty($_GET['user_name'])) {

			$res['user_name'] = "username can't empty";

		} elseif (isset($_GET['user_name']) && !empty($_GET['user_name'])) {

			$user_name = checkUserName($_GET['user_name'], $conn);

			if ($user_name != 1) {

				$res['user_name'] = $user_name;

			}

		}

		if (count($res) > 1) {

			echo json_encode($res);

		} else {

			$sql 	= "INSERT INTO demo_user SET user_name='".$_GET['user_name']."' ";
			$result = mysqli_query($conn, $sql);

			if ($result) {

				$res['result']	= true;
				$res['reload'] 	= "user.php?action=index";

			} else {

				$res['server'] = "Error insert data!";

			}

			echo json_encode($res);

		}

	} elseif (isset($_GET['action']) && $_GET['action'] == 'delete') {

		$sql1		= "DELETE FROM demo_user WHERE user_id = '".$_GET['user_id']."' ";
		$result1	= mysqli_query($conn, $sql1);
		
		$sql2 		= "DELETE FROM demo_finger WHERE user_id = '".$_GET['user_id']."' ";
		$result2 	= mysqli_query($conn, $sql2);

		if ($result1 && $result2) {

			$res['result'] 	= true;
			$res['reload'] 	= "user.php?action=index";

		} else {

			$res['server'] 	= "Error delete data!#".$sql1;

		}

		echo json_encode($res);

	} elseif (isset ($_GET['action']) && $_GET['action'] == 'checkreg') {
		
		$sql1		= "SELECT count(finger_id) as ct FROM demo_finger WHERE user_id=".$_GET['user_id'];
		$result1	= mysqli_query($conn, $sql1);
		$data1 		= mysqli_fetch_array($result1);
		
		if (intval($data1['ct']) > intval($_GET['current'])) {
			$res['result'] = true;			
			$res['current'] = intval($data1['ct']);			
		}
		else
		{
			$res['result'] = false;
		}
		echo json_encode($res);
		
	} else {

		echo "Parameter invalid..";

	}
?>
