var request = null;
function modifierIP(idEtu, nomEtu, idEC, codeEC) {   
    if(request == null) {
        $('#inputNom').val(nomEtu);
        $('#inputEC').val(codeEC);
        $('#currentEC').val(idEC);
        $('#currentEtu').val(idEtu);
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'tutorat/ws.php',
                data: { 'mode' : 2, 'etudiant' : idEtu, 'EC' : idEC },
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response['code'] == 1) {
                    $("#inputInscription").val(response['type'])
                    $("#inputNote").val(response['note']);
                    if(response['bareme'] == 0)
                        $("#inputBareme").val(20);
                    else
                        $("#inputBareme").val(response['bareme']);
                    if(response['type'] == 2) {
                        $('#noteGroup').show();
                        $('#baremeGroup').show();
                    }
                    else {
                        $('#noteGroup').hide();
                        $('#baremeGroup').hide();
                    }
                    $('#modification').modal("show");
                }
                else {
                    if(response['code'] == -2)
                        document.location.href = WEB_PATH;
                    else
                        displayErrorModal(response['erreur']);
                }
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                request = null;
            });   
    }    
}
function modifierIPConf() {
    if(request == null) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'tutorat/ws.php',
                data: { 'mode' : 3, 
                        'etudiant' : $('#currentEtu').val(), 
                        'EC' : $('#currentEC').val(), 
                        'type' : $('#inputInscription').val(),
                        'note' : $('#inputNote').val(),
                        'bareme' : $('#inputBareme').val() },
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response['code'] == 1) {
                    if(response['type'] == -1)
                        $('#' + response['etudiant'] + "_" + response['EC']).html("");
                    else if(response['type'] == 1)
                        $('#' + response['etudiant'] + "_" + response['EC']).html("X");
                    else
                        $('#' + response['etudiant'] + "_" + response['EC']).html(response['note']+"/"+response['bareme']);
                }
                else {
                    if(response['code'] == -2)
                        document.location.href = WEB_PATH;
                    else
                        displayErrorModal(response['erreur']);
                }
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                request = null;
            });  
    }
}
// Suivant le choix
function selectionInscription() {
    if($("#inputInscription").val() == 2) {
        $('#noteGroup').show();
        $('#baremeGroup').show();
    }
    else {
        $('#noteGroup').hide();
        $('#baremeGroup').hide();
    }
}