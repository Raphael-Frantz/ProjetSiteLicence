<?php
// @need EC l'EC (objet)
// @need groupes les groupes de l'EC
// @need groupeEC le groupe actuel

WebPage::addOnlineScript("var WEB_PATH = '".WEB_PATH."';");
WebPage::addJSScript("public/js/tools.js");
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Liste des étudiant(e)s de chaque groupe de <?php echo $data['EC']->getCode(); ?></h2>
    <p class="lead mb-0">
      Vous pouvez consulter ici la liste des étudiant(e)s de chaque groupe.
    </p>
  </div>
</section>

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
        <form id="importForm" method="post" action="<?php echo WEB_PATH; ?>groupesec/importergroupes.php" enctype="multipart/form-data">
          <div class="form-group row">
            <label for="inputFichier" class="col-sm-2 col-form-label">Fichier</label>
            <div class="col-sm-10">
              <div class="form-group">
                <input type="file" class="form-control-file" name="inputFichier" id="inputFichier" lang="fr">
              </div>
              <small class="form-text text-muted">Sélectionnez un fichier CSV avec le bon format (numero, nom, prenom, email, CM, TD, TP)</small>
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

<form id="controlForm" action="" method="post"></form>

<script>
var request = null;
var first = true;
function importer() {
    $('input[name="inputFichier"]').val("");
    $('#importation').modal("show");
}
function confImporter() {
    $('#importForm').submit();
}
function updateContent(id, content) {
    $('[data-toggle="tooltip"]').tooltip('dispose');
    $('#' + id).empty().html(content);
    $('[data-toggle="tooltip"]').tooltip();
}
function deleteElement(id) {
    $('[data-toggle="tooltip"]').tooltip('dispose');
    $('#' + id).remove();
    $('[data-toggle="tooltip"]').tooltip();    
}
function selectionGroupe() {
    if((request == null)) {//} && ($('#groupe').val() != -1)) {
        if(!first)
            $('#globalMsg').remove();
        first = false;
        
        if($('#groupe').val() != -1) {
            $('#inscrireLink').show();
            $('#idImport').hide();
            $('#ficheLink').show();
        }
        else {
            $('#inscrireLink').hide();
            $('#ficheLink').hide();
            $('#idImport').show();
        }
        
        displayWaitModal();
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'groupesec/ws.php',
                data: { 
                        'mode' : 2, 
                        'groupe' : $('#groupe').val()
                    },
                dataType: 'html'
            });
        request.done(function (response, textStatus, jqXHR) {
                updateContent('contenu', response);
                request = null;
                hideWaitModal();
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                hideWaitModal();
                displayErrorModal("Une erreur technique est survenue. Veuillez prévenir l'administrateur.");
                console.log(jqXHR);
                request = null;
            });
    }
}
function desinscription(etudiant) {
    if((request == null) && ($('#groupe').val() != -1)) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'groupesec/ws.php',
                data: { 
                        'mode' : 4, 
                        'groupe' : $('#groupe').val(),
                        'etudiant' : etudiant
                    },
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response['code'] == 1) {
                    deleteElement('ligne' + response['etudiant']);
                    
                    if($('#tableEtudiants tr').length <= 1) {
                        $('#contenu').empty()
                                     .append("<div class='media alert-danger text-center' id='message'>" +
                                             "<div class='media-body'><p class='lead mb-0'>" +
                                             "Il n'y a plus d'étudiant dans le groupe." +
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
function inscriptionGroupe(id, type) {
    if(request == null) {
        grp = -1;
        switch(type) {
            case 1:
                grp = $('#CM_' + id).val();
                break;
            case 2:
                grp = $('#TD_' + id).val();
                break;
            case 3:
                grp = $('#TP_' + id).val();
                break;
        }
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'groupesec/ws.php',
                data: { 'mode' : 6,
                        'groupeEC' : grp,
                        'type' : type,
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
</script>

<div class="container">
<form class="form-inline justify-content-between mt-2">
  <div class="form-group mb-2">
    <select class="form-control mr-2" id="groupe" name="groupe" onchange="javascript:selectionGroupe()">
<?php
if(!isset($data['groupes']) || (count($data['groupes']) == 0)) {
    echo "<option value='-1'>Aucun groupe</option>";
}
else {
    echo "<option value='-1'>Tous les groupes</option>";
    foreach($data['groupes'] as $groupe) {
        $type = Groupe::type2String($groupe['type']);
        echo "<option value=\"{$groupe['id']}\"";
        if(isset($data['groupeEC']) && ($data['groupeEC'] == $groupe['id']))
            echo " selected=\"selected\"";
        echo ">{$groupe['intitule']} ($type)</option>";
    }
}
?>
    </select>
<?php
if(UserModel::estRespEC($data['EC']->getId()) ||
   RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $data['EC']->getId())) {
?>
    <a id="inscrireLink" data-toggle='tooltip' data-placement='top' title='Inscrire des étudiants à ce groupe' class="btn btn-outline-primary mr-2" href="<?php echo WEB_PATH; ?>groupesec/inscrire.php">Inscrire</a>
<?php
}
?>
    <a data-toggle='tooltip' data-placement='top' title='Exporter les étudiants dans un fichier CSV' id="idExport" class="btn btn-outline-primary mr-2" href="<?php echo WEB_PATH; ?>groupesec/exporter.php">Exporter</a>
    <button data-toggle='tooltip' data-placement='top' title='Importer les groupes depuis un fichier CSV' type="button" id="idImport" class="btn btn-outline-primary mr-2" onclick="importer()">Importer</button>
    <a id='mailtoall' data-toggle='tooltip' data-placement='top' title='Envoyer un mail à tous les étudiants' class="btn btn-outline-primary mr-2" href="javascript:displayEmailModal()">Envoyer un email</a>
    <a id='ficheLink' data-toggle='tooltip' download data-placement='top' title='Imprimer la fiche de présence' class="btn btn-outline-primary mr-2" href="<?php echo WEB_PATH; ?>groupesec/fiche.php" target="_blank">Fiche présence</a>
  </div>
  <div class='form-group mb-2'>
    <a id="listeLink" data-toggle='tooltip' data-placement='top' title='Retourner à la liste des ECs' class="btn btn-outline-primary mr-2" href="<?php echo WEB_PATH; ?>ecs/liste.php">Retour</a>
  </div>
</form>
</div>

<div id="contenu"></div>

<?php WebPage::addOnReady("selectionGroupe();"); ?>