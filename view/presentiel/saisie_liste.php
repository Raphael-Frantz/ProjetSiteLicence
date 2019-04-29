<?php
function displayButton($type, $idBtn = "", $tips = "", $disabled = false, $js = "") {
    $texte = "";
    switch($type) {
        case InscriptionSeanceModel::TYPE_NON_SPECIFIE:
            $couleur = "secondary";
            $texte = "N";
            break;
        case InscriptionSeanceModel::TYPE_PRESENT:
            $couleur = "success";
            $texte = "P";
            break;
        case InscriptionSeanceModel::TYPE_ABSENT:
            $couleur = "danger";
            $texte = "A";
            break;
        case InscriptionSeanceModel::TYPE_JUSTIFIE:
            $couleur = "warning";
            $texte = "J";
            break;
    }
    
    if($disabled) {
        echo "<span";
        if($idBtn != "") echo " id='$idBtn'";
        if($tips != "") echo " data-toggle='tooltip' data-placement='top' title=\"$tips\"";
        echo " class='badge badge-$couleur mr-1 mb-1 p-2'>$texte</span>";
    }
    else {
        echo "<button data-saisie='saisie'";
        if($tips != "") echo " data-toggle='tooltip' data-placement='top' title=\"$tips\"";
        if($idBtn != "") echo " id='$idBtn'";
        echo " class='btn btn-sm btn-$couleur mr-1 mb-1' value='$type'";
        if($js != "") echo " onclick='$js'";
        echo ">$texte</button>";        
    }
}

if(!$data['droits']) {
        echo <<<HTML
<div class="media alert-danger text-center" id="droits">
  <div class="media-body">
    <p class="lead mb-0" id="contenuMessage">Vous n'avez qu'un accès en consultation.</p>
  </div>
</div>
HTML;
}

