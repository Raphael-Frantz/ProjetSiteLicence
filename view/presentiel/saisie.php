<?php
// @need 

WebPage::addJSScript("public/js/active-tooltips.js");
WebPage::addJSScript("public/js/tools.js");
WebPage::addOnlineScript("var WEB_PATH = '".WEB_PATH."';");

// Pour les dates
WebPage::addCSSScript("public/css/tempusdominus-bootstrap-4.css");
WebPage::addCSSScript("vendor/fontawesome/css/all.min.css");
WebPage::addJSScript("public/js/moment.js");
WebPage::addJSScript("public/js/tempusdominus-bootstrap-4.min.js");
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Saisie du présentiel de <?php echo $data['EC']->getCode(); ?></h2>
    <p class="lead mb-0">
      Depuis cette page, vous pouvez créer de nouvelles séances et saisir le présentiel de chaque séance.
    </p>
  </div>
</section>

<div class="modal fade" id="creationModal" tabindex="-1" role="dialog" aria-labelledby="creationModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="creationModalLabel">Création de séances</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="creationModalBody">
        <form id="creationForm" method="post">
          <div class="form-group row">
            <label for="inputDateDebut" class="col-sm-4 col-form-label">Début de la séance</label>
            <div class="col-sm-8">
              <div class="input-group date" id="inputDateDebut" data-target-input="nearest">
                <input type="text" name="inputDateDebut" class="form-control datetimepicker-input" data-target="#inputDateDebut"/>
                <div class="input-group-append" data-target="#inputDateDebut" data-toggle="datetimepicker">
                  <div class="input-group-text"><i class="icon-calendar"></i></div>
                </div>
              </div>
              <small id="inputDateDebutHelp" class="form-text text-muted">Le format de la date est JJ/MM/YYYY HH:MM</small>
              <?php WebPage::addOnlineScript("
              $('#inputDateDebut').datetimepicker({locale: 'fr'});
              $('#inputDateDebut').on('change.datetimepicker', function (e) {
                  
                var start = moment(e.date);
                var end = start.add(moment.duration(2, 'hours'));                
                
                $('#inputDateFin').datetimepicker('date', end);
              });              
              "); ?>
            </div>
          </div>
          <div class="form-group row">
            <label for="inputDateFin" class="col-sm-4 col-form-label">Fin de la séance</label>
            <div class="col-sm-8">
              <div class="input-group date" id="inputDateFin" data-target-input="nearest">
                <input type="text" name="inputDateFin" class="form-control datetimepicker-input" data-target="#inputDateFin"/>
                <div class="input-group-append" data-target="#inputDateFin" data-toggle="datetimepicker">
                  <div class="input-group-text"><i class="icon-calendar"></i></div>
                </div>
              </div>
              <small id="inputDateFinHelp" class="form-text text-muted">Le format de la date est JJ/MM/YYYY HH:MM</small>
              <?php WebPage::addOnlineScript("
              $('#inputDateFin').datetimepicker({locale: 'fr'});
              $('#inputDateFin').on('change.datetimepicker', function (e) {
                
              });
              "); ?>
            </div>
          </div>          
          <div class="form-group row">
            <label for="inputOcc" class="col-sm-4 col-form-label">Occurrences</label>
            <div class="col-sm-8">
              <input id="inputOcc" name="inputOcc" type='number' min='1' class='form-control' value='' placeholder="Saisir un nombre d'occurrences"/>
              <small id="inputOccHelp" class="form-text text-muted">Si vous saisissez un nombre supérieur à 1, plusieurs séances seront créées</small>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" onclick="confCreation()" data-dismiss="modal">Créer</button>
        <button type="button" class="btn btn-primary" data-dismiss="modal">Annuler</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="suppressionModal" tabindex="-1" role="dialog" aria-labelledby="suppressionModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="suppressionModalLabel">Suppresion de la séance</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="suppressionModalBody">
        Vous êtes sur le point de supprimer la séance du <span class='badge badge-secondary' id='suppSeance'></span>.
        Si vous confirmez la suppression, le présentiel associé sera perdu.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" onclick="confSuppression()" data-dismiss="modal">Confirmer</button>
        <button type="button" class="btn btn-primary" data-dismiss="modal">Annuler</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modificationModal" tabindex="-1" role="dialog" aria-labelledby="modificationModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modificationModalLabel">Modification de la séance</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="modificationModalBody">
        <form id="modificationForm" method="post">
          <div class="form-group row">
            <label for="inputDateDebutModif" class="col-sm-4 col-form-label">Début</label>
            <div class="col-sm-8">
              <div class="input-group date" id="inputDateDebutModif" data-target-input="nearest">
                <input type="text" name="inputDateDebutModif" class="form-control datetimepicker-input" data-target="#inputDateDebutModif"/>
                <div class="input-group-append" data-target="#inputDateDebutModif" data-toggle="datetimepicker">
                  <div class="input-group-text"><i class="icon-calendar"></i></div>
                </div>
              </div>
              <small id="inputDateDebutModifHelp" class="form-text text-muted">Le format de la date est JJ/MM/YYYY HH:MM</small>
              <?php WebPage::addOnlineScript("$('#inputDateDebutModif').datetimepicker({locale: 'fr'})"); ?>
            </div>
          </div>
          <div class="form-group row">
            <label for="inputDateFinModif" class="col-sm-4 col-form-label">Fin</label>
            <div class="col-sm-8">
              <div class="input-group date" id="inputDateFinModif" data-target-input="nearest">
                <input type="text" name="inputDateFinModif" class="form-control datetimepicker-input" data-target="#inputDateFinModif"/>
                <div class="input-group-append" data-target="#inputDateFinModif" data-toggle="datetimepicker">
                  <div class="input-group-text"><i class="icon-calendar"></i></div>
                </div>
              </div>
              <small id="inputDateFinModifHelp" class="form-text text-muted">Le format de la date est JJ/MM/YYYY HH:MM</small>
              <?php WebPage::addOnlineScript("$('#inputDateFinModif').datetimepicker({locale: 'fr'})"); ?>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" onclick="confModification()" data-dismiss="modal">Modifier</button>
        <button type="button" class="btn btn-primary" data-dismiss="modal">Annuler</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="importationModal" tabindex="-1" role="dialog" aria-labelledby="importationModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="importationModalLabel">Importation</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="importForm" method="post" action="<?php echo WEB_PATH; ?>presentiel/importerpresentiel.php" enctype="multipart/form-data">
          <div class="form-group row">
            <label for="inputFichier" class="col-sm-2 col-form-label">Fichier</label>
            <div class="col-sm-10">
              <div class="form-group">
                <input type="file" class="form-control-file" name="inputFichier" id="inputFichier" lang="fr">
              </div>
              <small class="form-text text-muted">Sélectionnez un fichier CSV avec le bon format</small>
            </div>
          </div>
          <div class="form-group row">
            <label for="inputCreer" class="col-sm-2 col-form-label">Création</label>
            <div class="col-sm-10">
              <div class="form-check">
                <input type="checkbox" class="form-check-input" name="inputCreer" id="inputCreer">
                <label class="form-check-label" for="inputCreer">Créer les séances inexistantes</label>
              </div>
              <small class="form-text text-muted">Si vous cochez cette option, les séances spécifiées dans le fichier qui sont inconnues, seront créées automatiquement</small>
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

<?php
if(count($data['groupes']) == 0) {
?>
<div class="container">
  <form class="form-inline justify-content-between mt-2">
    <div></div>
    <div class="form-group mb-2">
      <a data-toggle='tooltip' data-placement='top' title='Retour à la liste des ECs' id="idRetour" class="btn btn-outline-primary mr-2" href="<?php echo WEB_PATH; ?>ecs/liste.php">Retour</a>
    </div>
  </form>
</div>

<div class="media alert-danger text-center" id="message">
  <div class="media-body">
    <p class="lead mb-0" id="contenuMessage">Il n'y a aucun groupe dans cet EC.</p>
  </div>
</div>
<?php
}
else {
?>
<script>
var request = null;
function updateSeances(seances) {
    $('#seance').empty();
    if(seances.length == 0) {
        $("#seance").append("<option value='-2'>Pas de séance</option>");
        $("#btnSupprimer").hide();
        $('#btnModifier').hide();
        $('#btnRattrapage').hide();
    }
    else {
        $("#seance").append("<option value='-1'>Résumé</option>");
        for(i = 0; i < seances.length; i++) {
            $("#seance").append("<option value='" + seances[i]['id'] + 
                                "'>(" + (i+1) + ") " + seances[i]['debut'] +
                                "</option>");
        }
    }
}
function selectionGroupe() {
    if(request == null) {
        if($('#groupe').val() == -1) {
            displayWaitModal();
            request = $.ajax({
                    type: 'POST',
                    url: WEB_PATH + 'presentiel/ws.php',
                    data: { 
                            'mode' : 12
                        },
                    dataType: 'html'
                });
            request.done(function (response, textStatus, jqXHR) {
                    if(response == "")
                        document.location.href = WEB_PATH;
                    
                    updateContent('contenu', response);
                    $("#seance").hide();
                    $('#btnSupprimer').hide();
                    $('#btnModifier').hide();
                    $('#btnListe').hide();
                    $('#btnAjouter').hide();
                    $('#btnImporter').hide();
                    $('#btnRattrapage').hide();
                    $('#btnExporter').show();
                    hideWaitModal();
                    request = null;
                });
            request.fail(function (jqXHR, textStatus, errorThrown){
                    console.log(jqXHR);
                    hideWaitModal();
                    displayErrorModal("Une erreur technique est survenue. Veuillez prévenir l'administrateur.");
                    request = null;
                });            
        }
        else {
            request = $.ajax({
                    type: 'POST',
                    url: WEB_PATH + 'presentiel/ws.php',
                    data: { 
                            'mode' : 4, 
                            'groupe' : $('#groupe').val()
                        },
                    dataType: 'JSON'
                });
            request.done(function (response, textStatus, jqXHR) {
                    if(response['code'] == -2) {
                        document.location.href = WEB_PATH;
                    }
                    else {
                        $("#seance").show();
                        if(response['code'] == 1) {
                            $('#contenu').empty();
                            updateSeances(response['seances']);
                        }
                        else {
                            displayErrorModal(response['erreur']);
                        }
                        request = null;
                        if(response['seances'].length != 0) {
                            selectionSeance();
                        }
                        else {
                            if(response['droits'] == false)
                                displayErrorMsg("contenu", "Vous n'avez qu'un accès en consultation.");
                            
                            displayErrorMsg("contenu", "Il n'y a aucune séance pour ce groupe.");
                            
                            $('#btnListe').show();
                            $('#btnRattrapage').hide();
                            if(response['droits'] == true) {
                                $('#btnAjouter').show();
                                $('#btnImporter').show();
                                $('#btnExporter').show();
                            }
                            else {
                                $('#btnAjouter').hide();
                                $('#btnImporter').hide();
                                $('#btnExporter').hide();
                            }
                        }
                    }
                });
            request.fail(function (jqXHR, textStatus, errorThrown){
                    console.log(jqXHR);
                    displayErrorModal("Une erreur technique est survenue. Veuillez prévenir l'administrateur.");
                    request = null;
                });
        }
    }        
}
function selectionSeance() {
    if(request == null) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'presentiel/ws.php',
                data: { 
                        'mode' : 5, 
                        'groupe' : $('#groupe').val(),
                        'seance' : $('#seance').val(),
                    },
                dataType: 'html'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response == "")
                    document.location.href = WEB_PATH;
                
                updateContent('contenu', response);
                if($('#seance').val() == -1) {
                    $('#btnSupprimer').hide();
                    $('#btnListe').show();
                    $('#btnModifier').hide();
                    $('#btnRattrapage').hide();
                    
                    if($("div[id='droits']").length == 0) {
                        $('#btnAjouter').show();
                        $('#btnImporter').show();
                        $('#btnExporter').show();
                    }
                    else {
                        $('#btnAjouter').hide();
                        $('#btnImporter').hide();
                        $('#btnExporter').hide();
                    }
                }
                else {
                    if($("div[id='droits']").length == 0) {
                        $('#btnSupprimer').show();
                        $('#btnModifier').show();
                        $('#btnAjouter').show();
                        $('#btnImporter').show();
                        $('#btnExporter').show();
                        $('#btnRattrapage').show();
                        $('#btnListe').show();
                    }
                    else {
                        $('#btnSupprimer').hide();
                        $('#btnModifier').hide();
                        $('#btnAjouter').hide();
                        $('#btnImporter').hide();
                        $('#btnExporter').hide();
                        $('#btnRattrapage').hide();
                        $('#btnListe').hide();
                    }
                }
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                displayErrorModal("Une erreur technique est survenue. Veuillez prévenir l'administrateur.");
                request = null;
            });
    }    
}
function creation() {
    $('#creationModal').modal("show");
}
function confCreation() {
    if(request == null) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'presentiel/ws.php',
                data: { 
                        'mode' : 10,
                        'inputDateDebut' : $("input[name='inputDateDebut']").val(),
                        'inputDateFin' : $("input[name='inputDateFin']").val(),
                        'inputOcc' : $("input[name='inputOcc']").val()
                    },
                dataType: 'JSON'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response['code'] == -2) {
                    document.location.href = WEB_PATH;
                }
                else {
                    if(response['code'] == 1) {
                        updateSeances(response['seances']);
                        if(response['seances'].length != 0)
                            $('#seance').val(response['seance']);
                        $('#inputDateDebut').val('');
                        $('#inputDateFin').val('');
                        $('#inputOcc').val('');
                        request = null;
                        if(response['seances'].length != 0) selectionSeance();
                    }
                    else {
                        request = null;
                        displayErrorModal(response['erreur']);
                    }
                }
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                displayErrorModal("Une erreur technique est survenue. Veuillez prévenir l'administrateur.");
                request = null;
            });
    }
}
function suppression() {
    if(request == null) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'presentiel/ws.php',
                data: { 
                        'mode' : 7
                    },
                dataType: 'JSON'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response['code'] == -2) {
                    document.location.href = WEB_PATH;
                }
                else if(response['code'] == 1) {
                    $("#suppSeance").text(response['debut'] + "-" + response['fin']);
                    $('#suppressionModal').modal("show");
                }
                else {
                    displayErrorModal(response['erreur']);
                }
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                displayErrorModal("Une erreur technique est survenue. Veuillez prévenir l'administrateur.");
                request = null;
            });
    }
}
function confSuppression() {
    if(request == null) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'presentiel/ws.php',
                data: { 
                        'mode' : 9
                    },
                dataType: 'JSON'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response['code'] == -2) {
                    document.location.href = WEB_PATH;
                }
                else if(response['code'] == 1) {
                    $('#contenu').empty();
                    updateSeances(response['seances']);
                    request = null;
                    if(response['seances'].length != 0) {
                        $('#seance').val(response['seance']);
                        selectionSeance();
                    }
                    else {
                        $('#contenu').append("<div class='media alert-danger text-center' id='message'>" +
                                             "<div class='media-body'>" +
                                             "<p class='lead mb-0' id='contenuMessage'>Il n'y a aucune séance pour ce groupe.</p>" +
                                             "</div></div>");
                    }
                }
                else {
                    request = null;
                    displayErrorModal(response['erreur']);
                }
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                displayErrorModal("Une erreur technique est survenue. Veuillez prévenir l'administrateur.");
                request = null;
            });
    }
}
function modification() {
    if(request == null) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'presentiel/ws.php',
                data: { 
                        'mode' : 7
                    },
                dataType: 'JSON'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response['code'] == -2) {
                    document.location.href = WEB_PATH;
                }
                else if(response['code'] == 1) {
                    $("input[name='inputDateDebutModif']").val(response['debut']);
                    $("input[name='inputDateFinModif']").val(response['fin']);
                    $('#modificationModal').modal("show");
                }
                else {
                    displayErrorModal(response['erreur']);
                }
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                displayErrorModal("Une erreur technique est survenue. Veuillez prévenir l'administrateur.");
                request = null;
            });
    }
}
function confModification() {
    if(request == null) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'presentiel/ws.php',
                data: { 
                        'mode' : 8,
                        'inputDateDebut' : $("input[name='inputDateDebutModif']").val(),
                        'inputDateFin' : $("input[name='inputDateFinModif']").val()
                    },
                dataType: 'JSON'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response['code'] == -2) {
                    document.location.href = WEB_PATH;
                }
                else if(response['code'] == 1) {
                    updateSeances(response['seances']);
                    $('#seance').val(response['seance']);
                    request = null;
                    if(response['seances'].length != 0) selectionSeance();
                }
                else {
                    displayErrorModal(response['erreur']);
                    request = null;
                }
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                displayErrorModal("Une erreur technique est survenue. Veuillez prévenir l'administrateur.");
                request = null;
            });
    }
}
function change(etudiant, type, supp) {
    if(request == null) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'presentiel/ws.php',
                data: { 
                        'mode' : 6,
                        'etudiant' : etudiant,
                        'type' : type
                    },
                dataType: 'JSON'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response['code'] == -2) {
                    document.location.href = WEB_PATH;
                }
                else if(response['code'] == 1) {
                    if(supp) {
                        deleteElement('lg_' + etudiant);
                    }
                    else {
                        var selector;
                        if(response['etudiant'] == -1)
                            selector = $("span[id^='btn_']");
                        else
                            selector = $('#btn_' + etudiant);
                        
                        switch(response['type']) {
                            case 0:
                                selector.removeClass().addClass("badge badge-secondary mr-1 mb-1 p-2");
                                selector.text('N');
                                break;
                            case 1:
                                selector.removeClass().addClass("badge badge-success mr-1 mb-1 p-2");
                                selector.text('P');
                                break;
                            case 2:
                                selector.removeClass().addClass("badge badge-danger mr-1 mb-1 p-2");
                                selector.text('A');
                                break;
                        }
                    }
                    $('#nbNS').text($("span[id^='btn_'].badge-secondary").length);
                    $('#nbPre').text($("span[id^='btn_'].badge-success").length);
                    $('#nbAbs').text($("span[id^='btn_'].badge-danger").length);
                }
                else {
                    displayErrorModal("Une erreur technique est survenue (" + response['erreur'] + "). Veuillez prévenir l'administrateur.");
                }
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                displayErrorModal("Une erreur technique est survenue. Veuillez prévenir l'administrateur.");
                request = null;
            });
    }
}
function importer() {
    $('input[name="inputFichier"]').val("");
    $('input[name="inputCreer"]').prop("checked", false);
    $('#importationModal').modal("show");
}
function confImporter() {
    $('#importForm').submit();
}
function choixEtudiant(item) {
    $("#inputId").val(item.id);
    $("#inputName").val(item);
    return item.name;
}
function exporter() {
    if($('#groupe').val() != -1) {
        document.location.href = WEB_PATH + "presentiel/exporterpresentiel.php";
    }
    else
        document.location.href = WEB_PATH + "presentiel/exporterbilan.php";
}
</script>

