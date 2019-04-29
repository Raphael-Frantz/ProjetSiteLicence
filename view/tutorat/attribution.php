<?php
// @need 

WebPage::addOnlineScript("var WEB_PATH = '".WEB_PATH."';");
WebPage::addJSScript("public/js/active-tooltips.js");
WebPage::addJSScript("public/js/tools.js");
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Attribution des tuteurs</h2>
    <p class="lead mb-0">
      Ici, vous trouverez les étudiants et leur tuteur.
    </p>
  </div>
</section>

<script>
var request = null;
var first = true;
function deleteElement(id) {
    $('[data-toggle="tooltip"]').tooltip('dispose');
    $('#' + id).remove();
    $('[data-toggle="tooltip"]').tooltip();    
}
function selectionDiplome() {
    if((request == null) && ($('#diplome').val() != -1)) {
        if(!first)
            $('#globalMsg').remove();
        first = false;
        
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'tutorat/ws.php',
                data: { 'mode' : 4, 'diplome' : $('#diplome').val(), 'json' : true },
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                $('#tuteur').empty();
                if(response['tuteurs'].length == 0)
                    $('#tuteur').append("<option value='-1' checked>Aucun</option>");
                else {                
                    $('#tuteur').append("<option value='-1' checked>Tous les tuteurs</option>");
                    $.each(response['tuteurs'], function(id, val) {
                        $('#tuteur').append("<option value='" + val['id'] + "'>" + val['nom'] + "</option>"); 
                    });
                }
                request = null;
                selectionTuteur();
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                displayErrorModal("Une erreur technique est survenue. Veuillez prévenir l'administrateur.");
                request = null;
            });   
    }
}
function selectionTuteur() {
    if((request == null) && ($('#diplome').val() != -1)) {
        if(!first)
            $('#globalMsg').remove();
        first = false;
        
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'tutorat/ws.php',
                data: { 'mode' : 9, 'diplome' : $('#diplome').val(), 'tuteur' : $('#tuteur').val() },
                dataType: 'html'
            });
        request.done(function (response, textStatus, jqXHR) {
                $('#contenu').empty().append(response);
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                displayErrorModal("Une erreur technique est survenue. Veuillez prévenir l'administrateur.");
                request = null;
            });   
    }
}
function inscription(id) {
    if((request == null) && ($('#diplome').val() != -1)) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'tutorat/ws.php',
                data: { 'mode' : 10, 
                        'diplome' : $('#diplome').val(), 
                        'tuteur' : $('#tut_' + id).val(),
                        'etudiant' : id },
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response['code'] == -1)
                    displayErrorModal(response['msg']);
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                request = null;
            });
    }
}
function desinscription(id) {
    if((request == null) && ($('#diplome').val() != -1)) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'tutorat/ws.php',
                data: { 'mode' : 10, 
                        'diplome' : $('#diplome').val(), 
                        'tuteur' : -1,
                        'etudiant' : id },
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response['code'] == -1)
                    displayErrorModal(response['msg']);
                else {
                    deleteElement('ligne' + response['etudiant']);
                    
                    if($('#tableEtudiants tr').length <= 1) {
                        $('#emailBtn').hide();
                        $('#contenu').empty()
                                     .append("<div class='media alert-danger text-center' id='message'>" +
                                             "<div class='media-body'><p class='lead mb-0'>" +
                                             "Il n'y a plus d'étudiant pour ce tuteur." +
                                             "</p></div></div>");
                    }
                }
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                request = null;
            });
    }
}
</script>

<?php
if(!isset($data['diplomes']) || (count($data['diplomes']) == 0)) {
    $msg = "Vous n'avez accès à aucun diplôme.";
    echo <<<HTML
<div class="media alert-danger text-center" id="message">
  <div class="media-body">
    <p class="lead mb-0" id="contenuMessage">{$msg}</p>
  </div>
</div>
HTML;
}
else {
?>
<div class="container">
  <div class="justify-content-between btn-toolbar mb-3">
    <form class="form-inline justify-content-center mt-2">
      <div class="form-group mb-2">
        <select class="form-control mr-2" id="diplome" name="diplome" onchange="javascript:selectionDiplome()">
<?php
    foreach($data['diplomes'] as $diplome) {
        echo "<option value=\"{$diplome['id']}\"";
        if(isset($data['diplome']) && ($data['diplome']->getId() == $diplome['id']))
            echo " selected=\"selected\"";
        echo ">{$diplome['intitule']}</option>";
    }
?>
        </select>
        <select class="form-control mr-2" id="tuteur" name="tuteur" onchange="javascript:selectionTuteur()">
          <option value="-1">Tous les tuteurs</option>
<?php
    foreach($data['tuteurs'] as $tuteur) {
        echo "<option value=\"{$tuteur['id']}\"";
        if(isset($data['tuteur']) && ($data['tuteur'] == $tuteur['id']))
            echo " selected=\"selected\"";
        echo ">{$tuteur['nom']}</option>";
    }
?>
        </select>
        <a data-toggle='tooltip' data-placement='top' title='Exporter la liste dans un fichier CSV' download id="idExport" class="btn btn-outline-primary mr-2" href="<?php echo WEB_PATH; ?>tutorat/exportertuteurs.php">Exporter</a>
      </div>
    </form>
  </div>
</div>

<div id="contenu"></div>

<?php 
    WebPage::addOnReady("selectionDiplome();");
}