$('document').ready(function() {

    /*
       A chaque selection de niveau
     */
    $('#form_niveau').on('change', function() {
        var data = {}; // initialisation de l'objet data
        data.anneeId = $('#anneeId').val();
        data.annexeId = $('#annexeId').val();
        data.niveauId = $(this).val();
        var select = document.getElementById("form_classe");
        select.length = 0;

        $.ajax({
            url: Routing.generate('remplir_select_classe', { anneeId: data.anneeId, annexeId: data.annexeId, niveauId: data.niveauId }, true),
            type: 'GET',
            success: function(result) {
                // var dataJSON = JSON.parse(result);
                for (var k in result) {
                    var option = document.createElement("option");
                    option.value = result[k].id;
                    option.text = result[k].name;
                    select.add(option, select[k]);
                }
            },
            error: function(xhr, status, error) {
                console.log(error)
            }
        });
    });


    /*var callAjax = function (data, callback) {
      $.ajax({
        url: Routing.generate('route_name', { anneeId: data.anneeId, niveauId: data.niveauId }, true),
        type: 'GET',
        success: function (result) {
          callback(result);
        },
        error: function (xhr, status, error) {
          console.log(error)
        }
      });
    };*/
});