if(count($data['seances']) == 0) {    
?>
<div class="media alert-danger text-center" id="message">
  <div class="media-body">
    <p class="lead mb-0" id="contenuMessage">Il n'y a aucune séance pour ce groupe.</p>
  </div>
</div>
<?php
}
else {    
    if(($data['seance'] != -1) && ($data['droits'])) {
        // Compter le nombre de présents/absents/non spécifiés pour la séance en cours
        $cpt = [InscriptionSeanceModel::TYPE_NON_SPECIFIE => 0, 
                InscriptionSeanceModel::TYPE_PRESENT => 0, 
                InscriptionSeanceModel::TYPE_ABSENT => 0,
                InscriptionSeanceModel::TYPE_JUSTIFIE => 0];
        foreach($data['presences'] as $presence) {
            if(isset($data['justificatifs'][$data['seances'][0]['id']]) &&
               array_key_exists($presence['id'], 
                                $data['justificatifs'][$data['seances'][0]['id']]) !== false) {
                    $cpt[InscriptionSeanceModel::TYPE_JUSTIFIE]++;
            }
            else {
                if(isset($presence['pres'][$data['seances'][0]['id']]))
                    $cpt[$presence['pres'][$data['seances'][0]['id']]]++;
                else
                    $cpt[InscriptionSeanceModel::TYPE_NON_SPECIFIE]++;
            }
        }
?>
<div class="container">
  <p class="lead mb-0 text-center">
    <span class="badge badge-secondary">Non spécifié(s) : <span id='nbNS'><?php echo $cpt[InscriptionSeanceModel::TYPE_NON_SPECIFIE]; ?></span></span>
    <span class="badge badge-success">Présent(s) : <span id='nbPre'><?php echo $cpt[InscriptionSeanceModel::TYPE_PRESENT]; ?></span></span>
    <span class="badge badge-danger">Absent(s) : <span id='nbAbs'><?php echo $cpt[InscriptionSeanceModel::TYPE_ABSENT]; ?></span></span>
    <span class="badge badge-warning">Justifié(s) : <span id='nbAbs'><?php echo $cpt[InscriptionSeanceModel::TYPE_JUSTIFIE]; ?></span></span>
  </p>
  <p class="lead mb-0 text-center">
    Tous non spécifés : <?php displayButton(InscriptionSeanceModel::TYPE_NON_SPECIFIE, "", "", false, "change(-1, ".InscriptionSeanceModel::TYPE_NON_SPECIFIE.", false)");?>
    Tous présents : <?php displayButton(InscriptionSeanceModel::TYPE_PRESENT, "", "", false, "change(-1, ".InscriptionSeanceModel::TYPE_PRESENT.", false)");?>
    Tous absents : <?php displayButton(InscriptionSeanceModel::TYPE_ABSENT, "", "", false, "change(-1, ".InscriptionSeanceModel::TYPE_ABSENT.", false)");?>
  </p>
</div>
<?php
    }

?>
<table class="table table-striped">
  <thead>
    <tr>
      <th scope='col'></th>
      <th scope='col'>Numéro</th>
      <th scope='col'>Etudiants</th>
<?php
    // Affichage de l'entête : soit la liste des séances, soit uniquement deux colonnes
    if($data['seance'] == -1) {
        echo "<th scope='col'>Bilan</th>";
        $i = 1;
        foreach($data['seances'] as $seance) {
            echo "<th scope='col'><span data-toggle='tooltip' data-placement='top' title='{$seance['debut']}-{$seance['fin']}'>$i</span></th>";
            $i++;
        }
    }
    else {
        echo "<th scope='col'>Actuellement</th>";
        if($data['droits']) echo "<th scope='col'>Action</th>";        
    }
?>
    </tr>
  </thead>
  <tbody>
<?php
    $i = 1;
    foreach($data['presences'] as $presence) {
        echo <<<HTML
    <tr id='lg_{$presence['id']}'>
       <td>{$i}</td>
       <td>{$presence['numero']}</td>
HTML;
        if(UserModel::estTuteur() || UserModel::estRespDiplome())
            echo <<<HTML
          <td><a href="javascript:setEtudiant({$presence['id']}, 'etudiants/ip.php', 'presentiel/saisie.php')">{$presence['nom']} {$presence['prenom']}</a>
HTML;
        else
            echo <<<HTML
          <td>{$presence['nom']} {$presence['prenom']}
HTML;
        if(!$presence['groupe']) echo " <span class='badge badge-secondary'>(ratt.)</span>";
        echo "</td>";
        $i++;
        
        // Affichage du bilan de l'étudiant
        if($data['seance'] == -1) {
            $cpt = [InscriptionSeanceModel::TYPE_NON_SPECIFIE => 0, 
                    InscriptionSeanceModel::TYPE_PRESENT => 0, 
                    InscriptionSeanceModel::TYPE_ABSENT => 0,
                    InscriptionSeanceModel::TYPE_JUSTIFIE => 0,
                    InscriptionSeanceModel::TYPE_RATTRAPAGE => 0];
            foreach($data['seances'] as $seance) {
                if(isset($data['justificatifs'][$seance['id']]) &&
                   array_key_exists($presence['id'], $data['justificatifs'][$seance['id']]) !== false) {
                    $cpt[InscriptionSeanceModel::TYPE_JUSTIFIE]++;
                }
                else {
                    if(isset($presence['pres'][$seance['id']])) {
                        $cpt[$presence['pres'][$seance['id']]]++;
                    }
                    else {
                        $cpt[InscriptionSeanceModel::TYPE_NON_SPECIFIE]++;
                    }
                }
            }
            if(isset($data['rattrapages'][$presence['id']]))
                $cpt[InscriptionSeanceModel::TYPE_RATTRAPAGE] = count($data['rattrapages'][$presence['id']]);
            
            if($presence['groupe']) {
                echo "<td>";
                echo "<span class='badge badge-secondary mr-1 mb-1 p-2'>".$cpt[InscriptionSeanceModel::TYPE_NON_SPECIFIE]."</span> / ".
                     "<span class='badge badge-success mr-1 mb-1 p-2'>".$cpt[InscriptionSeanceModel::TYPE_PRESENT]."</span> / ".
                     "<span class='badge badge-danger mr-1 mb-1 p-2'>".$cpt[InscriptionSeanceModel::TYPE_ABSENT]."</span> / ".
                     "<span class='badge badge-warning mr-1 mb-1 p-2'>".$cpt[InscriptionSeanceModel::TYPE_JUSTIFIE]."</span> / ".
                     "<span class='badge badge-primary mr-1 mb-1 p-2'>".$cpt[InscriptionSeanceModel::TYPE_RATTRAPAGE]."</span>";
                echo "</td>";
            }
            else {
                echo "<td>";
                echo "<span class='badge badge-primary mr-1 mb-1 p-2'>".$cpt[InscriptionSeanceModel::TYPE_PRESENT]."</span>";
                echo "</td>";
            }
        }
        
        $justif = false;
        foreach($data['seances'] as $seance) {
            echo "<td>";
            if(isset($data['justificatifs'][$seance['id']]) &&
               array_key_exists($presence['id'], $data['justificatifs'][$seance['id']]) !== false) {
                // Il y a un justificatif pour cet étudiant
                $justif = true;
                if($data['justificatifs'][$seance['id']][$presence['id']]['debut'] !=
                   $data['justificatifs'][$seance['id']][$presence['id']]['fin'])
                    displayButton(InscriptionSeanceModel::TYPE_JUSTIFIE, "", 
                                  $data['justificatifs'][$seance['id']][$presence['id']]['debut']."-".
                                  $data['justificatifs'][$seance['id']][$presence['id']]['fin'], true);
                else
                    displayButton(InscriptionSeanceModel::TYPE_JUSTIFIE, "", 
                                  $data['justificatifs'][$seance['id']][$presence['id']]['debut'], true);
            }
            else {
                if(isset($presence['pres'][$seance['id']])) {
                    displayButton($presence['pres'][$seance['id']], "btn_{$presence['id']}", "", true);
                }
                else {
                    displayButton(InscriptionSeanceModel::TYPE_NON_SPECIFIE, "btn_{$presence['id']}", "", true);
                }
            }
            echo "</td>";
        }   
        if(($data['seance'] != -1) && $data['droits']) {
            echo "<td>";
            if(!$justif) {
                
                if($presence['groupe']) {
                    displayButton(InscriptionSeanceModel::TYPE_NON_SPECIFIE, "", 
                              "Supprimer le présentiel de cet étudiant", false, 
                              "change({$presence['id']}, ".InscriptionSeanceModel::TYPE_NON_SPECIFIE.", false)");
                    displayButton(InscriptionSeanceModel::TYPE_PRESENT, "", 
                                  "Spécifier l'étudiant comme présent", false,
                                  "change({$presence['id']}, ".InscriptionSeanceModel::TYPE_PRESENT.", false)");
                    displayButton(InscriptionSeanceModel::TYPE_ABSENT, "",
                                  "Spécifier l'étudiant comme absent", false,
                                  "change({$presence['id']}, ".InscriptionSeanceModel::TYPE_ABSENT.", false)");
                }
                else {
                    displayButton(InscriptionSeanceModel::TYPE_NON_SPECIFIE, "", 
                              "Supprimer le présentiel de cet étudiant", false, 
                              "change({$presence['id']}, ".InscriptionSeanceModel::TYPE_NON_SPECIFIE.", true)");

                }
            }
            echo "</td>";
        }
        echo "</tr>";
    }
?>
  </tbody>
</table>
<?php
}