<div class="container">
  <form class="form-inline justify-content-between mt-2">
    <div id='divSelection' class="form-group mb-2">
      <select class="form-control mr-2" id="groupe" name="groupe" onchange="javascript:selectionGroupe()">
        <option value='-1'>Bilan</option>
<?php
    for($i = 0; $i < count($data['groupes']); $i++) {
        $type = Groupe::type2String($data['groupes'][$i]['type']);
        echo "<option value='{$data['groupes'][$i]['id']}'";
        if(isset($data['groupe']) && ($data['groupes'][$i]['id'] == $data['groupe']))
            echo " selected=\"selected\"";
        echo ">{$data['groupes'][$i]['intitule']} ($type)</option>";
    }
?>
      </select>
      <select class="form-control mr-2" id="seance" name="seance" onchange="javascript:selectionSeance()">
<?php
    if(count($data['seances']) == 0) {
        echo "<option value='-2'>Pas de séance</option>";
    }
    else {
        echo "<option value='-1'>Résumé</option>";
        for($i = 0; $i < count($data['seances']); $i++) {
            echo "<option value='{$data['seances'][$i]['id']}'";
            if(isset($data['seance']) && ($data['seances'][$i]['id'] == $data['seance']))
                echo " selected=\"selected\"";
            echo ">(".($i + 1).") {$data['seances'][$i]['debut']}</option>";
        }
    }
