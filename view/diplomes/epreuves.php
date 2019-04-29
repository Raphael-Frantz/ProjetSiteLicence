<?php
// @need 

WebPage::addOnlineScript("var WEB_PATH = '".WEB_PATH."';");
WebPage::addJSScript("public/js/active-tooltips.js");
WebPage::addJSScript("public/js/tools.js");
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Gestion des épreuves</h2>
    <p class="lead mb-0">
      Ici, vous pouvez accéder aux épreuves de tout le diplôme.
    </p>
  </div>
</section>

<script>
var request = null;
var first = true;
function selectionDiplome() {
    if((request == null) && ($('#diplome').val() != -1)) {
        if(!first)
            $('#globalMsg').remove();
        first = false;
        $('#diplome').find('[value="-1"]').remove();
        
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'diplomes/ws.php',
                data: { 'mode' : 11, 'diplome' : $('#diplome').val() },
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                $('#semestre').empty();
                nbSemestres = parseInt(response['nbSemestres']);
                for(i = 1; i <= nbSemestres; i++) {
                    if(i == 1)
                        $("#semestre").append("<option value='" + i + "' checked>Semestre " + (i + response['minSemestre'] - 1) + "</option>");
                    else
                        $("#semestre").append("<option value='" + i + "'>Semestre " + (i + response['minSemestre'] - 1) + "</option>");
                }
                $('#semestre').show();
                request = null;
                selectionSemestre();
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                request = null;
            });
    }
}
function selectionSemestre() {
    if((request == null) && ($('#diplome').val() != -1) && ($('#semestre').val() != -1)) {
        if(!first)
            $('#globalMsg').remove();
        first = false;
        
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'diplomes/ws.php',
                data: { 'mode' : 13, 'diplome' : $('#diplome').val(), 'semestre' : $('#semestre').val() },
                dataType: 'html'
            });
        request.done(function (response, textStatus, jqXHR) {
                updateContent('contenu', response);
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                request = null;
            });
    }
}
function bloque(epreuve, etat) {
    if(request == null) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'epreuves/ws.php',
                data: { 'mode' : 5, 'epreuve' : epreuve, 'etat' : etat },
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response['code'] == 1) {
                    if(response['etat'] == 1) {
                        $('#blo_' + response['epreuve']).hide();
                        $('#deb_' + response['epreuve']).show();
                    }
                    else {
                        $('#blo_' + response['epreuve']).show();
                        $('#deb_' + response['epreuve']).hide();                        
                    }
                }
                else
                    displayErrorModal(response['erreur']);
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                displayErrorModal("Une erreur technique est survenue. Veuillez prévenir l'administrateur.");
                request = null;
            });
    }
}
function active(epreuve, etat) {
    if(request == null) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'epreuves/ws.php',
                data: { 'mode' : 1, 'epreuve' : epreuve, 'etat' : etat },
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response['code'] == 1) {
                    if(response['etat'] == 1) {
                        $('#act_' + response['epreuve']).hide();
                        $('#des_' + response['epreuve']).show();
                    }
                    else {
                        $('#act_' + response['epreuve']).show();
                        $('#des_' + response['epreuve']).hide();                        
                    }
                }
                else
                    displayErrorModal(response['erreur']);
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                displayErrorModal("Une erreur technique est survenue. Veuillez prévenir l'administrateur.");
                request = null;
            });
    }
}
function visible(epreuve, etat) {
    if(request == null) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'epreuves/ws.php',
                data: { 'mode' : 2, 'epreuve' : epreuve, 'etat' : etat },
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response['code'] == 1) {
                    if(response['etat'] == 1) {
                        $('#vis_' + response['epreuve']).hide();
                        $('#cac_' + response['epreuve']).show();
                    }
                    else {
                        $('#vis_' + response['epreuve']).show();
                        $('#cac_' + response['epreuve']).hide();                        
                    }
                }
                else
                    displayErrorModal(response['erreur']);
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
    <select <?php
if(!isset($data['nbSemestres'])) echo "style=\"display: none;\"";
?> class="form-control mr-2" id="semestre" name="semestre" onchange="javascript:selectionSemestre()">
<?php
if(isset($data['nbSemestres'])) {
    echo "<option value='-1'>Tous les semestres</option>";
    for($i = 1; $i <= $data['nbSemestres']; $i++) {
        echo "<option value='$i'";
        if($data['semestre'] == $i)
            echo " selected=\"selected\"";
        echo ">Semestre ".($i + $data['minSemestre'] - 1)."</option>";
    }
}
?>
    </select>
      </div>
    </form>
  </div>
</div>

<div id="contenu"></div>

<?php 
    WebPage::addOnReady("selectionDiplome();");
}