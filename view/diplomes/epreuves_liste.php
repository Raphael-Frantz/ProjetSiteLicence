<?php
// @need 

if(!isset($data['epreuves']) || ($data['epreuves'] === null) || (sizeof($data['epreuves']) == 0)) {
?>
<div class="media alert-danger text-center">
  <div class="media-body">
    <p class="lead mb-0">Il n'y a pas d'épreuve pour cet EC dans la base actuellement.</p>
  </div>
</div>
<?php
}
else {
?>
<table class="table table-striped">
  <thead>
    <tr>
      <th scope="col">EC</th>
      <th scope="col">Intitulé</th>
      <th scope="col">Type</th>
      <th scope="col">Max</th>
      <th scope="col">Session 1</th>
      <th scope="col">Session 2</th>
      <th scope="col">Session 1 (disp)</th>
      <th scope="col">Session 2 (disp)</th>
      <th class="text-right" scope="col">Actions</th>
    </tr>
  </thead>
  <tbody>
<?php
foreach($data['epreuves'] as $epreuve) {
 {
        echo "<tr><td>".$epreuve['code']." - ".$epreuve['EC']."</td>";
        echo "<td>".$epreuve['intitule']."</td>";
        echo "<td>".Epreuve::TYPE_DESCRIPTION[$epreuve['type']]."</td>";
        echo "<td>".$epreuve['max']."</td>";
        echo "<td>".$epreuve['session1']."%</td>";
        echo "<td>".$epreuve['session2']."%</td>";
        echo "<td>".$epreuve['session1disp']."%</td>";
        echo "<td>".$epreuve['session2disp']."%</td>";
        echo "<td class='text-right'>";
        
        // Activation/désactivation de la saisie de l'épreuve
        if($epreuve['active'] == 1) {
          $tmp1 = "style='display: none;'";
          $tmp2 = "";
        }
        else {
          $tmp1 = "";
          $tmp2 = "style='display: none;'";
        }
        echo "<a id='act_{$epreuve['id']}' $tmp1 class='btn btn-sm btn-outline-success mr-1' data-toggle='tooltip' data-placement='top' ".
           "title=\"Active l'épreuve\" href='javascript:active({$epreuve['id']},1)'><i class='icon-check'></i></a>";
        echo "<a id='des_{$epreuve['id']}' $tmp2 class='btn btn-sm btn-outline-danger mr-1' data-toggle='tooltip' data-placement='top' ".
           "title=\"Désactive l'épreuve\" href='javascript:active({$epreuve['id']},0)'><i class='icon-close'></i></a>";
        
        // Cache/rend visible l'épreuve
        if($epreuve['visible'] == 1) {
          $tmp1 = "style='display: none;'";
          $tmp2 = "";
        }
        else {
          $tmp1 = "";
          $tmp2 = "style='display: none;'";
        }
        echo "<a id='vis_{$epreuve['id']}' $tmp1 class='btn btn-sm btn-outline-success mr-1' data-toggle='tooltip' data-placement='top' ".
           "title=\"Rend visible l'épreuve\" href='javascript:visible({$epreuve['id']},1)'><i class='icon-eye'></i></a>";
        echo "<a id='cac_{$epreuve['id']}' $tmp2 class='btn btn-sm btn-outline-danger mr-1' data-toggle='tooltip' data-placement='top' ".
           "title=\"Cache l'épreuve\" href='javascript:visible({$epreuve['id']},0)'><i class='icon-eye'></i></a>";

        // Bloque/débloque l'épreuve
        if($epreuve['bloquee'] == 1) {
          $tmp1 = "style='display: none;'";
          $tmp2 = "";
        }
        else {
          $tmp1 = "";
          $tmp2 = "style='display: none;'";
        }
        echo "<a id='blo_{$epreuve['id']}' $tmp1 class='btn btn-sm btn-outline-success mr-1' data-toggle='tooltip' data-placement='top' ".
           "title=\"Rend visible l'épreuve\" href='javascript:bloque({$epreuve['id']},1)'><i class='icon-lock'></i></a>";
        echo "<a id='deb_{$epreuve['id']}' $tmp2 class='btn btn-sm btn-outline-danger mr-1' data-toggle='tooltip' data-placement='top' ".
           "title=\"Cache l'épreuve\" href='javascript:bloque({$epreuve['id']},0)'><i class='icon-lock'></i></a>";
        
        // Saisie des notes
        echo <<<HTML
<a data-toggle='tooltip' data-placement='top' title='Saisie des notes'
       href="javascript:setEpreuve({$epreuve['id']}, 'notes/saisie.php')" class='btn btn-sm btn-outline-primary mr-1'>
        <i class='icon-book-open'></i>
      </a>
HTML;
        
        // Suppression/modification de l'épreuve
        $lienSupp = WEB_PATH."epreuves/supprimer.php";
        $lienModi = WEB_PATH."epreuves/modifier.php";
        echo "<button name='idModi' type='submit' class='btn btn-sm btn-outline-primary mr-1' data-toggle='tooltip' data-placement='top' ".
           "title=\"Modifier l'épreuve\" form='controlForm' formaction='$lienModi' value='{$epreuve['id']}'><i class='icon-wrench'></i></button>";
        echo "<button name='idSupp' type='submit' class='btn btn-sm btn-outline-danger mr-1' data-toggle='tooltip' data-placement='top' ".
           "title=\"Supprimer l'épreuve\" form='controlForm' formaction='$lienSupp' value='{$epreuve['id']}'><i class='icon-trash'></i></button>";

        echo "</td>";
        echo "</tr>";
    }
}
?>
  </tbody>
</table>
<?php
}