<?php
// @need 

WebPage::addOnlineScript("var WEB_PATH = '".WEB_PATH."';");
WebPage::addJSScript("public/js/active-tooltips.js");
WebPage::addJSScript("public/js/tools.js");
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Liste des tuteurs</h2>
    <p class="lead mb-0">
      Ici, vous trouverez la liste des tuteurs par diplôme.
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
                data: { 'mode' : 4, 'diplome' : $('#diplome').val() },
                dataType: 'html'
            });
        request.done(function (response, textStatus, jqXHR) {
                $('#contenu').empty().html(response);
                if($('#tableTuteurs tr').length <= 1) $('#emailBtn').hide();
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                request = null;
            });   
    }
}
function desinscription(id) {
    if(!first)
            $('#globalMsg').remove();
        first = false;

    if((request == null) && ($('#diplome').val() != -1)) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'tutorat/ws.php',
                data: { 'mode' : 6, 'enseignant' : id },
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response['code'] == 1) {
                    deleteElement('ligne' + response['enseignant']);
                    
                    if($('#tableTuteurs tr').length <= 1) {
                        $('#emailBtn').hide();
                        $('#contenu').empty()
                                     .append("<div class='media alert-danger text-center' id='message'>" +
                                             "<div class='media-body'><p class='lead mb-0'>" +
                                             "Il n'y a plus de tuteur dans ce diplôme." +
                                             "</p></div></div>");
                    }
                }
                else
                    displayErrorModal(response['msg']);
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                displayErrorModal("Une erreur technique est survenue. Veuillez prévenir l'administrateur.");
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
        if(isset($data['diplome']) && ($data['diplome'] == $diplome['id']))
            echo " selected=\"selected\"";
        echo ">{$diplome['intitule']}</option>";
    }
?>
        </select>
        <a id='emailBtn' data-toggle='tooltip' data-placement='top' title='Envoyer un mail à tous les tuteurs' class="btn btn-outline-primary mr-2" href="javascript:displayEmailModal()">Envoyer un email</a>
        <a data-toggle='tooltip' data-placement='top' title='Ajouter un tuteur pour ce diplôme' class="btn btn-outline-primary mr-2" href="<?php echo WEB_PATH; ?>tutorat/ajouter.php">Ajouter</a>
      </div>
    </form>
  </div>
</div>

<div id="contenu"></div>

<?php 
    WebPage::addOnReady("selectionDiplome();");
}