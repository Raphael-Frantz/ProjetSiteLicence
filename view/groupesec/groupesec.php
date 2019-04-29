<?php
// @need groupes la liste des groupes

WebPage::addJSScript("public/js/active-tooltips.js");
WebPage::addJSScript("public/js/tools.js");
WebPage::addOnlineScript("var WEB_PATH = '".WEB_PATH."';");

$respDip = RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $data['EC']->getId());

?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Gestion des groupes de <?php echo $data['EC']->getCode(); ?></h2>
    <p class="lead mb-0">Ici, vous trouverez la liste de tous les groupes de l'EC.</p>
  </div>
</section>

<form id="controlForm" action="" method="post"></form>

<div class="modal fade" id="importationModal" tabindex="-1" role="dialog" aria-labelledby="importationModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="importationModalLabel">Importation</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="importationModalBody">
        <form id="importForm" method="post" action="<?php echo WEB_PATH; ?>groupesec/importer.php">
          <div class="form-group row">
            <label for="inputType" class="col-sm-4 col-form-label">Type de groupe</label>
            <div class="col-sm-8">
              <select class="form-control" id="inputType" name="inputType" onchange="javascript:selectionType()">
                <option value='-1' selected='selected'>Faites un choix</option>
                <option value='1'>Groupe d'un diplôme</option>
                <option value='2'>Groupe d'un EC</option>
              </select>
            </div>
          </div>
          <div class="form-group row" id='divDiplome' style='display: none;'>
            <label for="inputDiplome" class="col-sm-4 col-form-label">Diplôme</label>
            <div class="col-sm-8">
              <select class="form-control" id="inputDiplome" name="inputDiplome" onchange="javascript:selectionDiplome()">
              <?php /* Liste des diplômes */ ?>
              </select>
            </div>
          </div>
          <div class="form-group row" id='divSemestre' style='display: none;'>
            <label for="inputSemestre" class="col-sm-4 col-form-label">Semestre</label>
            <div class="col-sm-8">
              <select class="form-control" id="inputSemestre" name="inputSemestre" onchange="javascript:selectionSemestre()">
              <?php /* Liste des semestres */ ?>
              </select>
            </div>
          </div>
          <div class="form-group row" id='divGroupe' style='display: none;'>
            <label for="inputGroupe" class="col-sm-4 col-form-label">Groupe</label>
            <div class="col-sm-8">
              <select class="form-control" id="inputGroupe" name="inputGroupe" onchange="javascript:selectionGroupe()">
              <?php /* Liste des groupes du diplôme/semestre */ ?>
              </select>
            </div>
          </div>
          <div class="form-group row" id='divEC' style='display: none;'>
            <label for="inputEC" class="col-sm-4 col-form-label">EC</label>
            <div class="col-sm-8">
              <select class="form-control" id="inputEC" name="inputEC" onchange="javascript:selectionEC()">
              <?php /* Liste des ECs */ ?>
              </select>
            </div>
          </div>
          <div class="form-group row" id='divGroupeEC' style='display: none;'>
            <label for="inputGroupeEC" class="col-sm-4 col-form-label">Groupe</label>
            <div class="col-sm-8">
              <select class="form-control" id="inputGroupeEC" name="inputGroupeEC" onchange="javascript:selectionGroupeEC()">
              <?php /* Liste des groupes de l'EC */ ?>
              </select>
            </div>
          </div>
          <div class="form-group row" id='divIntitule' style='display: none;'>
            <label for="inputIntitule" class="col-sm-4 col-form-label">Intitulé</label>
            <div class="col-sm-8">
              <input id="inputIntitule" name="inputIntitule" type='text' class='form-control' value='' placeholder="Saisir un nom"/>
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
    $('#inputFichier').val("");
    $('#importationModal').modal("show");
}
function confImporter() {
    if(request == null) {
        if($('#inputType').val() == 1) {
            if(($('#inputDiplome').val() != -1) &&
               ($('#inputSemestre').val() != -1)) {
                if($('#inputGroupe').val() == -1)
                    $('#importForm').submit();
                else {
                    if($('#inputIntitule').val() == "")
                        $('#inputIntitule').val($('#inputGroupe option:selected').text());
                    $('#importForm').submit();
                }
            }
            else
                displayErrorModal("Vous devez sélectionner un diplôme et un semestre.");
        }
        else if($('#inputType').val() == 2) {
            if($('#inputEC').val() != -1) {
                if($('#inputGroupeEC').val() == -1)
                   $('#importForm').submit();
                else {
                    if($('#inputIntitule').val() == "")
                        $('#inputIntitule').val($('#inputGroupeEC option:selected').text());
                    $('#importForm').submit();
                }
            }
            else
                displayErrorModal("Vous devez sélectionner un EC.");
        }
        else {
            displayErrorModal("Vous devez choisir un type d'importation.");
        }
    }
}
function selectionGroupeEC() {
    $('#divIntitule').show();
    if($('#inputGroupeEC').val() != -1) {
        //$('#inputGroupeEC').find('[value="-1"]').remove();
        $('#inputIntitule').val($('#inputGroupeEC option:selected').text());
    }
    else
        $('#inputIntitule').val("");
}
function selectionEC() {
    if((request == null) && ($('#inputEC').val() != -1)) {
        $('#inputEC').find('[value="-1"]').remove();
        
        $('#divGroupeEC').show();
        $('#inputIntitule').val("");
        $('#divIntitule').hide();
        getSelectContent('inputGroupeEC', 'groupesec/ws.php', { 'mode' : 1, 'EC' : $('#inputEC').val() }, 'groupes', 'Tous les groupes', 'intitule', 'id' );
    }
}
function selectionGroupe() {
    if($('#inputGroupe').val() != -1) {
        $('#divIntitule').show();
        $('#inputIntitule').val($('#inputGroupe option:selected').text());
    }
    else {
        $('#divIntitule').hide();
        $('#inputIntitule').val("");
    }
}
function selectionSemestre() {
    if((request == null) && ($('#inputSemestre').val() != -1)) {
        $('#inputSemestre').find('[value="-1"]').remove();
        
        $('#divGroupe').show();
        $('#inputIntitule').val("");
        $('#divIntitule').hide();
        getSelectContent('inputGroupe', 'groupes/ws.php', { 'mode' : 1, 'json' : true, 'diplome' : $('#inputDiplome').val(), 'semestre' : $('#inputSemestre').val() }, 'groupes', 'Tous les groupes', '', '' );
    }
}
function selectionDiplome() {
    if((request == null) && ($('#inputDiplome').val() != -1)) {
        $('#inputDiplome').find('[value="-1"]').remove();

        $('#inputIntitule').val("");
        $('#divIntitule').hide();
        $('#divSemestre').show();
        $('#divGroupe').hide();
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'diplomes/ws.php',
                data: { 'mode' : 11, 'diplome' : $('#inputDiplome').val() },
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                $('#inputSemestre').empty();
                $('#inputSemestre').append("<option value='-1' checked>Choisissez un semestre</option>");
                nbSemestres = parseInt(response['nbSemestres']);
                for(i = 1; i <= nbSemestres; i++)
                    $("#inputSemestre").append("<option value='" + i + "'>Semestre " + i + "</option>");
                $('#inputSemestre').show();
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                request = null;
            });
    }
}
function selectionType() {
    if(request == null) {
        if($('#inputType').val() == 1) {
            $('#divEC').hide();
            $('#divGroupeEC').hide();
            
            $('#divDiplome').show();
            $('#divSemestre').hide();
            $('#divGroupe').hide();
            
            $('#divIntitule').hide();
            
            getSelectContent('inputDiplome', 'diplomes/ws.php', { 'mode' : 12 }, 'diplomes', 'Choisissez un diplôme', 'intitule', 'id' );
        }
        else {
            $('#divEC').show();
            $('#divGroupeEC').hide();
            
            $('#divDiplome').hide();
            $('#divSemestre').hide();
            $('#divGroupe').hide();        
            
            $('#divIntitule').hide();
            
            getSelectContent('inputEC', 'ecs/ws.php', { 'mode' : 1 }, 'liste', 'Choisissez un EC', 'nom', 'id' );
        }
        
        if($('#inputType').val() != -1)
            $('#inputType').find('[value="-1"]').remove();
    }
}
function getSelectContent(id, url, data, JSONkey, defautLabel, label, value) {
    if(request == null) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + url,
                data: data,
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response['code'] == 1) {
                    $('#' + id).empty();
                    if(response[JSONkey].length == 0)
                        $('#' + id).append("<option value='-1' checked>Aucun</option>");
                    else {                
                        $('#' + id).append("<option value='-1' checked>" + defautLabel + "</option>");
                        val = -1;
                        lab = "";
                        
                        $.each(response[JSONkey], function(ind, val) {
                            if(value != '')
                                v = val[value];
                            else
                                v = ind;
                            if(label != '')
                                l = val[label];
                            else
                                l = val;
                            $('#' + id).append("<option value='" + v + "'>" + l + "</option>"); 
                        });
                    }
                }
                else {
                    console.log(response);
                    displayErrorModal(response);
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

<div class="container">
  <form class="form-inline justify-content-between mt-2">
    <div class="form-group mb-2">
    </div>
    <div class="form-group mb-2">
<?php
if($respDip || UserModel::estAdmin()) {
?>
       <a data-toggle='tooltip' data-placement='top' title='Créer un nouveau groupe' id="ajouterLink" class="btn btn-outline-primary mr-2" href="ajouter.php">Créer un groupe</a>
       <button type='button' data-toggle='tooltip' data-placement='top' title='Importer un groupe existant' id="ajouterLink" class="btn btn-outline-primary mr-2" onclick="javascript:importer()">Importer</button>
<?php
}
?>
    </div>
    <div class="form-group mb-2">
      <a data-toggle='tooltip' data-placement='top' title='Retourner à la liste des ECs' href='<?php echo WEB_PATH; ?>ecs/liste.php' class='btn btn-outline-primary'>Retour</a>
    </div>
  </form>
</div>
<?php
if(count($data['groupes']) == 0) {
    echo <<<HTML
<div class="media alert-danger text-center" id="message">
  <div class="media-body">
    <p class="lead mb-0" id="contenuMessage">Il n'y a aucun groupe pour le moment.</p>
  </div>
</div>
HTML;
}
else {
    echo <<<HTML
<table class="table table-striped">
  <thead>
    <tr>
      <th scope="col">Intitulé</th>
      <th scope="col">Type</th>
      <th class="text-right" scope="col">Actions</th>
    </tr>
  </thead>
HTML;
    $lienModif = WEB_PATH."groupesec/modifier.php";
    $lienSupp = WEB_PATH."groupesec/supprimer.php";
    $lienGrp = WEB_PATH."groupesec/etudiants.php";
    foreach($data['groupes'] as $groupe) {
        $type = Groupe::type2String($groupe['type']);
        echo <<<HTML
        <tr id='ligne{$groupe['id']}'>
          <th scope='row'>{$groupe['intitule']}</th>
          <td>{$type}</td>
          <td class="text-right">
HTML;
        if(UserModel::estRespEC($data['EC']->getId()) || $respDip) {
            echo <<<HTML
            <button name='idModi' type='submit' class='btn btn-sm btn-outline-primary mr-2' data-toggle='tooltip' 
                    data-placement='top' title="Modifier le groupe" form='controlForm' formaction='$lienModif' 
                    value='{$groupe['id']}'>
              <i class='icon-wrench'></i>
            </button>
HTML;
        }
        echo <<<HTML
            <button name='groupeEC' type='submit' class='btn btn-sm btn-outline-primary mr-2' data-toggle='tooltip' 
                    data-placement='top' title="Liste des étudiants" form='controlForm' formaction='$lienGrp' 
                    value='{$groupe['id']}'>
              <i class='icon-people'></i>
            </button>
HTML;
        if(UserModel::estAdmin() || $respDip) {
            echo <<<HTML
            <button name='idSupp' type='submit' class='btn btn-sm btn-outline-danger mr-2' data-toggle='tooltip' 
                    data-placement='top' title="Supprimer le groupe" form='controlForm' formaction='$lienSupp' 
                    value='{$groupe['id']}'>
              <i class='icon-trash'></i>
            </button>
HTML;
        }
        echo <<<HTML
          </td>
        </tr>
HTML;
    }
    echo "</table>";
}