<?php
// @need groupes la liste des groupes

WebPage::addOnlineScript("var WEB_PATH = '".WEB_PATH."';");
WebPage::addJSScript("public/js/tools.js");
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Attribution des groupes</h2>
    <p class="lead mb-0">Ici, vous trouverez la liste des étudiants et leurs groupes attribués. </p>
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
        <form id="importForm" method="post" action="<?php echo WEB_PATH; ?>groupes/importer.php" enctype="multipart/form-data">
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

<script>
var request = null;
function importer() {
    $('input[name="inputFichier"]').val("");
    $('#importation').modal("show");
}
function confImporter() {
    $('#importForm').submit();
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
        
        displayWaitModal();
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'groupes/ws.php',
                data: { 'mode' : 2, 'diplome' : $('#diplome').val(), 'semestre' : $('#semestre').val() },
                dataType: 'html'
            });
        request.done(function (response, textStatus, jqXHR) {
                $('[data-toggle="tooltip"]').tooltip('dispose');
                $('#contenu').empty().html(response);
                $('[data-toggle="tooltip"]').tooltip();
                request = null;
                hideWaitModal();
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                request = null;
                hideWaitModal();
            });   
    }
}
function selection(first) {
    if(request == null) {
        $('#diplome').show();
        $('#listeLink').hide();
        
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

        displayWaitModal();
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'groupes/ws.php',
                data: { 'mode' : 2, 'diplome' : $('#diplome').val(), 'semestre' : semestre },
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
                hideWaitModal();
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                request = null;
                $('#wait').modal('hide');
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
                for(i = 1; i <= response['nbSemestres']; i++)
                    $("#semestre").append("<option value='" + i + "'>Semestre " + (i + response['minSemestre'] - 1) + "</option>");
                request = null;
                selectionSemestre();
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                request = null;
                $('#wait').modal('hide');
            });
    }
}
function inscription(id, type) {
    if((request == null) && ($('#diplome').val() != -1) && ($('#semestre').val() != -1)) {
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
                url: WEB_PATH + 'groupes/ws.php',
                data: { 'mode' : 3, 
                        'diplome' : $('#diplome').val(), 
                        'semestre' : $('#semestre').val(),
                        'groupe' : grp,
                        'type' : type,
                        'etudiant' : id },
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response['code'] == -1)
                    alert("KO" + response['msg']);
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                request = null;
            });
    }
}
</script>

<form class="form-inline justify-content-center mt-2">
  <div class="form-group mb-2">
    <select class="form-control mr-2" id="diplome" name="diplome" onchange="javascript:selection(false)">
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
        echo "<option value='$i'";
        if($data['semestre'] == $i)
            echo " selected=\"selected\"";
        echo ">Semestre ".($i + $data['minSemestre'] - 1)."</option>";
    }
}
?>
    </select>
    <button data-toggle='tooltip' data-placement='top' title='Importer les groupes depuis un fichier CSV' type="button" id="idImport" class="btn btn-outline-primary mr-2" onclick="importer()">Importer</button>
    <a data-toggle='tooltip' data-placement='top' download title='Exporter les groupes dans un fichier CSV' id="idExport" class="btn btn-outline-primary mr-2" href="<?php echo WEB_PATH; ?>groupes/exporter.php">Exporter</a>
  </div>
</form>

<div id="contenu"></div>

<?php WebPage::addOnReady("selection(true);"); ?>