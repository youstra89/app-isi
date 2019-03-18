$(function () {
    jQuery(document).ready(function() {
        console.log("jQuery est prêt !");

        $(".niveauclass").change(function() {
            mafonctionchange('classe','niveau');
        }).trigger('change');

        function mafonctionchange(selecteur,selecteurparent)
        {
            $.ajax({
                url: 'http://localhost/Symfony/web/app_dev.php/form/rempli',
                type: 'POST',
                data:
                {
                    id : $("select."+selecteurparent+"class option:selected").val(),
                    select : selecteur
                },
                dataType: 'json',
                success: function(reponse) {

                    $('.'+selecteur+'class').empty();
                    $.each(reponse, function(index, element) {
                        $('.' + selecteur + 'class').append('<option value="'+ element.id +'" selected="selected"> '+ element.nom +' </option>');
                    });

                    if (selecteur == 'niveau') {
                        mafonctionchange('classe','niveau');
                    }
                }
            });
        }
    });
});
