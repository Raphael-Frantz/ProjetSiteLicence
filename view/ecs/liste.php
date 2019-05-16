<?php
// @need ECS la liste des ECs

WebPage::addOnlineScript("var WEB_PATH = '".WEB_PATH."';");

if(isset($data['current'])) {
    WebPage::addOnlineScript("$([document.documentElement, document.body]).animate({ scrollTop: $(\"#EC_{$data['current']}\").offset().top }, 2000);");
}
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Liste des ECs</h2>
    <p class="lead mb-0">
        Ici, vous trouverez la liste de tous les EC dont vous êtes le responsable ou dans lesquels vous intervenez.
    </p>
  </div>
</section>

<script>
var request = null;
function setEC(id, url) {
    if(request == null) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'groupesec/ws.php',
                data: { 'mode' : 5, 'EC' : id, 'groupeEC' : -1 },
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

<?php
if(!isset($data['ECS']) || ($data['ECS'] === null) || (sizeof($data['ECS']) == 0)) {
?>
<div class="media alert-danger text-center">
  <div class="media-body">
    <p class="lead mb-0">Vous n'avez aucun accès actuellement.</p>
  </div>
</div>
<?php
}
else {
?>
<table class="table table-striped">
  <thead>
    <tr>
      <th scope="col">Intitulé</th>
      <th class="text-right" scope="col">Actions</th>
    </tr>
  </thead>
  <tbody>
<?php
WebPage::addJSScript("public/js/active-tooltips.js");

foreach($data['ECS'] as $ECS) {
    echo <<<HTML
  <tr id='EC_{$ECS['id']}'>
    <td>{$ECS['code']} - {$ECS['intitule']}</td>
    <td class='text-right'>
      <a data-toggle='tooltip' data-placement='top' title="Informations" 
       href="javascript:setEC({$ECS['id']}, 'ecs/informations.php')" class='btn btn-sm mr-1 btn-outline-primary'>
        <i class='icon-info'></i>
      </a>
      <a data-toggle='tooltip' data-placement='top' title="Liste des étudiants" 
       href="javascript:setEC({$ECS['id']}, 'groupesec/etudiants.php')" class='btn btn-sm mr-1 btn-outline-primary'>
        <i class='icon-people'></i>
      </a>
      <a data-toggle='tooltip' data-placement='top' title="Gestion des groupes" 
       href="javascript:setEC({$ECS['id']}, 'groupesec/index.php')" class='btn btn-sm mr-1 btn-outline-primary'>
        <i class='icon-layers'></i>
      </a>
      <a data-toggle='tooltip' data-placement='top' title="Gestion des épreuves" 
       href="javascript:setEC({$ECS['id']}, 'epreuves/index.php')" class='btn btn-sm mr-1 btn-outline-primary'>
        <i class='icon-notebook'></i>
      </a>
      <a data-toggle='tooltip' data-placement='top' title="Saisie des notes" 
       href="javascript:setEC({$ECS['id']}, 'notes/saisie.php')" class='btn btn-sm mr-1 btn-outline-primary'>
        <i class='icon-book-open'></i>
      </a>
      <a data-toggle='tooltip' data-placement='top' title="Gestion du présentiel"
       href="javascript:setEC({$ECS['id']}, 'presentiel/saisie.php')" class='btn btn-sm mr-1 btn-outline-primary'>
        <i class='icon-user-following'></i>
      </a>
    </td>
  </tr>
HTML;
}
?>
  </tbody>
</table>
<?php
}