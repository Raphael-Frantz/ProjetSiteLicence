<?php
// @need 

WebPage::addOnlineScript("var WEB_PATH = '".WEB_PATH."';");
WebPage::addJSScript("public/js/tools.js");
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Liste des étudiants de chaque groupe</h2>
    <p class="lead mb-0">
      Vous pouvez consulter ici la liste des étudiants de chaque groupe.
      Vous pouvez également inscrire/désincrire des étudiants.
    </p>
  </div>
</section>

<form id="controlForm" action="" method="post"></form>

<script>
var request = null;
var first = true;
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
    if((request == null) && ($('#diplome').val() != -1) && ($('#semestre').val() != -1)) {
        if(!first)
            $('#globalMsg').remove();
        first = false;
        
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'groupes/ws.php',
                data: { 'mode' : 1, 'diplome' : $('#diplome').val(), 'semestre' : $('#semestre').val(), 'json' : true },
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                $("#groupe").empty();
                if(response['groupes'].length == 0) {
                    $("#groupe").append("<option value='-1'>Pas de groupe</option>");
                }
                else {
                    $("#groupe").append("<option value='-1'>Sélectionnez un groupe</option>");
                    
                    for(i in response['groupes']) {
                            $("#groupe").append("<option value='" + i + 
                                                "'>" + response['groupes'][i] + 
                                                "</option>");
                    }
                }
                updateContent('contenu', '');
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                request = null;
            });
    }
}
function selectionGroupe() {
    if((request == null) && ($('#diplome').val() != -1) && ($('#semestre').val() != -1) && 
       ($('#groupe').val() != -1)) {
        if(!first)
            $('#globalMsg').remove();
        first = false;
        $('#groupe').find('[value="-1"]').remove();

        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'groupes/ws.php',
                data: { 
                        'mode' : 4, 
                        'diplome' : $('#diplome').val(), 
                        'semestre' : $('#semestre').val(),
                        'groupe' : $('#groupe').val()
                    },
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
function desinscription(etudiant) {
    if((request == null) && ($('#groupe').val() != -1)) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'groupes/ws.php',
                data: { 
                        'mode' : 6, 
                        'diplome' : $('#diplome').val(), 
                        'semestre' : $('#semestre').val(),
                        'groupe' : $('#groupe').val(),
                        'etudiant' : etudiant
                    },
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response['code'] == 1)
                    deleteElement('ligne' + response['etudiant']);
                else {
                    displayErrorModal(response['msg']);
                }
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                displayErrorModal("Une erreur est survenue.");
                request = null;
            });
    }
}
</script>

<div class="container">
<form class="form-inline justify-content-between mt-2">
  <div class="form-group mb-2">
    <select class="form-control mr-2" id="diplome" name="diplome" onchange="javascript:selectionDiplome()">
<?php
if(!isset($data['diplome']) || ($data['diplome'] == null))
    echo "<option id='defautDiplome' value='-1'>Sélectionnez un diplôme</option>";

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
    <select class="form-control mr-2" id="groupe" name="groupe" onchange="javascript:selectionGroupe()">
<?php
if(!isset($data['groupes']) || (count($data['groupes']) == 0)) {
    echo "<option value='-1'>Aucun groupe</option>";
}
else {
    echo "<option value='-1'>Sélectionnez un groupe</option>";
    foreach($data['groupes'] as $groupe) {
        echo "<option value=\"{$groupe['id']}\"";
        if(isset($data['groupe']) && ($data['groupe'] == $groupe['id']))
            echo " selected=\"selected\"";
        echo ">{$groupe['intitule']}</option>";
    }
}
?>
    </select>
    <a id="listeLink" data-toggle='tooltip' data-placement='top' title='Inscrire des étudiants à ce groupe' class="btn btn-outline-primary mr-2" href="<?php echo WEB_PATH; ?>groupes/inscrire.php">Inscrire</a>
  </div>
  <div class='form-group mb-2'>
    <a id="listeLink" data-toggle='tooltip' data-placement='top' title='Retourner à la liste des groupes' class="btn btn-outline-primary mr-2" href="<?php echo WEB_PATH; ?>groupes/index.php">Retour</a>
  </div>
</form>
</div>

<div id="contenu"></div>

<?php WebPage::addOnReady("selectionGroupe();"); ?>