?>
      </select>
<?php
    if(!$data['droits']) 
        $disp = "style='display: none;'";
    else
        $disp = "";
?>
      <a id='btnSupprimer' style='display: none;' data-toggle='tooltip' data-placement='top' title='Supprimer la séance courante' class="btn btn-outline-danger mr-2" href="javascript:suppression()"><i class="icon-trash"></i></a>
      <a id='btnModifier' style='display: none;' data-toggle='tooltip' data-placement='top' title='Modifier la séance courante' class="btn btn-outline-success mr-2" href="javascript:modification()"><i class="icon-wrench"></i></a>
      <a id='btnAjouter' style='display: none;' data-toggle='tooltip' data-placement='top' title='Ajouter une nouvelle séance' class="btn btn-outline-primary mr-2" href="javascript:creation()"><i class="icon-plus"></i></a>
      <a id='btnExporter' style='display: none;' data-toggle='tooltip' data-placement='top' title='Exporter vers un fichier CSV' download class="btn btn-outline-primary mr-2" href="javascript:exporter()"><i class='fas fa-file-export'></i></a>
      <a id='btnImporter' style='display: none;' data-toggle='tooltip' data-placement='top' title='Importer depuis un fichier CSV' class="btn btn-outline-primary mr-2" href="javascript:importer()"><i class='fas fa-file-import'></i></a>
      <a id='btnRattrapage' style='display: none;' data-toggle='tooltip' data-placement='top' title="Spécifier un rattrapage" class="btn btn-outline-primary mr-2" href='rattrapage.php'><i class='icon-user-follow'></i></a>
      <a id='btnListe' data-toggle='tooltip' download data-placement='top' title='Imprimer la fiche de présence' class="btn btn-outline-primary mr-2" href="<?php echo WEB_PATH; ?>groupesec/fiche.php" target="_blank"><i class='icon-printer'></i></a>
    </div>
    <div class="form-group mb-2">
      <a data-toggle='tooltip' data-placement='top' title='Retour à la liste des ECs' id="idRetour" class="btn btn-outline-primary mr-2" href="<?php echo WEB_PATH; ?>ecs/liste.php">Retour</a>
    </div>
  </form>
</div>

<div id="contenu"></div>

<?php
    if(count($data['seances']) != 0)
        WebPage::addOnReady("selectionSeance();");
    else
        WebPage::addOnReady("selectionGroupe();");
}