<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Mes IPs</h2>
    <p class="lead mb-0">Retrouvez ici toutes vos IPs.</p>
    <div class="alert alert-danger lead text-center mt-2" role="alert">
      En cas de différences, merci de contacter le responsable de la formation.
    </div> 
  </div>
</section>

<section>
  <div class="container">
<?php 
WebPage::addJSScript("public/js/active-tooltips.js");
$i = 0;
while($i < count($data['IPs'])) {
    echo "<h3 class='mb-2'>{$data['IPs'][$i]['diplome']}</h3>";
    echo <<<HTML
<table class="table table-striped table-bordered">
  <thead>
    <tr>
      <th scope="col">Semestre</th>
      <th scope="col">EC</th>
      <th scope="col">IP</th>
    </tr>
  </thead>
  <tbody>
HTML;

    $j = $i;
    while(($j < count($data['IPs'])) && ($data['IPs'][$j]['diplome'] == $data['IPs'][$i]['diplome'])) {
        // Recherche du prochain indice pour changer de diplôme/semestre
        $k = $j;
        while(($k < count($data['IPs'])) && 
              ($data['IPs'][$k]['diplome'] == $data['IPs'][$j]['diplome']) &&
              ($data['IPs'][$k]['semestre'] == $data['IPs'][$j]['semestre']))
            $k++;

        // Affichage de toutes les lignes du semestre
        $first = true;
        do {
            // Affichage du semestre et de l'EC
            if($first) {
                $first = false;
                $tmpSem = $data['IPs'][$j]['semestre'] + $data['IPs'][$j]['minSemestre'] - 1;
                echo "<tr><td rowspan='".($k - $j)."'>Semestre {$tmpSem}</td>";
                echo "<td>{$data['IPs'][$j]['code']} - {$data['IPs'][$j]['intitule']}</td>";
            }
            else
                echo "<tr><td>{$data['IPs'][$j]['code']} - {$data['IPs'][$j]['intitule']}</td>";
            
            // Affichage de l'inscription
            echo "<td>";
            switch($data['IPs'][$j]['type']) {
                case InscriptionECModel::TYPE_INSCRIT:
                    echo "X";
                    break;
                case InscriptionECModel::TYPE_VALIDE:
                    echo $data['IPs'][$j]['note'];
                    break;
            }
            echo "</td></tr>";
            $j++;
        } while($j < $k);
    }
    $i = $j;
    echo "</table>";
}
?>
  </div>
</section>