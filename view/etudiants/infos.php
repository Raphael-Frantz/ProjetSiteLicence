<?php
/* Pour la recherche */
//WebPage::addJSScript("public/js/typeahead.min.js"); 
//WebPage::addOnlineScript("$('input[name=rechercher]').typeahead({ minLength: 3, source: rechercher, updater: selectEtudiant });");

WebPage::addOnlineScript("var WEB_PATH = '".WEB_PATH."';");
WebPage::addJSScript("public/js/tools.js"); 
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Informations sur les étudiants</h2>
    <p class="lead mb-0">
      Vous pouvez obtenir les informations sur les étudiants : IP, notes, présentiel.
    </p>
  </div>
</section>

<script>
var request = null;
function selectionDiplome() {
    if((request == null) && ($('#diplome').val() != -1)) {        
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'etudiants/ws.php',
                data: { 'mode' : 6, 'diplome' : $('#diplome').val(), 'semestre' : $('#semestre').val() },
                dataType: 'html'
            });
        request.done(function (response, textStatus, jqXHR) {
                $('[data-toggle="tooltip"]').tooltip('dispose');
                $('#contenu').empty().html(response);
                $('[data-toggle="tooltip"]').tooltip();
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                request = null;
            });
    }
}
function setEtudiant(id, url) {
    if(request == null) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'etudiants/ws.php',
                data: { 'mode' : 7, 'etudiant' : id },
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                document.location.href = WEB_PATH + url;
                request = null;
            });
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                request = null;
            });   
    }
}
</script>

<form id="controlForm" action="" method="post"></form>

<form class="form-inline justify-content-center mt-2">
  <div class="form-group mb-2">
    <select class="form-control mr-2" id="diplome" name="diplome" onchange="javascript:selectionDiplome()">
        <option value="-1">Sélectionnez un diplôme</option>
<?php
foreach($data['diplomes'] as $diplome) {
    echo "<option value=\"{$diplome['id']}\"";
    if(isset($data['diplome']) && ($data['diplome'] == $diplome['id']))
        echo " selected=\"selected\"";
    echo ">{$diplome['intitule']}</option>";
}
?>
    </select>
  </div>
</form>

<div id="contenu"></div>

<?php WebPage::addOnReady("selectionDiplome(true);"); ?>