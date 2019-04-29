<?php
// @need tuteurs la liste des enseignants potentiels

WebPage::addOnlineScript("var WEB_PATH = '".WEB_PATH."';");
WebPage::addOnlineScript("var nb = ".count($data['tuteurs']).";");
WebPage::addJSScript("public/js/tools.js");
WebPage::addJSScript("public/js/active-tooltips.js");
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Ajout de tuteurs</h2>
    <p class="lead mb-0">
      Sélectionnez les enseignants dans la liste pour les ajouter comme tuteur du diplôme <?php echo $data['diplomeObj']; ?>.
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
                url: WEB_PATH + 'tutorat/ws.php',
                data: { 'mode' : 5, 'enseignant' : id },
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response['code'] == 1) {
                    deleteElement('ligne' + response['enseignant']);
                    nb--;
                    if(nb == 0) {
                        content = "<div class='media alert-danger text-center'><div class='media-body'><p class='lead mb-0'>" +
                                  "Plus d'enseignant à ajouter.</p></div></div>";
                        updateContent('contenu', content);
                    }
                }
                else {
                    displayErrorModal(response['msg']);
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
  <form class="form-inline justify-content-end mt-2">
    <div class="form-group mb-2">
       <a id="listeLink" data-toggle='tooltip' data-placement='top' title='Retourner à la liste des tuteurs' class="btn btn-outline-primary mr-2" href="<?php echo WEB_PATH; ?>tutorat/tuteurs.php">Retour</a>
    </div>
  </form>
</div>

<div id="contenu">
<?php include("tuteurs_liste.php"); ?>
</div>