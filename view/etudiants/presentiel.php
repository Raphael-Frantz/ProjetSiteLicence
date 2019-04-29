<?php
WebPage::addJSScript("public/js/active-tooltips.js");

/**
 * Vérifie si la séance correspond à un justificatif.
 * @param debut le début de la séance
 * @param fin la fin de la séance
 * @param justifs les justificatifs
 * @return 'true' si c'est une absence justifiée, 'false' sinon
 */
function estJustifie($debut, $fin, &$justifs) : bool {
    global $data;
        
    $i = 0;
    $trouve = false;
    while(($i < count($justifs)) && !$trouve) {
        $trouve = (($debut >= $justifs[$i]['debut']) &&
                   ($fin <= $justifs[$i]['fin']));
        $i++;
    }
    return $trouve;
}
?>

<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Présentiel</h2>
    <p class="lead mb-0">
      Retrouvez ici les justificatifs d'absence et le présentiel pour tous les EC de l'étudiant(e) <span class='badge badge-primary'><?php echo $data['etudiant']; ?></span>.
    </p>
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
  
  <form class="form-inline justify-content-between mt-2 mb-2">
    <div>
      <a data-toggle='tooltip' data-placement='top' title="IP de l'étudiant(e)s" class="btn btn-outline-primary mr-2" href="<?php echo WEB_PATH; ?>etudiants/ip.php">IP</a>
      <a data-toggle='tooltip' data-placement='top' title="Notes de l'étudiant(e)s" class="btn btn-outline-primary mr-2" href="<?php echo WEB_PATH; ?>etudiants/notes.php">Notes</a>
      <a data-toggle='tooltip' data-placement='top' title="Groupes de l'étudiant(e)s" class="btn btn-outline-primary mr-2" href="<?php echo WEB_PATH; ?>etudiants/groupes.php">Groupes</a>    
    </div>
<?php
if(isset($data['back']) && ($data['back'] != "")) {
    $path = WEB_PATH.$data['back'];
    echo <<<HTML
    <div>
      <a data-toggle='tooltip' data-placement='top' title="Retour" class="btn btn-outline-primary mr-2" href="$path">Retour</a>
    </div>
HTML;
}
?>    
  </form> 
  
    <h3 class="mb-4">Justificatifs d'absence</h3>

<?php    
if(!isset($data['justificatifs']) || (count($data['justificatifs']) == 0)) {
    echo <<<HTML
    <div class='card mb-4 border-primary box-shadow'>
      <div class='card-body'>
        <p class='lead mb-0'>Il n'y a aucun justificatif enregistré.</p>
      </div>
    </div>
HTML;
}
else {
    echo <<<HTML
    <p class="lead mb-2">
        Voici la liste des justificatifs d'absence enregistrés :
    </p>    
<table class="table table-striped table-bordered" id="tableEtudiants">
  <thead>
    <tr>
      <th scope="col">Motif</th>
      <th scope="col">Date</th>
    </tr>
  </thead>
HTML;
    foreach($data['justificatifs'] as &$justificatif) {
        $justificatif['heure'] = (date('G', $justificatif['debut']) != 0);
        
        // Pour les vérifications dans la base, il faut faire en sorte que la date de fin soit +1 jour si justif sur une journée
        if(!$justificatif['heure'])
            $fin = strtotime('-1 day', $justificatif['fin']);
        else
            $fin = $justificatif['fin'];
        
        if($justificatif['debut'] != $fin)
            $date = "Du ".DateTools::timestamp2Date($justificatif['debut'], $justificatif['heure']).
                    " au ".DateTools::timestamp2Date($fin, $justificatif['heure']);
        else
            $date = "Le ".DateTools::timestamp2Date($justificatif['debut'], $justificatif['heure']);
        
        echo <<<HTML
        <tr>
          <td>{$justificatif['motif']}</td>
          <td>{$date}</td>
        </tr>
HTML;
    }
    echo "</table>";
}
?>
  </div>
</section>

<section class="bg-light">
  <div class="container mb-0">
    
    <h3 class="mb-4">Présentiel</h3>

