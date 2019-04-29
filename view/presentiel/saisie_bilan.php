<?php
// @need bilan le bilan du présentiel
?>

<table class="table table-striped">
  <thead>
    <tr>
      <th scope='col'></th>
      <th scope='col'>Numéro</th>
      <th scope='col'>Etudiants</th>
      <th scope='col'>CM</th>
      <th scope='col'>TD</th>
      <th scope='col'>TP</th>
    </tr>
  </thead>
  <tbody>
<?php
$i = 1;
foreach($data['bilan'] as $etudiant) {
    echo <<<HTML
    <tr id='lg_{$etudiant['id']}'>
       <td>{$i}</td>
       <td>{$etudiant['numero']}</td>
HTML;
    if(UserModel::estTuteur() || UserModel::estRespDiplome())
        echo <<<HTML
          <td><a href="javascript:setEtudiant({$etudiant['id']}, 'etudiants/ip.php', 'presentiel/saisie.php')">{$etudiant['nom']} {$etudiant['prenom']}</a>
HTML;
    else
        echo <<<HTML
          <td>{$etudiant['nom']} {$etudiant['prenom']}  
HTML;
    foreach([Groupe::GRP_CM, Groupe::GRP_TD, Groupe::GRP_TP] as $type) {
        echo "<td>";
        foreach([InscriptionSeanceModel::TYPE_PRESENT => "success", 
                 InscriptionSeanceModel::TYPE_ABSENT => "danger", 
                 InscriptionSeanceModel::TYPE_JUSTIFIE => "warning",
                 InscriptionSeanceModel::TYPE_RATTRAPAGE => "primary"] as $typeP => $nb) {
            echo "<span class='badge badge-$nb mr-1 mb-1 p-2'>{$etudiant['pres'][$type][$typeP]}</span> ";
        }
        echo "</td>";
    }
    echo "</tr>";
    $i++;
}
?>
  </tbody>
</table>