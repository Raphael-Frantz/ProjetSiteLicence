<?php
WebPage::addJSScript("public/js/active-tooltips.js");
WebPage::addJSScript("public/js/tools.js");
WebPage::addOnlineScript("var WEB_PATH = '".WEB_PATH."';");
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Mes informations</h2>
    <p class="lead mb-0">Bienvenue <?php echo UserModel::getCurrentUser(); ?>. Retrouvez ici toutes vos informations.</p>
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

<section>
  <div class="container">
    <h3 class="mb-4">Responsabilités d'EC</h3>
<?php
if(count($data['ECs']) > 0) {
    echo <<<HTML
<table class="table table-striped table-bordered" id="tableEtudiants">
  <thead>
    <tr>
      <th scope="col">EC</th>
      <th scope="col" class='text-right'>Actions</th>
    </tr>
  </thead>
HTML;
    foreach($data['ECs'] as $EC) {
        echo <<<HTML
    <tr>
      <td>{$EC['nom']}</td>
      <td class='text-right'>
        <a data-toggle='tooltip' data-placement='top' title="Informations" 
         href="javascript:setEC({$EC['id']}, 'ecs/informations.php')" class='btn btn-sm mr-1 btn-outline-primary my-1'>
          <i class='icon-info'></i>
        </a>
        <a data-toggle='tooltip' data-placement='top' title="Liste des étudiants" 
         href="javascript:setEC({$EC['id']}, 'groupesec/etudiants.php')" class='btn btn-sm mr-1 btn-outline-primary my-1'>
          <i class='icon-people'></i>
        </a>
        <a data-toggle='tooltip' data-placement='top' title="Gestion des groupes" 
         href="javascript:setEC({$EC['id']}, 'groupesec/index.php')" class='btn btn-sm mr-1 btn-outline-primary my-1'>
          <i class='icon-layers'></i>
        </a>
        <a data-toggle='tooltip' data-placement='top' title="Gestion des épreuves" 
         href="javascript:setEC({$EC['id']}, 'epreuves/index.php')" class='btn btn-sm mr-1 btn-outline-primary my-1'>
          <i class='icon-notebook'></i>
        </a>
        <a data-toggle='tooltip' data-placement='top' title="Saisie des notes" 
         href="javascript:setEC({$EC['id']}, 'notes/saisie.php')" class='btn btn-sm mr-1 btn-outline-primary my-1'>
          <i class='icon-book-open'></i>
        </a>
        <a data-toggle='tooltip' data-placement='top' title="Gestion du présentiel"
         href="javascript:setEC({$EC['id']}, 'presentiel/saisie.php')" class='btn btn-sm mr-1 btn-outline-primary'>
          <i class='icon-user-following'></i>
        </a>
      </td>
    </tr>
HTML;
    }
    echo "</table>";
}
else {
?>
    <div class='card mb-4 border-primary box-shadow'>
      <div class='card-body'>
        <p class='lead mb-0'>Vous êtes responsable d'aucun EC.</p>
      </div>
    </div>
<?php
}
?>
  </div>
</section>

<section>
  <div class="container">
    <h3 class="mb-4">Intervention dans des EC</h3>
<?php
if(count($data['intECs']) > 0) {
    echo <<<HTML
<table class="table table-striped table-bordered" id="tableEtudiants">
  <thead>
    <tr>
      <th scope="col">EC</th>
      <th scope="col" class='text-right'>Actions</th>
    </tr>
  </thead>
HTML;
    foreach($data['intECs'] as $EC) {
        echo <<<HTML
    <tr>
      <td>{$EC['nom']}</td>
      <td class='text-right'>
        <a data-toggle='tooltip' data-placement='top' title="Infos/Config." 
         href="javascript:setEC({$EC['id']}, 'ecs/informations.php')" class='btn btn-sm mr-1 btn-outline-primary'>
          <i class='icon-info'></i>
        </a>
        <a data-toggle='tooltip' data-placement='top' title="Liste des étudiants" 
         href="javascript:setEC({$EC['id']}, 'groupesec/etudiants.php')" class='btn btn-sm mr-1 btn-outline-primary'>
          <i class='icon-people'></i>
        </a>
        <a data-toggle='tooltip' data-placement='top' title="Gestion des groupes" 
         href="javascript:setEC({$EC['id']}, 'groupesec/index.php')" class='btn btn-sm mr-1 btn-outline-primary'>
          <i class='icon-layers'></i>
        </a>
        <a data-toggle='tooltip' data-placement='top' title="Gestion des épreuves" 
         href="javascript:setEC({$EC['id']}, 'epreuves/index.php')" class='btn btn-sm mr-1 btn-outline-primary'>
          <i class='icon-notebook'></i>
        </a>
        <a data-toggle='tooltip' data-placement='top' title="Saisie des notes" 
         href="javascript:setEC({$EC['id']}, 'notes/saisie.php')" class='btn btn-sm mr-1 btn-outline-primary'>
          <i class='icon-book-open'></i>
        </a>
        <a data-toggle='tooltip' data-placement='top' title="Gestion du présentiel"
         href="javascript:setEC({$EC['id']}, 'presentiel/saisie.php')" class='btn btn-sm mr-1 btn-outline-primary'>
          <i class='icon-user-following'></i>
        </a>
      </td>
    </tr>
HTML;
    }
    echo "</table>";
}
else {
?>
    <div class='card mb-4 border-primary box-shadow'>
      <div class='card-body'>
        <p class='lead mb-0'>Vous intervenez dans aucun EC.</p>
      </div>
    </div>
<?php
}
?>
  </div>
</section>