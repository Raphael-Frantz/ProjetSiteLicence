<?php
// @need EC l'EC
// @need epreuves la liste des épreuves

WebPage::addOnlineScript("var WEB_PATH = '".WEB_PATH."';");
WebPage::addJSScript("public/js/tools.js");
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Saisie des notes de <?php echo $data['EC']->getCode(); ?></h2>
    <p class="lead mb-0">
        Vous pouvez saisir les notes pour l'épreuve et le groupe sélectionné.
        Vous pouvez également importer ou exporter les notes.
    </p>
  </div>
</section>

<form id="controlForm" action="#" method="post"></form>

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
        <form id="importForm" method="post" action="<?php echo WEB_PATH; ?>notes/importer.php" enctype="multipart/form-data">
          <div class="form-group row">
            <label for="inputFichier" class="col-sm-2 col-form-label">Fichier</label>
            <div class="col-sm-10">
              <div class="form-group">
                <input type="file" class="form-control-file" name="inputFichier" id="inputFichier" lang="fr">
              </div>
              <small class="form-text text-muted">Sélectionnez un fichier CSV avec le bon format (numero, nom, prenom, email, note, max)</small>
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
function updateContent(id, content) {
    $('[data-toggle="tooltip"]').tooltip('dispose');
    $('#' + id).empty().html(content);
    $('[data-toggle="tooltip"]').tooltip();
}
function selectionEpreuve() {
    if(request == null) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'notes/ws.php',
                data: { 
                        'mode' : 2, 
                        'epreuve' : $('#epreuve').val(), 
                        'groupe' : $('#groupe').val()
                    },
                dataType: 'html'
            });
        request.done(function (response, textStatus, jqXHR) {
                updateContent('contenu', response);
                request = null;
                if($('#epreuve').val() == -1)
                    $('#idImport').hide();
                else
                    $('#idImport').show();
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                displayErrorModal("Une erreur technique est survenue. Veuillez prévenir l'administrateur.");
                request = null;
            });
    }    
}
function set(id, val) {
    $('input[name=note_' + id + ']').val(val);
    if($('input:text').filter(function() { return this.value != ""; }).length != 0) {
        $('#idSauve').show();
        $('#divSelection').hide();
    }
    else {
        $('#idSauve').hide();
        $('#divSelection').show();
    }
}
function check(id) {
    var val = $('input[name=note_' + id + ']').val();
    val = val.toUpperCase();
    
    if((val != 'ABI') && (val != 'ABJ')) {
        var re = /,/g;
        val = parseFloat(val.replace(re, '.'));
        if(isNaN(val)) val = "";
    }
    $('input[name=note_' + id + ']').val(val);
    
    if($('input:text').filter(function() { return this.value != ""; }).length != 0) {
        $('#idSauve').show();
        $('#divSelection').hide();
    }
    else {
        $('#idSauve').hide();
        $('#divSelection').show();
    }
}
function validerModif() {
    nbModifs = $('input:text').filter(function() { return this.value != ""; }).length;
    if(nbModifs == 0) {
        alert("Aucune modification à effectuer.");
    }
    else {
        $('#formModif').submit();
    }
}
</script>

<div class="container">
  <form class="form-inline justify-content-between mt-2">
    <div id='divSelection' class="form-group mb-2">
      <select class="form-control mr-2" id="epreuve" name="epreuve" onchange="javascript:selectionEpreuve()">
<?php
echo "<option value=\"-1\">Toutes</option>";
foreach($data['epreuves'] as $epreuve) {
    echo "<option value=\"{$epreuve['id']}\"";
    if(isset($data['epreuve']) && ($data['epreuve']->getId() == $epreuve['id']))
        echo " selected=\"selected\"";
    echo ">{$epreuve['intitule']}</option>";
}
?>
      </select>
      <select class="form-control mr-2" id="groupe" name="groupe" onchange="javascript:selectionEpreuve()">
<?php
if(count($data['groupes']) > 0) {
    for($i = 0; $i < count($data['groupes']); $i++) {
        $type = Groupe::type2String($data['groupes'][$i]['type']);
        echo "<option value='{$data['groupes'][$i]['id']}'";
        if(isset($data['groupeEC']) && ($data['groupes'][$i]['id'] == $data['groupeEC']))
            echo " selected=\"selected\"";
        echo ">{$data['groupes'][$i]['intitule']} ($type)</option>";
    }
}
else {
    echo "<option value='-1'>Aucun groupe</option>";
}
?>
      </select>
      <button data-toggle='tooltip' data-placement='top' title='Importer les notes depuis un fichier CSV' type="button" id="idImport" class="btn btn-outline-primary mr-2" onclick="javascript:importer()">Importer</button>
      <a data-toggle='tooltip' data-placement='top' title='Exporter les notes dans un fichier CSV' download id="idExport" class="btn btn-outline-primary mr-2" href="<?php echo WEB_PATH; ?>notes/exporter.php">Exporter</a>
    </div>
    <div class="form-group mb-2">
      <button type='button' data-toggle='tooltip' data-placement='top' style='display: none;' title='Sauvegarder les modifications' id="idSauve" class="btn btn-outline-primary mr-2" onclick="javascript:validerModif()">Sauvegarder</button>
    </div>
    <div class="form-group mb-2">
      <a id="listeLink" data-toggle='tooltip' data-placement='top' title='Retourner à la liste des ECs' class="btn btn-outline-primary mr-2" href="<?php echo WEB_PATH; ?>ecs/liste.php">Retour</a>
    </div>
  </form>
</div>

<form id='formModif' action='<?php echo WEB_PATH; ?>notes/ajouter.php' method='post'>
<div id="contenu"></div>
</form>

<?php WebPage::addOnReady("selectionEpreuve();"); ?>