<?php
WebPage::addOnlineScript("var WEB_PATH = '".WEB_PATH."';");
WebPage::addJSScript("public/js/tools.js");
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Tuteurs et groupes</h2>
    <p class="lead mb-0">Retrouvez ici les affectations des groupes et tuteurs.</p>
  </div>
</section>

<script>
var request = null;
function selectionDiplome() {
    if((request == null) && ($('#diplome').val() != -1)) {
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
                        $("#semestre").append("<option value='" + i + "' checked>Semestre " + i + "</option>");
                    else
                        $("#semestre").append("<option value='" + i + "'>Semestre " + i + "</option>");
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
    if((request == null) && ($('#diplome').val() != -1)) {
        displayWaitModal();
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'groupes/ws.php',
                data: { 'mode' : 5, 'diplome' : $('#diplome').val(), 'semestre' : $('#semestre').val() },
                dataType: 'html'
            });
        request.done(function (response, textStatus, jqXHR) {
                $('#contenu').empty().html(response);
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
</script>

<div class="container">
  <div class="alert alert-danger lead text-center mt-2" role="alert">
    Les listes contiennent uniquement les étudiants qui ont procédé à leur inscription pédagogique.
    Si vous n'apparaissent pas, merci de contacter au plus vite le responsable de la Licence.
  </div>       
  
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
            echo ">Semestre $i</option>";
        }
    }
?>        
        </select>
      </div>
    </form>  

  </div>

</div>

<div id="contenu"></div>

<?php WebPage::addOnReady("selectionDiplome();"); ?>