<?php
// @need diplome le diplôme
// @need structure la structure d'un diplôme (UE/EC)
?>

<?php
WebPage::addOnlineScript("var WEB_PATH = '".WEB_PATH."';");
?>

<div class="modal fade" id="confirmation" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmationModalLabel">Confirmation</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="confirmationTexte"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" onclick="supprimer()" data-dismiss="modal">Supprimer</button>
        <button type="button" class="btn btn-primary" data-dismiss="modal">Annuler</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="message" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="messageLabel"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="messageTexte"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="choixEC" tabindex="-1" role="dialog" aria-labelledby="choixECModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ECModalLabel">Choix de l'EC</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <div class="form-group row">
            <label for="inputIntitule" class="col-sm-2 col-form-label">EC</label>
            <div class="col-sm-10">
              <select class="form-control" name="choixEC" id="inputEC">
              </select>
            </div>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" onclick="ajouter()" data-dismiss="modal">Ajouter</button>
        <button type="button" class="btn btn-primary" data-dismiss="modal">Annuler</button>
      </div>
    </div>
  </div>
</div>

<script>
var request = null;
var currentUE = -1;
var currentEC = -1;
var currentSem = 1;

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
function selectionSemestre(sem) {

    if(request == null) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'diplomes/ws.php',
                data: { 'mode' : 0, 'semestre' : sem },
                dataType: 'html'
            });
        request.done(function (response, textStatus, jqXHR) {
                updateContent('structure', response);
                currentSem = sem;
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                displayErrorModal("Une erreur technique est survenue. Veuillez prévenir l'administrateur.");
                request = null;
            });
    }
}

function parcours() {
    if(request == null) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'diplomes/ws.php',
                data: { 'mode' : 0},
                dataType: 'html'
            });
        request.done(function (response, textStatus, jqXHR) {
                updateContent('structure', response);
                currentSem = sem;
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                displayErrorModal("Une erreur technique est survenue. Veuillez prévenir l'administrateur.");
                request = null;
            });
    }

    request = null;
}

function ajouterUE(sem) {
    if(request == null) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'diplomes/ws.php',
                data: { 'mode' : 1, 'semestre' : sem },
                dataType: 'html'
            });
        request.done(function (response, textStatus, jqXHR) {
                updateContent('structure', response);
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                displayErrorModal("Une erreur technique est survenue. Veuillez prévenir l'administrateur.");
                request = null;
            });
    }
}
function supprimerEC(ue, ec) {
    currentUE = ue;
    currentEC = ec;
    mode = 2;
    $('#confirmationTexte').empty();
    $('#confirmationTexte').append("Êtes-vous sûr de supprimer l'EC " + $('#EC_' + ec).text() + " ?");
    $('#confirmation').modal("show");
}
function supprimerUE(num, id) {
    currentUE = id;
    mode = 1;
    $('#confirmationTexte').empty();
    $('#confirmationTexte').append("Êtes-vous sûr de supprimer l'UE " + num + " ?");
    $('#confirmation').modal("show");
}
function supprimerSemestre() {
    if(nbSemestres != 1) {
        mode = 3;
        $('#confirmationTexte').empty();
        $('#confirmationTexte').append("Êtes-vous sûr de supprimer le semestre " + currentSem + " avec toutes les UE/EC ?");
        $('#confirmation').modal("show");
    }
    else {
        $('#messageTexte').empty().append("Il est impossible de supprimer tous les semestres d'un diplôme.");
        $('#messageLabel').empty().append("Suppression impossible");
        $('#message').modal("show");
    }
}
function supprimer() {
    if(request == null) {
        switch(mode) {
            case 1:
                content = { 'mode' : 2, 'semestre' : currentSem, 'UE' : currentUE };
                break;
            case 2:
                content = { 'mode' : 8, 'semestre' : currentSem, 'UE' : currentUE, 'EC' : currentEC };
                break;
            case 3:
                content = { 'mode' : 10, 'semestre' : currentSem };
        }
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'diplomes/ws.php',
                data: content,
                dataType: 'html'
            });
        if(mode == 3) {
            request.done(function (response, textStatus, jqXHR) {
                    $('[data-toggle="tooltip"]').tooltip('dispose');
                    $('#structure').empty().html(content);
                    request = null;

                    $("#S" + nbSemestres + "Label").remove();
                    nbSemestres--;
                    if(currentSem > nbSemestres)
                        currentSem = nbSemestres;
                    $("#S" + nbSemestres).prop("checked", true).trigger("click");
                    $('[data-toggle="tooltip"]').tooltip();

                    $("#S" + nbSemestres).change();
                });
        }
        else {
            request.done(function (response, textStatus, jqXHR) {
                    updateContent('structure', response);
                    request = null;
                });
        }
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                displayErrorModal("Une erreur technique est survenue. Veuillez prévenir l'administrateur.");
                request = null;
            });
    }
}
function monterUE(num) {
    if(request == null) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'diplomes/ws.php',
                data: { 'mode' : 3, 'semestre' : currentSem, 'UE' : num },
                dataType: 'html'
            });
        request.done(function (response, textStatus, jqXHR) {
                updateContent('structure', response);
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                displayErrorModal("Une erreur technique est survenue. Veuillez prévenir l'administrateur.");
                request = null;
            });
    }
}
function descendreUE(num) {
    if(request == null) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'diplomes/ws.php',
                data: { 'mode' : 4, 'semestre' : currentSem, 'UE' : num },
                dataType: 'html'
            });
        request.done(function (response, textStatus, jqXHR) {
                updateContent('structure', response);
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                displayErrorModal("Une erreur technique est survenue. Veuillez prévenir l'administrateur.");
                request = null;
            });
    }
}
function ajouterEC(num) {
    if(request == null) {
        currentUE = num;
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'ecs/ws.php',
                data: { 'mode' : 1 },
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                $('#inputEC').empty();
                for(i = 0; i < response['liste'].length; i++)
                    $('#inputEC').append(new Option(response['liste'][i]['nom'],
                                                    response['liste'][i]['id']));
                $('#choixEC').modal("show");
                //$('[data-toggle="tooltip"]').tooltip();
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                displayErrorModal("Une erreur technique est survenue. Veuillez prévenir l'administrateur.");
                request = null;
            });
    }
}
function ajouter() {
    if(request == null) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'diplomes/ws.php',
                data: { 'mode' : 5, 'semestre' : currentSem, 'UE' : currentUE, 'EC' : $('#inputEC').val() },
                dataType: 'html'
            });
        request.done(function (response, textStatus, jqXHR) {
                updateContent('structure', response);
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                displayErrorModal("Une erreur technique est survenue. Veuillez prévenir l'administrateur.");
                request = null;
            });
    }
}
function monterEC(ue, ec) {
    if(request == null) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'diplomes/ws.php',
                data: { 'mode' : 6, 'semestre' : currentSem, 'UE' : ue, 'EC' : ec },
                dataType: 'html'
            });
        request.done(function (response, textStatus, jqXHR) {
                updateContent('structure', response);
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                displayErrorModal("Une erreur technique est survenue. Veuillez prévenir l'administrateur.");
                request = null;
            });
    }
}
function descendreEC(ue, ec) {
    if(request == null) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'diplomes/ws.php',
                data: { 'mode' : 7, 'semestre' : currentSem, 'UE' : ue, 'EC' : ec },
                dataType: 'html'
            });
        request.done(function (response, textStatus, jqXHR) {
                updateContent('structure', response);
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                displayErrorModal("Une erreur technique est survenue. Veuillez prévenir l'administrateur.");
                request = null;
            });
    }
}
function ajouterSemestre() {
    if(request == null) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'diplomes/ws.php',
                data: { 'mode' : 9 },
                dataType: 'html'
            });
        request.done(function (response, textStatus, jqXHR) {
                nbSemestres++;
                $("#semestreButtons").append("<label class='btn btn-secondary active' id='S" + nbSemestres + "Label'>" +
                                             "<input type='radio' name='semestre' id='S" + nbSemestres +
                                             "' autocomplete='off' onchange='selectionSemestre(" + nbSemestres + ")'> S" +
                                             (nbSemestres + minSemestre - 1) + "</label>");
                request = null;
                $("#S" + currentSem).prop("checked", false);
                $("#S" + currentSem + "Label").removeClass('active');
                currentSem = nbSemestres;
                $("#S" + nbSemestres).prop("checked", true).trigger("click");
                $("#S" + nbSemestres).change();
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                request = null;
            });
    }
}
</script>

