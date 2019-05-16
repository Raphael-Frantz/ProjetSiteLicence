<?php
// @need diplomes la liste des diplômes
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Gestion des diplômes</h2>
    <p class="lead mb-0">Ici, vous trouverez la liste de tous les diplômes créés sur le site. Vous pouvez en créer ou les modifier.</p>
  </div>
</section>

<form id="controlForm" action="" method="post"></form>

<?php
if(UserModel::estAdmin()) {
    echo <<<HTML
<form class="form-inline justify-content-center mt-2">
  <div class="form-group mb-2">
     <a class="btn btn-outline-primary" href="ajouter.php">Créer un diplôme</a>
  </div>
</form>
HTML;
}

if(!isset($data['diplomes']) || ($data['diplomes'] === null) || (sizeof($data['diplomes']) == 0)) {
?>
<div class="media alert-danger text-center">
  <div class="media-body">
    <p class="lead mb-0">Il n'y a pas de diplôme dans la base actuellement.</p>
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

foreach($data['diplomes'] as $diplome) {
  echo "<tr><td>".$diplome['intitule']."</td>";
  echo "<td class='text-right'>";
  if(UserModel::estAdmin())
    echo "<button name='idModi' type='submit' class='btn btn-sm btn-outline-primary' data-toggle='tooltip' data-placement='top' title='Éditer le diplôme' form='controlForm' formaction='".WEB_PATH."diplomes/modifier.php' value='{$diplome['id']}'><i class='icon-wrench'></i></button>&nbsp;";
  echo "<button name='idEdit' type='submit' class='btn btn-sm btn-outline-primary' data-toggle='tooltip' data-placement='top' title='Structure du diplôme' form='controlForm' formaction='".WEB_PATH."diplomes/structure.php' value='{$diplome['id']}'><i class='icon-list'></i></button>&nbsp;";
  if(UserModel::estAdmin())
    echo "<button name='idSupp' type='submit' class='btn btn-sm btn-outline-danger' data-toggle='tooltip' data-placement='top' title='Supprimer le diplôme' form='controlForm' formaction='".WEB_PATH."diplomes/supprimer.php' value='{$diplome['id']}'><i class='icon-trash'></i></button>";
  echo "</td>";
  echo "</tr>";
}
?>
  </tbody>
</table>
<?php
}
