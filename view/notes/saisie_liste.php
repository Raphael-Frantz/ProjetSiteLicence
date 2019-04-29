<?php
// @need 

if(count($data['etudiants']) == 0) {
    $msg = "Il n'y a aucun étudiant dans ce groupe.";
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
      <th scope="col">Note</th>
HTML;
    if($data['saisie']) echo "      <th scope='col'>Saisie (/ {$data['etudiants'][0]['max']})</th>";
    echo <<<HTML
    </tr>
  </thead>
HTML;
    $i = 0;
    $total = [ 'nb' => 0, 'max' => 0, 'min' => $data['etudiants'][0]['max'], 'bar' => $data['etudiants'][0]['max'], 'moy' => 0 ];
    foreach($data['etudiants'] as &$etudiant) {
        $i++;
        if($etudiant['note'] == null) $etudiant['note'] = Note::TYPE_AUCUNE;
        $msg = Note::convertirStr($etudiant['note'], $etudiant['max']);
        
        // Statistiques
        if($etudiant['note'] >= 0) {
            $total['nb']++;
            if($etudiant['note'] < $total['min']) $total['min'] = $etudiant['note'];
            if($etudiant['note'] > $total['max']) $total['max'] = $etudiant['note'];
            $total['moy'] += $etudiant['note'];
        }
        
        echo <<<HTML
        <tr>
          <th scope='row'>{$etudiant['numero']}</th>
HTML;
        if(UserModel::estTuteur() || UserModel::estRespDiplome())
            echo <<<HTML
          <td><a href="javascript:setEtudiant({$etudiant['id']}, 'etudiants/ip.php', 'groupesec/etudiants.php')">{$etudiant['nom']} {$etudiant['prenom']}</a></td>
HTML;
        else
            echo <<<HTML
          <td>{$etudiant['nom']} {$etudiant['prenom']}</td>
HTML;
        echo "<td>$msg</td>";
        
        if($data['saisie']) {
            echo <<<HTML
          <td>
            <div class="form-row align-items-center">
              <div class="col-auto">
                <input class='form-control col-xs-2 form-control-sm' tabindex='$i' value='' onchange='javascript:check({$etudiant['id']})' name='note_{$etudiant['id']}'/>
              </div>
              <div class="col-auto">
                <button type='button' class='btn btn-danger btn-sm' onclick="javascript:set({$etudiant['id']}, 'ABI')">ABI</button>
                <button type='button' class='btn btn-info btn-sm' onclick="javascript:set({$etudiant['id']}, 'ABJ')">ABJ</button>
                <button type='button' class='btn btn-secondary btn-sm' onclick="javascript:set({$etudiant['id']}, 'NS')">NS</button>
                <button type='button' class='btn btn-sm' onclick="javascript:set({$etudiant['id']}, '')">Vide</button>
              </div>
            <div>
          </td>
        </tr>
HTML;
        }
    }
    
    if($data['saisie'])
        $span = 2;
    else
        $span = 1;
        
    
    // Affichage du nombre de saisies
    echo <<<HTML
    <tr>
      <th scope='row' colspan='3' class='text-right table-dark'>Notes saisies</th>
      <td colspan='$span'>{$total['nb']}</td>
    </tr>
HTML;
    
    // Affichage de la moyenne
    echo <<<HTML
    <tr>
      <th scope='row' colspan='3' class='text-right table-dark'>Moyenne</th>
HTML;
    if($total['nb'] != 0) {
        $total['moy'] /= $total['nb'];
        
        echo "<td colspan='$span'>".Note::convertirStr(round($total['moy'], 3), $total['bar'])."</td>";
    }
    else {
        echo "<td colspan='$span'>-</td>";
    }    
    echo "</tr>";
    
    // Affichage du max
    echo <<<HTML
    <tr>
      <th scope='row' colspan='3' class='text-right table-dark'>Maximum</th>
HTML;
    if($total['nb'] != 0)
        echo "<td colspan='$span'>".Note::convertirStr($total['max'], $total['bar'])."</td>";
    else
        echo "<td colspan='$span'>-</td>";
    echo "</tr>";
    
    // Affichage du min
    echo <<<HTML
    <tr>
      <th scope='row' colspan='3' class='text-right table-dark'>Minimum</th>
HTML;
    if($total['nb'] != 0)
        echo "<td colspan='$span'>".Note::convertirStr($total['min'], $total['bar'])."</td>";
    else
        echo "<td colspan='$span'>-</td>";
    
    echo "</tr>";
    
    echo "</table>";
}