<?php
if(count($data['presentiel']) <= 0) {
    echo <<<HTML
        <div class='card mb-4 border-primary box-shadow'>
      <div class='card-body'>
        <p class='lead mb-0'>Aucun présentiel disponible.</p>
      </div>
    </div>
HTML;
}
else {
    echo <<<HTML
    <p class="lead mb-2">
        Voici le détail de votre présentiel (cliquez sur <i class='icon icon-plus'></i> pour afficher le détail par séance) :
    </p>   
    <table class="table table-striped table-bordered mb-0" id="tableGroupes">
HTML;
    foreach($data['presentiel'] as $EC) {
        echo <<<HTML
      <thead>
        <tr>
          <th scope="col" colspan="3" class="text-center table-primary">{$EC['code']} - {$EC['intitule']}</th>
        </tr>
      </thead>
HTML;
        foreach($EC['pres'] as $id => $groupe) {
            $typeGrp = Groupe::type2String($groupe['type']);
            $cptGroupe = [InscriptionSeanceModel::TYPE_NON_SPECIFIE => 0,
                          InscriptionSeanceModel::TYPE_PRESENT => 0,
                          InscriptionSeanceModel::TYPE_ABSENT => 0,
                          InscriptionSeanceModel::TYPE_JUSTIFIE => 0,
                          InscriptionSeanceModel::TYPE_RATTRAPAGE => 0 ];
            echo <<<HTML
      <thead>
        <tr>
          <th scope="col" colspan="3" class="text-center table-success">
            {$groupe['groupe']} ({$typeGrp})
            <button value='1' id='btn_{$id}' data-toggle='tooltip' data-placement='top' title='Afficher/masquer le détail' class='btn btn-sm btn-outline-success' onclick="toggle({$id});"><i class='icon icon-plus'></i>
          </th>
        </tr>
      </thead>
      <tbody id='grp_{$id}' style='display: none;'>
HTML;
            $numSeance = 0;
            foreach($groupe['seances'] as $seance) {
                $numSeance++;
                $debut = DateTools::timestamp2Date($seance['debut'], true);
                $fin = DateTools::timestamp2Date($seance['fin'], true);
                
                // Vérifier les justificatifs
                if(estJustifie($seance['debut'], $seance['fin'], $data['justificatifs'])) {
                    $type = "<span class='badge badge-warning'>justifié</span>";
                    $cptGroupe[InscriptionSeanceModel::TYPE_JUSTIFIE]++;
                }
                else {
                    if($seance['type'] === null) {
                        $type = "<span class='badge badge-secondary'>non spécifié</span>";
                        $cptGroupe[InscriptionSeanceModel::TYPE_NON_SPECIFIE]++;
                    }
                    else {
                        $cptGroupe[$seance['type']]++;
                        if($seance['type'] == InscriptionSeanceModel::TYPE_PRESENT)
                            $type = "<span class='badge badge-success'>présent</span>";
                        else
                            $type = "<span class='badge badge-danger'>absent</span>";
                    }
                }
                echo <<<HTML
        <tr>
          <td>Seance {$numSeance}</td>
          <td>$debut - $fin</td>
          <td>$type</td>
        </tr>
HTML;
            }
            
            // Rattrapages
            if(isset($EC['ratt'][$groupe['type']])) {
                foreach($EC['ratt'][$groupe['type']] as $seance) {
                    $debut = DateTools::timestamp2Date($seance['debut'], true);
                    $fin = DateTools::timestamp2Date($seance['fin'], true);
                    echo <<<HTML
        <tr>
          <td>{$seance['intitule']}</td>
          <td>$debut - $fin</td>
          <td><span class='badge badge-primary'>rattrapage</span></td>
        </tr>
HTML;
                    $cptGroupe[InscriptionSeanceModel::TYPE_RATTRAPAGE]++;
                }
            }
            
            echo <<<HTML
      </tbody>
      <tr>
        <th scope="col" colspan="3" class="text-center table-success">
          Sur $numSeance séance(s) : 
HTML;
            if($cptGroupe[InscriptionSeanceModel::TYPE_NON_SPECIFIE] != 0)
                echo "<span class='badge badge-secondary mr-1'>Non spécifié : {$cptGroupe[InscriptionSeanceModel::TYPE_NON_SPECIFIE]}</span>";
            if($cptGroupe[InscriptionSeanceModel::TYPE_PRESENT] != 0)
                echo "<span class='badge badge-success mr-1'>Présent : {$cptGroupe[InscriptionSeanceModel::TYPE_PRESENT]}</span>";
            if($cptGroupe[InscriptionSeanceModel::TYPE_ABSENT] != 0)
                echo "<span class='badge badge-danger mr-1'>Absent : {$cptGroupe[InscriptionSeanceModel::TYPE_ABSENT]}</span>";
            if($cptGroupe[InscriptionSeanceModel::TYPE_JUSTIFIE] != 0)
                echo "<span class='badge badge-warning mr-1'>Justifié : {$cptGroupe[InscriptionSeanceModel::TYPE_JUSTIFIE]}</span>";
            if($cptGroupe[InscriptionSeanceModel::TYPE_RATTRAPAGE] != 0)
                echo "<span class='badge badge-primary mr-1'>Rattrapage : {$cptGroupe[InscriptionSeanceModel::TYPE_RATTRAPAGE]}</span>";
            echo <<<HTML
        </th>
      </tr>
HTML;
        }
    }
    echo "    </table>";
}
?>
  </div>
</section>