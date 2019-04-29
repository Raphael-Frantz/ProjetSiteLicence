<?php
// @need 

WebPage::addOnlineScript("var WEB_PATH = '".WEB_PATH."';");
WebPage::addJSScript("public/js/active-tooltips.js");
WebPage::addJSScript("public/js/tools.js");
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Jutificatifs d'absence</h2>
    <p class="lead mb-0">
      Vous trouverez ici les justificatifs d'absence des étudiants.
    </p>
  </div>
</section>

<script>
var request = null;
var first = true;
function infos(id) {
    if((request == null) && ($('#diplome').val() != -1)) {
        if(!first)
            $('#globalMsg').remove();
        first = false;
        
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'presentiel/ws.php',
                data: { 'mode' : 3, 'diplome' : $('#diplome').val(), 'justif' : id },
                dataType: 'html'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response == "")
                    document.location.href = WEB_PATH;
                
                displayInfoModal("Informations", response);
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                displayErrorModal("Une erreur technique est survenue. Veuillez prévenir l'administrateur.");
                request = null;
            });   
    }
}
function selectionDiplome() {
    if((request == null) && ($('#diplome').val() != -1)) {
        if(!first)
            $('#globalMsg').remove();
        first = false;
        
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'presentiel/ws.php',
                data: { 'mode' : 1, 'diplome' : $('#diplome').val() },
                dataType: 'html'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response == "")
                    document.location.href = WEB_PATH;
                
                updateContent('contenu', response);
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                displayErrorModal("Une erreur technique est survenue. Veuillez prévenir l'administrateur.");
                request = null;
            });   
    }
}
function redirect(id, url) {
    if((request == null) && ($('#diplome').val() != -1)) {
        if(!first)
            $('#globalMsg').remove();
        first = false;
        
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'presentiel/ws.php',
                data: { 'mode' : 2, 
                        'diplome' : $('#diplome').val(), 
                        'justif' : id },
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response['code'] == -2)
                    document.location.href = WEB_PATH;
                else if(response['code'] == -1)
                    displayErrorModal(response['erreur']);
                else
                    document.location.href = WEB_PATH + url;
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
        if(isset($data['diplome']) && ($data['diplome'] == $diplome['id']))
            echo " selected=\"selected\"";
        echo ">{$diplome['intitule']}</option>";
    }
?>
            </select>
            <a data-toggle='tooltip' data-placement='top' download title='Exporter la liste dans un fichier CSV' id="idExport" class="btn btn-outline-primary mr-2" href="<?php echo WEB_PATH; ?>presentiel/exporter.php">Exporter</a>
            <a data-toggle='tooltip' data-placement='top' title='Ajouter un justificatif' id="idExport" class="btn btn-outline-primary mr-2" href="<?php echo WEB_PATH; ?>presentiel/ajouter.php">Ajouter</a>
          </div>
        </form>
      </div>
    </div>
    
    <div id="contenu"></div>
<?php
    WebPage::addOnReady("selectionDiplome();");
}
?>
  </div>
</section>