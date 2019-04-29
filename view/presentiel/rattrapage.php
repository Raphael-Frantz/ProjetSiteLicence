<?php
// @need 

WebPage::addOnlineScript("var WEB_PATH = '".WEB_PATH."';");
WebPage::addOnlineScript("var nb = ".count($data['etudiants']).";");
WebPage::addJSScript("public/js/active-tooltips.js");
WebPage::addJSScript("public/js/tools.js");
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Rattrapage</h2>
    <p class="lead mb-0">
      Sélectionnez les étudiants dans la liste pour les inscrire dans la séance courante.
    </p>
  </div>
</section>

<form id="controlForm" action="" method="post"></form>

<script>
var request = null;
function inscription(id) {
    $('#globalMsg').remove();
    if(request == null) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'presentiel/ws.php',
                data: { 'mode' : 6, 'etudiant' : id, 'type' : 1 },
                dataType: 'json'
            });
        request.done(function (response, textStatus, jqXHR) {
                if(response['code'] == -2) {
                    document.location.href = WEB_PATH;
                }
                else if(response['code'] == 1) {
                    deleteElement('ligne' + response['etudiant']);
                    nb--;
                    if(nb == 0) {
                        content = "<div class='media alert-danger text-center'><div class='media-body'><p class='lead mb-0'>" +
                                  "Plus d'étudiant(e) à ajouter.</p></div></div>";
                        updateContent('contenu', content);
                    }
                }
                else
                    displayErrorModal(response['erreur']);
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
       <a id="listeLink" data-toggle='tooltip' data-placement='top' title='Retourner à la séance' class="btn btn-outline-primary mr-2" href="<?php echo WEB_PATH; ?>presentiel/saisie.php">Retour</a>
    </div>
  </form>
</div>

<div id="contenu">
<?php
if(count($data['etudiants']) == 0) {
    echo <<<HTML
<div class="media alert-danger text-center" id="message">
  <div class="media-body">
    <p class="lead mb-0" id="contenuMessage">Il n'y a aucun(e) étudiant(e) d'un autre groupe à ajouter à cette séance.</p>
  </div>
</div>
HTML;
}
else {
    echo <<<HTML
<table class="table table-striped" id="tableEtudiants">
  <thead>
    <tr>
      <th scope="col">Numéro</th>
      <th scope="col">Nom</th>
      <th scope="col">Prénom</th>
      <th scope="col">Email</th>
      <th class="text-right" scope="col">Actions</th>
    </tr>
  </thead>
HTML;
    foreach($data['etudiants'] as $etudiant) {
        echo <<<HTML
        <tr id='ligne{$etudiant['id']}'>
          <th scope='row'>{$etudiant['numero']}</th>
          <td>{$etudiant['nom']}</td>
          <td>{$etudiant['prenom']}</td>
          <td>{$etudiant['email']}</td>
          <td class="text-right">
            <a class='btn btn-sm btn-outline-warning mr-2' href='javascript:inscription({$etudiant['id']})' data-toggle="tooltip" data-placement="top" title="Inscrire l'étudiant">
              <i class="icon-plus"></i>
            </a>
          </td>
        </tr>
HTML;
    }
    echo "</table>";
}
?>
</div>