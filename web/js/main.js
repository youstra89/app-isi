$("document").ready(function() {
    $(".matricule").keyup(function() {
        var annexeId = $("#annexeId").val()
        if ($(this).val().length >= 9 && $(this).focusout) {
            $.ajax({
                type: 'get',
                url: Routing.generate('eleve', { matricule: $(this).val(), annexeId: annexeId }, true),
                beforeSend: function() {
                    console.log('ça charge');
                },
                success: function(data) {
                    $(".nomFr").val(data.nomFr);
                    $(".pnomFr").val(data.pnomFr);
                    $(".dateNaissance").val(data.dateNaissance["date"]);
                    console.log('nomFr Ok')
                }
            });
        } else {
            $(".nomFr").val('');
            $(".pnomFr").val('');
            $(".dateNaissance").val('');
        }
    });
});