<?php
// @need 

WebPage::addOnlineScript("var WEB_PATH = '".WEB_PATH."';");
WebPage::addOnlineScript("var nb = ".count($data['etudiants']).";");
WebPage::addJSScript("public/js/active-tooltips.js");
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Inscription d'étudiants</h2>
    <p class="lead mb-0">
      Sélectionnez les étudiants dans la liste pour les inscrire dans le groupe <?php echo $data['groupeObj']; ?>.
    </p>
  </div>
</section>

<form id="controlForm" action="" method="post"></form>

<script>
var request = null;
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
function inscription(id) {
    $('#globalMsg').remove();
    if(request == null) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'groupes/ws.php',
                data: { 'mode' : 7, 'etudiant' : id },
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response['code'] == 1) {
                    deleteElement('ligne' + response['etudiant']);
                    nb--;
                    if(nb == 0) {
                        content = "<div class='media alert-danger text-center'><div class='media-body'><p class='lead mb-0'>" +
                                  "Plus d'étudiant à inscrire.</p></div></div>";
                        updateContent('contenu', content);
                    }
                }
                else {
                    // #TODO# Message d'erreur
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

<form class="form-inline justify-content-center mt-2">
  <div class="form-group mb-2">
     <a id="listeLink" data-toggle='tooltip' data-placement='top' title='Retourner à la liste des étudiants' class="btn btn-outline-primary mr-2" href="<?php echo WEB_PATH; ?>groupes/etudiants.php">Retour</a>
  </div>
</form>

<div id="contenu">
<?php include("inscrire_liste.php"); ?>
</div>