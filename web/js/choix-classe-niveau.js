function remplirClasseSearch()
{  
    $(".ChoiceClasseSearch").change(function() {
         
        var vendor = $("select.ChoiceNiveauSearch option:selected").val();
        var DATA = 'id=' + niveau;
         
        $.ajax({
            type: "POST",
            dataType: 'json',
            url:  "http://localhost/Symfony/web/app_dev.php/rempli-classe",
            data: DATA,
            success: function(msg)
            {
                $.each(msg, function(index, classe)
                {
                    $('.ChoiceClasseSearch').html('');
                    $('.ChoiceClasseSearch').append('<option value="'+ classe.classeId +'" selected="selected"> '+ classe[0].libellerClasseFr +' </option>');
                });
            }
        });
    }); 
}