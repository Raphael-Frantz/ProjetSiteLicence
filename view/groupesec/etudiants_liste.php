<?php
// @need etudiants la liste des étudiants
$estResp = isset($_SESSION['current']['EC']) && 
           (UserModel::estRespEC($_SESSION['current']['EC']) ||
            RespDiplomeModel::estResponsableDiplomeEC(UserModel::getId(), $_SESSION['current']['EC']));

if(isset($data['groupesCM']) || isset($data['groupesTD']) || isset($data['groupesTP']))
    $mode = 1;
else
    $mode = 2;
    
if(count($data['etudiants']) == 0) {
    $msg = "Il n'y a aucun étudiant inscrit dans ce groupe.";
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
      <th scope="col">Email</th>
HTML;
    if($mode == 1) {
        if(isset($data['groupesCM'])) echo "<th scope=\"col\">Groupe CM</th>";
        if(isset($data['groupesTD'])) echo "<th scope=\"col\">Groupe TD</th>";
        if(isset($data['groupesTP'])) echo "<th scope=\"col\">Groupe TP</th>";
    }
    echo <<<HTML
      <th class="text-right" scope="col">Actions</th>
    </tr>
  </thead>
HTML;
    foreach($data['etudiants'] as $etudiant) {
        echo <<<HTML
        <tr id='ligne{$etudiant['id']}'>
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
        echo <<<HTML
          <td data-email='email'>{$etudiant['email']}</td>
HTML;
        if($mode == 1) {
            if(count($data['groupesCM']) == 0) {
                echo "<td>Pas de groupe</td>";
            }
            else {
                echo "<td>";
                if(isset($_SESSION['current']['EC']) && $estResp) {
                    echo "<select id='CM_{$etudiant['id']}' onchange='inscriptionGroupe({$etudiant['id']}, 1)'>";
                    if($etudiant['groupeCM'] == null)
                        echo "<option value='-1' selected>Aucun</option>";
                    else
                        echo "<option value='-1'>Aucun</option>";
                    foreach($data['groupesCM'] as $groupeCM) {
                        if($etudiant['groupeCM'] == $groupeCM['id'])
                            echo "<option value='{$groupeCM['id']}' selected>{$groupeCM['intitule']}</option>";
                        else
                            echo "<option value='{$groupeCM['id']}'>{$groupeCM['intitule']}</option>";
                    }
                    echo "</select>";
                }
                else {
                    $tmp = "Aucun";
                    foreach($data['groupesCM'] as $groupe) {
                        if($groupe['id'] == $etudiant['groupeCM'])
                            $tmp = $groupe['intitule'];
                    }
                    echo $tmp;
                }
                echo "</td>";
            }
            if(count($data['groupesTD']) == 0) {
                echo "<td>Pas de groupe</td>";
            }
            else {
                echo "<td>";
                if(isset($_SESSION['current']['EC']) && $estResp) {
                    echo "<select id='TD_{$etudiant['id']}' onchange='inscriptionGroupe({$etudiant['id']}, 2)'>";
                    if($etudiant['groupeTD'] == null)
                        echo "<option value='-1' selected>Aucun</option>";
                    else
                        echo "<option value='-1'>Aucun</option>";
                    foreach($data['groupesTD'] as $groupeTD) {
                        if($etudiant['groupeTD'] == $groupeTD['id'])
                            echo "<option value='{$groupeTD['id']}' selected>{$groupeTD['intitule']}</option>";
                        else
                            echo "<option value='{$groupeTD['id']}'>{$groupeTD['intitule']}</option>";
                    }
                    echo "</select>";
                }
                else {
                    $tmp = "Aucun";
                    foreach($data['groupesTD'] as $groupe) {
                        if($groupe['id'] == $etudiant['groupeTD'])
                            $tmp = $groupe['intitule'];
                    }
                    echo $tmp;
                }
                echo "</td>";
            }
            if(count($data['groupesTP']) == 0) {
                echo "<td>Pas de groupe</td>";
            }
            else {
                echo "<td>";
                if(isset($_SESSION['current']['EC']) && $estResp) {
                    echo "<select id='TP_{$etudiant['id']}' onchange='inscriptionGroupe({$etudiant['id']}, 3)'>";
                    if($etudiant['groupeTP'] == null)
                        echo "<option value='-1' selected>Aucun</option>";
                    else
                        echo "<option value='-1'>Aucun</option>";
                    foreach($data['groupesTP'] as $groupeTP) {
                        if($etudiant['groupeTP'] == $groupeTP['id'])
                            echo "<option value='{$groupeTP['id']}' selected>{$groupeTP['intitule']}</option>";
                        else
                            echo "<option value='{$groupeTP['id']}'>{$groupeTP['intitule']}</option>";
                    }
                    echo "</select>";
                }
                else {
                    $tmp = "Aucun";
                    foreach($data['groupesTP'] as $groupe) {
                        if($groupe['id'] == $etudiant['groupeTP'])
                            $tmp = $groupe['intitule'];
                    }
                    echo $tmp;
                }
                echo "</td>";
            }
            
        }
        echo "<td class='text-right'>";
        
        if($estResp && ($mode != 1)) {
            echo <<<HTML
            <a class='btn btn-sm btn-outline-danger mr-2' href='javascript:desinscription({$etudiant['id']})' data-toggle="tooltip" data-placement="top" title="Désinscrire l'étudiant">
              <i class="icon-minus"></i>
            </a>
HTML;
        }
        echo <<<HTML
            <a class='btn btn-sm btn-outline-primary mr-2' href='mailto:{$etudiant['email']}' data-toggle="tooltip" data-placement="top" title="Envoyer un mail">
              <i class="icon-envelope"></i>
            </a>
          </td>
        </tr>
HTML;
    }
    echo "</table>";
}