<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Structure du diplôme <?php echo $data['diplome']; ?></h2>
    <p class="lead mb-0">Modifier les UE/EC du diplôme.</p>
  </div>
</section>

<?php
WebPage::addJSScript("public/js/active-tooltips.js");
WebPage::addOnlineScript("var nbSemestres = ".$data['diplome']->getNbSemestres().";");
WebPage::addOnlineScript("var minSemestre = ".$data['diplome']->getMinSemestre().";");
?>

<div class="container mt-2">
    <div class="justify-content-between btn-toolbar mb-3">
      <div class="btn-group btn-group-toggle mr-2" data-toggle="buttons" id="semestreButtons">

<?php
$checked = "";
$active = "";
echo <<<HTML
<label class="btn btn-secondary $active" id='Parcours'>
  <input type="radio" name="parcours" id="Parcours" autocomplete="off" {$checked} onchange="parcours()"> Parcours
</label>
HTML;

for($i = 1; $i <= $data['diplome']->getNbSemestres(); $i++) {
    if($i == 1) {
        $checked = "checked";
        $active = "active";
    }
    else {
        $checked = "";
        $active = "";
    }

    $num = ($i - 1) + $data['diplome']->getMinSemestre();
    echo <<<HTML
        <label class="btn btn-secondary $active" id='S{$i}Label'>
          <input type="radio" name="semestre" id="S{$i}" autocomplete="off" {$checked} onchange="selectionSemestre({$i})"> S{$num}
        </label>
HTML;
}
?>
      </div>
      <div class="input-group">
        <button onclick='javascript:ajouterSemestre()' class='btn btn-outline-primary mr-2' data-toggle='tooltip' data-placement='top' title='Ajouter un semestre'><i class='icon-plus'></i></button>
        <button onclick='javascript:supprimerSemestre()' class='btn btn-outline-danger mr-2' data-toggle='tooltip' data-placement='top' title='Supprimer ce semestre'><i class='icon-trash'></i></button>
        <a data-toggle='tooltip' data-placement='top' title='Retourner à la liste des diplômes' href='<?php echo WEB_PATH; ?>diplomes/index.php' class='btn btn-outline-primary'>Retour</a>
      </div>
    </div>
</div>

<section>
    <div id="structure">
      <?php include("content.php"); ?>
    </div>
</section>
