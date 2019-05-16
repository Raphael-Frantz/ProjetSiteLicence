<?php
// @need ECS la liste des ECs
?>
<script>
$(document).ready(function () {

    $('#ec').DataTable()( {
				dom: 'ptlf',
					language: {

            url: "DataTables/media/js/French.json"
        },

        dom: "tip",

        pagingType: "simple",

        pageLength: 8,

        order: [[1, 'desc'], [0, 'asc']],

        columns: [

            {type: "text"},

            {type: "html"},

            {orderable: false}

        ]

    });

});


});
</script>
<?php
WebPage::addCSSScript("vendor/DataTables/media/css/jquery.dataTables.min.css");
WebPage::addJSScript("vendor/DataTables/media/js/jquery.dataTables.min.js");
WebPage::addOnReady("   $('#ec').DataTable();");
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Gestion des EC</h2>
    <p class="lead mb-0">
        Ici, vous trouverez la liste de tous les EC créés sur le site.
        Ils sont communs à tous les diplômes.
        Vous pouvez en créer ou les modifier.
    </p>
  </div>
</section>

<form id="controlForm" action="" method="post"></form>

<form class="form-inline justify-content-center mt-2">
  <div class="form-group mb-2">
     <a class="btn btn-outline-primary" href="ajouter.php">Créer un EC</a>
  </div>
</form>

<?php
if(!isset($data['ECS']) || ($data['ECS'] === null) || (sizeof($data['ECS']) == 0)) {
?>
<div class="media alert-danger text-center">
  <div class="media-body">
    <p class="lead mb-0">Il n'y a pas d'EC dans la base actuellement.</p>
  </div>
</div>
<?php
}
else {
?>
<table class="table table-striped" id="ec" >
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
  echo "<tr><td>{$ECS['code']} - {$ECS['intitule']}</td>";
  echo "<td class='text-right'>";
  echo "<button name='idModi' type='submit' class='btn btn-sm btn-outline-primary mb-1' data-toggle='tooltip' data-placement='top' title=\"Éditer l'EC\" form='controlForm' formaction='".WEB_PATH."ecs/modifier.php' value='{$ECS['id']}'><i class='icon-wrench'></i></button>&nbsp";
  if(UserModel::estAdmin())
    echo "<button name='idSupp' type='submit' class='btn btn-sm btn-outline-danger' data-toggle='tooltip' data-placement='top' title=\"Supprimer l'EC\" form='controlForm' formaction='".WEB_PATH."ecs/supprimer.php' value='{$ECS['id']}'><i class='icon-trash'></i></button>";
  echo "</td>";
  echo "</tr>";
}
?>
  </tbody>
</table>
<?php
}