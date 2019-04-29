<?php
// @need 

WebPage::addOnlineScript("var WEB_PATH = '".WEB_PATH."';");
WebPage::addJSScript("public/js/tools.js");
WebPage::addJSScript("public/js/active-tooltips.js");
WebPage::addJSScript("public/js/ip.js");
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">IP des étudiants</h2>
    <p class="lead mb-0">
      Ici, vous trouverez les inscriptions pédagogiques des étudiants.
      Cliquez sur une case pour modifier la valeur.
    </p>
  </div>
</section>

<div class="modal" id="wait" tabindex="-1" role="dialog" aria-labelledby="waitModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-body text-center">
        <img src='<?php echo WEB_PATH; ?>public/pictures/urca.gif'/><br/>
        Patience, traitement en cours...
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="importation" tabindex="-1" role="dialog" aria-labelledby="importationModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="importationModalLabel">Importation</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="importForm" method="post" action="<?php echo WEB_PATH; ?>tutorat/importer.php" enctype="multipart/form-data">
          <div class="form-group row">
            <label for="inputFichier" class="col-sm-2 col-form-label">Fichier</label>
            <div class="col-sm-10">
              <div class="form-group">
                <input type="file" class="form-control-file" name="inputFichier" id="inputFichier" lang="fr">
              </div>
              <small class="form-text text-muted">Sélectionnez un fichier CSV avec le bon format</small>
            </div>
          </div>           
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" onclick="confImporter()" data-dismiss="modal">Importer</button>
        <button type="button" class="btn btn-primary" data-dismiss="modal">Annuler</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modification" tabindex="-1" role="dialog" aria-labelledby="modificationModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modificationModalLabel">Modification</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="currentEC" name="currentEC" value=""/>
        <input type="hidden" id="currentEtu" name="currentEtu" value=""/>      
        <div class="form-group row">
          <label for="inputNom" class="col-sm-2 col-form-label">Etudiant</label>
          <div class="col-sm-10">
            <input class="form-control" id='inputNom' type="text" placeholder="Nom de l'étudiant" readonly/>
          </div>
        </div>
        <div class="form-group row">
          <label for="inputEC" class="col-sm-2 col-form-label">EC</label>
          <div class="col-sm-10">
            <input class="form-control" id='inputEC' type="text" placeholder="Nom de l'EC" readonly/>
          </div>
        </div>
        <div class="form-group row">
          <label for="inputInscription" class="col-sm-2 col-form-label">Inscription</label>
          <div class="col-sm-10">
            <select class="form-control" name="choixInscription" id="inputInscription" onchange="javascript:selectionInscription()">
              <option value="<?php echo InscriptionECModel::TYPE_NONINSCRIT; ?>">Non inscrit</option>
              <option value="<?php echo InscriptionECModel::TYPE_INSCRIT; ?>">Inscrit</option>
              <option value="<?php echo InscriptionECModel::TYPE_VALIDE; ?>">Validé</option>
            </select>
          </div>
        </div>
        <div class="form-group row" id="noteGroup">
          <label for="inputNote" class="col-sm-2 col-form-label">Note</label>
          <div class="col-sm-10">
            <input class="form-control" name="note" id="inputNote" type="text"/>
          </div>
        </div>
        <div class="form-group row" id="baremeGroup">
          <label for="inputBareme" class="col-sm-2 col-form-label">Barème</label>
          <div class="col-sm-10">
            <input class="form-control" name="bareme" id="inputBareme" type="text" value="20"/>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" onclick="modifierIPConf()" data-dismiss="modal">Modifier</button>
        <button type="button" class="btn btn-primary" data-dismiss="modal">Annuler</button>
      </div>
    </div>
  </div>
</div>

<?php 
if(isset($data['nbSemestres']))
    WebPage::addOnlineScript("var nbSemestres = {$data['nbSemestres']};");
else
    WebPage::addOnlineScript("var nbSemestres = -1;");
if(isset($data['minSemestre']))
    WebPage::addOnlineScript("var minSemestre = {$data['minSemestre']};");
else
    WebPage::addOnlineScript("var minSemestre = -1;");
?>

<script>
var request = null;
var first = true;
function selectionDiplome() {
    if((request == null) && ($('#diplome').val() != -1)) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'diplomes/ws.php',
                data: { 'mode' : 11, 'diplome' : $('#diplome').val() },
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                $("#semestre").empty();
                nbSemestres = parseInt(response['nbSemestres']);
                minSemestre = parseInt(response['minSemestre']);
                for(i = 1; i <= nbSemestres; i++) {
                    if(i == 1)
                        $("#semestre").append("<option value='" + i + "' checked>Semestre " + (i + minSemestre - 1) + "</option>");
                    else
                        $("#semestre").append("<option value='" + i + "'>Semestre " + (i + minSemestre - 1) + "</option>");
                }
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
    $("#buttonBar").empty();
    
    sem = parseInt($('#semestre').val());
    debut = 0;
    if(sem % 2 == 0)
        debut = 2;
    else
        debut = 1;    
    
    for(i = debut; i <= nbSemestres; i = i + 2) {
        if(i == sem)
            $("#buttonBar").append("<label class='btn btn-secondary active'>" +
                                   "<input type='radio' name='annee' id='S_" + i + 
                                   "' autocomplete='off' onchange='javascript:selectionSemestreBouton(" + i + ");'> S" + i +
                                   "</label>");
        else
            $("#buttonBar").append("<label class='btn btn-secondary'>" +
                                   "<input type='radio' name='annee' id='S_" + i + 
                                   "' autocomplete='off' onchange='javascript:selectionSemestreBouton(" + i + ");'> S" + i +
                                   "</label>");
    }
    
    selectionSemestreBouton(sem);
}
function selectionSemestreBouton(sem) {
    if((request == null) && ($('#diplome').val() != -1) && ($('#semestre').val() != -1)) {
        if(!first)
            $('#globalMsg').remove();
        first = false;
        
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'tutorat/ws.php',
                data: { 'mode' : 1, 'diplome' : $('#diplome').val(), 'semestre' : $('#semestre').val(), 'semestreIns' : sem },
                dataType: 'html'
            });
        request.done(function (response, textStatus, jqXHR) {
                $('#contenu').empty().html(response);
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
        <select class="form-control mr-2" id="semestre" name="semestre" onchange="javascript:selectionSemestre()">
<?php
    if(isset($data['nbSemestres'])) {
        for($i = 1; $i <= $data['nbSemestres']; $i++) {
            echo "<option value=\"$i\"";
            if($data['semestre'] == $i)
                echo " selected=\"selected\"";
            echo ">Semestre ".($i + $data['minSemestre'] - 1)."</option>";
        }
    }
?>        
        </select>
<?php
if(UserModel::estRespDiplome()) {
?>
        <button data-toggle='tooltip' data-placement='top' title='Importer les IP depuis un fichier CSV' type="button" id="idImport" class="btn btn-outline-primary mr-2" onclick="importer()">Importer</button>
        <a data-toggle='tooltip' data-placement='top' title='Exporter les IP dans un fichier CSV' id="idExport" class="btn btn-outline-primary mr-2" href="<?php echo WEB_PATH; ?>tutorat/exporter.php">Exporter</a>
<?php
}
?>
      </div>
    </form>
  
    <div id="buttonBar" class="btn-group btn-group-toggle" style="padding-top: 10px; padding-bottom: 10px" data-toggle="buttons">
    </div>
  </div>
</div>

<div id="contenu"></div>

<?php 
    WebPage::addOnReady("selectionSemestre();");
}
?>