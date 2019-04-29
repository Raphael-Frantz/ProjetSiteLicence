<?php
// @need groupes la liste des groupes

WebPage::addOnlineScript("var WEB_PATH = '".WEB_PATH."';");
WebPage::addJSScript("public/js/tools.js");
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Gestion des groupes</h2>
    <p class="lead mb-0">Ici, vous trouverez la liste de tous les groupes créés sur le site. Vous pouvez en créer ou les modifier.</p>
  </div>
</section>

<form id="controlForm" action="" method="post"></form>

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
                url: WEB_PATH + 'groupes/ws.php',
                data: { 'mode' : 1, 'diplome' : $('#diplome').val(), 'semestre' : $('#semestre').val() },
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
</script>

<form class="form-inline justify-content-center mt-2">
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
     <a id="ajouterLink" class="btn btn-outline-primary mr-2" href="ajouter.php">Créer un groupe</a>
  </div>
</form>

<div id="contenu"></div>

<?php WebPage::addOnReady("selectionSemestre();"); ?>