<?php
// @need 

if((count($data['etudiants']) == 0) || (count($data['epreuves']) == 0)) {
    if(count($data['etudiants']) == 0)
        $msg = "Il n'y a aucun étudiant dans ce groupe.";
    else
        $msg = "Il n'y a aucune épreuve dans cet EC.";
    echo <<<HTML
<div class="media alert-danger text-center" id="message">
  <div class="media-body">
    <p class="lead mb-0" id="contenuMessage">{$msg}</p>
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
      <th scope="col">Nom - Prénom</th>
HTML;
    $total = [];
    foreach($data['epreuves'] as $epreuve) {
        echo <<<HTML
<th scope='col' data-toggle='tooltip' data-placement='top' 
    title='{$epreuve['session1']}% / {$epreuve['session2']}% / {$epreuve['session1disp']}% / {$epreuve['session2disp']}%'>
  {$epreuve['intitule']}
</th>
HTML;
        $total[] = [ 'nb' => 0, 'max' => 0, 'min' => $epreuve['max'], 'bar' => $epreuve['max'], 'moy' => 0 ];
    }
    echo <<<HTML
    </tr>
  </thead>
HTML;
    for($i = 0; $i < count($data['etudiants'][0]); $i++) {
        echo <<<HTML
        <tr>
          <th scope='row'>{$data['etudiants'][0][$i]['numero']}</th>
HTML;
        if(UserModel::estTuteur() || UserModel::estRespDiplome())
            echo <<<HTML
          <td><a href="javascript:setEtudiant({$data['etudiants'][0][$i]['id']}, 'etudiants/ip.php', 'groupesec/etudiants.php')">{$data['etudiants'][0][$i]['nom']} {$data['etudiants'][0][$i]['prenom']}</a></td>
HTML;
        else
            echo <<<HTML
          <td>{$data['etudiants'][0][$i]['nom']} {$data['etudiants'][0][$i]['prenom']}</td>
HTML;
        for($j = 0; $j < count($data['etudiants']); $j++) {
            $note = $data['etudiants'][$j][$i]['note'];
            if($note == null) $note = Note::TYPE_AUCUNE;
            $max = $data['etudiants'][$j][$i]['max'];
            
            // Statistiques
            if($note >= 0) {
                $total[$j]['nb']++;
                if($note < $total[$j]['min']) $total[$j]['min'] = $note;
                if($note > $total[$j]['max']) $total[$j]['max'] = $note;
                $total[$j]['moy'] += $note;
            }
            
            // Affichage de la note
            echo "<td>".Note::convertirStr($note, $max)."</td>";
        }
        echo "</tr>";
    }
    
    // Affichage du nombre de saisies
    echo <<<HTML
    <tr>
      <th scope='row' colspan='2' class='text-right table-dark'>Notes saisies</th>
HTML;
    for($j = 0; $j < count($data['etudiants']); $j++) {
        echo "<td>{$total[$j]['nb']}</td>";
    }
    echo "</tr>";
    
    // Affichage de la moyenne
    echo <<<HTML
    <tr>
      <th scope='row' colspan='2' class='text-right table-dark'>Moyenne</th>
HTML;
    for($j = 0; $j < count($data['etudiants']); $j++) {
        if($total[$j]['nb'] != 0) {
            $total[$j]['moy'] /= $total[$j]['nb'];
            
            echo "<td>".Note::convertirStr(round($total[$j]['moy'], 3), $total[$j]['bar'])."</td>";
        }
        else {
            echo "<td>-</td>";
        }
    }
    echo "</tr>";
    
    // Affichage du max
    echo <<<HTML
    <tr>
      <th scope='row' colspan='2' class='text-right table-dark'>Maximum</th>
HTML;
    for($j = 0; $j < count($data['etudiants']); $j++) {
        if($total[$j]['nb'] != 0)
            echo "<td>".Note::convertirStr($total[$j]['max'], $total[$j]['bar'])."</td>";
        else
            echo "<td>-</td>";
    }
    echo "</tr>";
    
    // Affichage du min
    echo <<<HTML
    <tr>
      <th scope='row' colspan='2' class='text-right table-dark'>Minimum</th>
HTML;
    for($j = 0; $j < count($data['etudiants']); $j++) {
        if($total[$j]['nb'] != 0)
            echo "<td>".Note::convertirStr($total[$j]['min'], $total[$j]['bar'])."</td>";
        else
            echo "<td>-</td>";
    }
    echo "</tr>";
    
    echo "</table>";
}