<?php
// @need users la liste des enseignants
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Gestion des enseignants</h2>
    <p class="lead mb-0">Ici, vous trouverez la liste de tous les enseignants.</p>
  </div>
</section>

<form id="controlForm" action="" method="post"></form>

<form class="form-inline justify-content-center mt-2">
  <div class="form-group mb-2">
     <a class="btn btn-outline-primary" href="ajouter.php">Créer un enseignant</a>
  </div>
</form>

<?php
if(!isset($data['users']) || ($data['users'] === null) || (sizeof($data['users']) == 0)) {
?>
<div class="media alert-danger text-center">
  <div class="media-body">
    <p class="lead mb-0">Il n'y a pas d'enseignant dans la base actuellement.</p>
  </div>
</div>
<?php
}
else {
?>

<table class="table table-striped">
  <thead>
    <tr>
      <th scope="col">Nom/prénom</th>
      <th scope="col">Email</th>
      <th class="text-right" scope="col">Actions</th>
    </tr>
  </thead>
  <tbody>
<?php
WebPage::addJSScript("public/js/active-tooltips.js");

foreach($data['users'] as $user) {
  echo "<tr><td>{$user['nom']} {$user['prenom']}</td>";
  echo "<td>{$user['email']}</td>";
  echo "<td class='text-right'>";
  echo "<a href='mailto:{$user['email']}' class='btn btn-sm btn-outline-primary' data-toggle='tooltip' data-placement='top' title='Envoyer un email'><i class='icon-envelope'></i></a>&nbsp;";
  echo "<button name='idModi' type='submit' class='btn btn-sm btn-outline-primary' data-toggle='tooltip' data-placement='top' title=\"Éditer l'enseignant\" form='controlForm' formaction='".WEB_PATH."users/modifier.php' value='{$user['id']}'><i class='icon-wrench'></i></button>&nbsp;";
  echo "<button name='idRole' type='submit' class='btn btn-sm btn-outline-primary' data-toggle='tooltip' data-placement='top' title='Prendre son rôle' form='controlForm' formaction='".WEB_PATH."users/role.php' value='{$user['id']}'><i class='icon-user'></i></button>&nbsp;";
  echo "<button name='idSupp' type='submit' class='btn btn-sm btn-outline-danger' data-toggle='tooltip' data-placement='top' title=\"Supprimer l'enseignant\" form='controlForm' formaction='".WEB_PATH."users/supprimer.php' value='{$user['id']}'><i class='icon-trash'></i></button>";
  echo "</td>";
  echo "</tr>";
}
?>
  </tbody>
</table>
<?php
}