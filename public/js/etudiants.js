var request = null;
function selectionDiplome(first) {
    if(request == null) {
        semestre = $('#semestre').val();
        if($('#diplome').val() == -1) {
            $('#semestre').empty().hide();
            $('#ajouterLink').show();
            $('#inscrireLink').hide();
            semestre = -1;
        }
        else {
            if(!first) {
                $('#semestre').empty().hide();
                $('#semestre').show();
                semestre = -1;
            }
            if(semestre != -1) {
                $('#inscrireLink').show();
                $('#ajouterLink').show();
            }
            else {
                $('#inscrireLink').hide();
                $('#ajouterLink').hide();
            }
        }
        
        if(!first)
            $('#globalMsg').remove();
                
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'etudiants/ws.php',
                data: { 'mode' : 1, 'diplome' : $('#diplome').val(), 'semestre' : semestre },
                dataType: 'html'
            });
        request.done(function (response, textStatus, jqXHR) {
                $('[data-toggle="tooltip"]').tooltip('dispose');
                $('#contenu').empty().html(response);
                $('[data-toggle="tooltip"]').tooltip();
                request = null;
                if(($('#diplome').val() != -1) && !first) {
                    updateSemestre();
                }
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                request = null;
            });   
    }
}
function selectionSemestre() {
    if((request == null) && ($('#diplome').val() != -1)) {
        $('#globalMsg').remove();
        
        if($('#semestre').val() != -1) {
            $('#inscrireLink').show();
            $('#ajouterLink').show();
        }
        else {
            $('#inscrireLink').hide();
            $('#ajouterLink').hide();
        }
        
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'etudiants/ws.php',
                data: { 'mode' : 1, 'diplome' : $('#diplome').val(), 'semestre' : $('#semestre').val() },
                dataType: 'html'
            });
        request.done(function (response, textStatus, jqXHR) {
                $('[data-toggle="tooltip"]').tooltip('dispose');
                $('#contenu').empty().html(response);
                $('[data-toggle="tooltip"]').tooltip();
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                request = null;
            });   
    }
}
function desinscription(id) {
    $('#globalMsg').remove();
    if((request == null) && ($('#diplome').val() != -1)) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'etudiants/ws.php',
                data: { 'mode' : 4, 'diplome' : $('#diplome').val(), 'semestre' : $('#semestre').val(), 'etudiant' : id },
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response['code'] == 1) {
                    $('[data-toggle="tooltip"]').tooltip('dispose');
                    $('#ligne' + response['etudiant']).remove();
                    $('[data-toggle="tooltip"]').tooltip();
                }
                else {
                    // #TODO# Message d'erreur
                }
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                request = null;
            });
    }
}
function updateSemestre() {
    if((request == null) && ($('#diplome').val() != -1)) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'diplomes/ws.php',
                data: { 'mode' : 11, 'diplome' : $('#diplome').val() },
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                $("#semestre").empty();
                $("#semestre").append("<option value='-1'>Tous les semestres</option>");
                for(i = 1; i <= response['nbSemestres']; i++)
                    $("#semestre").append("<option value='" + i + "'>Semestre " + i + "</option>");
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                request = null;
            });
    }
}
function importer() {
    $('input[name="inputFichier"]').val("");
    $('#importation').modal("show");
}
function confImporter() {
    $('#wait').modal('show');
    $('#importForm').submit();
}