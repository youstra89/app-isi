$('document').ready(function(){
	// console.log("Ca marche");
	// $("#supprimeEleve").on("click", function(){
	// 	if(confirm("Cette action est irreversible. Voulez-vous continuer ?")){
	// 		alert("Parfait");
	// 	}
	// });
	/* Cette fonction-ci va me permettre de remplir le tableau qui affiche la liste de tous les élèves d'un regime */
	/* Je le commence ici */
	// $('#table1').bind("load", function(){
		// var data = {};
		// var table = document.getElementById('table1');
		// table.length = 0;
		$.ajax({
			// url: Routing.generate('selection_eleves_regime', {as: data.as, regime: data.regime}, true),
			url: Routing.generate('selection_eleves_regime', true),
			type: 'GET',
			success: function (result){
			  for (var i in result) {
			    // var tr = document.createElement('tr');
					$("#table1 > tbody:last").append("<tr><td>" + result[i].id + "</td><td>" + result[i].matricule + "</td><td>" + result[i].nomFr + "</td><td>" + result[i].nomAr + "</td><td>" + result[i].sexe + "</td><td>" + result[i].dateNaissance['date'] + "</td><td></td></tr>"
				);

			  }
			},
			error: function (xhr, status, error){
				console.log(error)
			}
		});
		console.log("Ca marche");
		alert("C'est fini");
	// });
	/* Fin de la fonction qui permet de remplir le tableau affichant la liste de tous les élèves d'un regime */
});
