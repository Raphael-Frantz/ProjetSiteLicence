<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Mes notes</h2>
    <p class="lead mb-0">Retrouvez ici toutes vos notes.</p>
    
    <div class="alert alert-danger lead text-center mt-2" role="alert">
      Ces notes ne sont pas officielles. Elles sont temporaires tant que le jury de semestre n'a pas eu lieu.<br/>
      En cas de problèmes, contacter le responsable de l'EC ou du diplôme.
    </div> 

  </div>
</section>

<script>
function toggle(id) {
    if($('#btn_' + id).val() == 1) {
        $('#btn_' + id).val(0);
        $('#btn_' + id).html("<i class='icon icon-minus'>");
        
    }
    else {
        $('#btn_' + id).val(1);
        $('#btn_' + id).html("<i class='icon icon-plus'>");
    }
    $('#grp_' + id).toggle();
}
</script>

<section>
  <div class="container">
<?php
if(!isset($data['notes']) || count($data['notes']) == 0) {
    $msg = "Il n'y a pas de notes à afficher.";
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
<table class="table table-bordered table-striped mt-3">
HTML;
    $dip = "";      // Diplôme courant
    $sem = -1;      // Semestre courant
    $EC = -1;       // EC courant
    $totalCC1 = 0;  // Total CC session 1 de l'EC courant
    $totalCC1P = 0; // Total pourcentage CC session 1 de l'EC courant
    $totalEET1 = 0;  // Total EET session 1 de l'EC courant
    $totalEET1P = 0; // Total pourcentage EET session 1 de l'EC courant
    $manqueNoteCC = false; // 'true' s'il manque une note à l'EC (donc, pas de calcul)
    $manqueNoteEET = false; // 'true' s'il manque une note à l'EC (donc, pas de calcul)
    $cpt = 0;       // Compteur pour les groupes d'affichage
    $def = false;
    for($i = 0; $i < count($data['notes']); $i++) {
        $note = $data['notes'][$i];

        // Diplôme différent
        if(($dip == "") || ($note['diplome'] != $dip)) {
            echo "<tr><th colspan='2' class='text-center table-primary'>{$note['diplome']}</th></tr>";
            $dip = $note['diplome'];
            $sem = -1;
        }
        
        // Semestre différent
        if(($sem == -1) || ($sem != $note['semestre'])) {
            $tmpSem = $note['semestre'] + $note['minSemestre'] - 1;
            echo "<tr><th colspan='2' class='text-center table-success'>Semestre {$tmpSem}</th></tr>";
            $sem = $note['semestre'];
            $EC = -1;
        }
        
        // EC différent
        if($EC != $note['idEC']) {
            $EC = $note['idEC'];
            $totalCC1 = 0;
            $totalCC1P = 0;
            $totalEET1 = 0;
            $totalEET1P = 0;
            $manqueNoteCC = false;
            $manqueNoteEET = false;
            $def = false;
            echo <<<HTML
<tr>
  <th colspan='2' class='text-center table-secondary'>
    <div class='d-flex justify-content-between'>
      <span></span>
      <span>({$note['code']}) {$note['intitule']}</span>
      <span>
        <button value='1' id='btn_{$cpt}' data-toggle='tooltip' data-placement='top' 
                title='Afficher/masquer le détail' class='btn btn-sm btn-outline-success'
                onclick="toggle({$cpt});">
          <i class='icon icon-plus'></i>
        </button>
      </span>
    </div>
  </th>
</tr>
<tbody id='grp_{$cpt}' style='display: none;'>
  <tr>
    <th scope='col'>Epreuve(s)</th><th scope='col'>Note(s) (session 1)</th>
  </tr>
HTML;
            $cpt++;
        }
        
        // Est-ce que la note compte pour la première session ?
        if($note['session1'] > 0) {
            // Compte pour le semestre 1
            if(($note['note'] !== null) && ($note['visible'] == 1)) {
                if(Epreuve::estCC($note['typeE'])) {
                    // Note de CC
                    if(($note['note'] != Note::TYPE_ABI) && ($note['note'] != Note::TYPE_ABJ))
                        $totalCC1 += ($note['note']/$note['max'])*$note['session1'];
                    $totalCC1P += $note['session1'];
                }
                else {
                    // Note d'EET
                    if($note['note'] == Note::TYPE_ABI)
                        $def = true;
                    else
                        if($note['note'] != Note::TYPE_ABJ)
                            $totalEET1 += ($note['note']/$note['max'])*$note['session1'];
                    $totalEET1P += $note['session1'];
                }
            }
            else {
                if(Epreuve::estCC($note['typeE']))
                    $manqueNoteCC = true;
                else
                    $manqueNoteEET = true;
            }
        }
        
        // Affichage de la note
        if(($note['session1'] == 0) && ($note['session2'] >= 0)) {
            // C'est une note de deuxième session uniquement
        }
        else {
            if($note['type'] == InscriptionECModel::TYPE_VALIDE) {
                $msg = "<span class='badge badge-secondary'>{$note['noteip']}/{$note['bareme']}</span>";
            }
            else {
                if(($note['visible'] == 1) && ($note['note'] !== null)) {
                    $msg = Note::convertirStr($note['note'], $note['max']);
                }
                else {
                    $msg = "-";
                }
            }
        }
        
        // Affichage de la ligne
        // #TODO# : ici, affichage de la première session uniquement !
        if($note['type'] != InscriptionECModel::TYPE_VALIDE) {        
            if(($note['session1'] != 0) || (($note['session1'] == 0) && ($note['session2'] == 0))) {
                echo "<tr>";
                echo "<td>{$note['epreuve']}</td>";
                
                if(($note['session1'] == 0) && ($note['session2'] == 0)) {
                    // Répartition non spécifiée dans l'application
                    echo "<td>$msg</td>";
                }
                else {
                    if($note['session1'] != 0)
                        echo "<td>$msg ({$note['session1']}%)</td>";
                    else
                        echo "<td></td>";
                }
                echo "</tr>\n";
            }
        }
        
        // Affichage du total de l'EC
        if(($i == count($data['notes']) - 1) || ($data['notes'][$i+1]['idEC'] != $EC)) {
            echo "</tbody>";
            $totalEC = 0;
            if(($totalCC1P != 0) && !$manqueNoteCC) {
                $totalEC += $totalCC1;
                if($totalCC1P != 100) {
                    echo "<tr>";
                    echo "<th class='table-secondary'>Total CC</th>";
                    echo "<td>".Note::convertirStr(round($totalCC1, 3), $totalCC1P).
                         " (".Note::convertirStr(round($totalCC1/$totalCC1P*20, 3), 20).")</td>";
                    echo "<tr>";
                }
            }
            if(($totalEET1P != 0) && !$manqueNoteEET) {
                $totalEC += $totalEET1;
                if($totalEET1P != 100) {
                    echo "<tr>";
                    echo "<th class='table-secondary'>Total EET</th>";
                    echo "<td>".Note::convertirStr(round($totalEET1, 3), $totalEET1P).
                         " (".Note::convertirStr(round($totalEET1/$totalEET1P*20,3), 20).")</td>";
                    echo "<tr>";
                }
            }
            if($note['type'] == InscriptionECModel::TYPE_VALIDE) {
                echo "<tr>";
                echo "<th class='table-secondary'>Total EC</th>";
                echo "<td><span class='badge badge-secondary'>{$note['noteip']}/{$note['bareme']}</span> (validation)</td>";
                echo "<tr>";
            }
            else {
                if(($totalCC1P + $totalEET1P == 100) && !$manqueNoteCC && !$manqueNoteEET) {
                    echo "<tr>";
                    echo "<th class='table-secondary'>Total EC</th>";
                    echo "<td>".Note::convertirStr(round($totalEC, 3), 100).
                         " (".Note::convertirStr(round($totalEC/5, 3), 20).")</td>";
                    echo "<tr>";
                }
            }
        }
    }
    echo "</table>";
}
?>
  </div>
</section>