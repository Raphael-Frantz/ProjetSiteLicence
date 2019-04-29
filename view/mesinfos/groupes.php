<?php
function afficherGroupe(string $titre, array $tableau) : void {
    $long = count($tableau);
    echo <<<HTML
        <tr>
          <td rowspan="$long">$titre</td>
          <td>{$tableau[0][0]} ({$tableau[0][1]})</td>
        </tr>
HTML;
    for($i = 1; $i < count($tableau); $i++) {
        echo <<<HTML
        <tr>
          <td>{$tableau[$i][0]} ({$tableau[$i][1]})</td>
        </tr>
HTML;
    }
}
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Mes groupes</h2>
    <p class="lead mb-0">Retrouvez ici vos groupes dans le diplôme et dans chaque EC.</p>
  </div>
</section>

<section>
  <div class="container">
    <h3 class="mb-4">Mes groupes dans mes diplômes</h3>
    
<?php
if(count($data['groupes']) == 0) {
    echo <<<HTML
    <div class='card mb-4 border-primary box-shadow'>
      <div class='card-body'>
        <p class='lead mb-0'>Vous êtes inscrit(e) dans aucun groupe. Contactez le responsable de votre/vos formation(s).</p>
      </div>
    </div>
HTML;
}
else {
   echo <<<HTML
    <p class="lead mb-2">
        Voici la liste des groupes dans lesquels vous êtes inscrit(e)s :
    </p>   
<table class="table table-striped table-bordered" id="tableGroupes">
HTML;
    $diplome = "";
    $semestre = "";
    $tmp = [];
    foreach($data['groupes'] as $groupe) {
        if($diplome != $groupe['diplome']) {
            if(count($tmp) > 0) { afficherGroupe("Semestre $semestre", $tmp); $tmp = []; }
            $diplome = $groupe['diplome'];
            $semestre = $groupe['semestre'];
            echo <<<HTML
  <thead>
    <tr>
      <th scope="col" colspan="2" class="text-center table-primary">$diplome</th>
    </tr>
    <tr class="table-secondary">
      <th scope="col">Semestre</th>
      <th scope="col">Groupe</th>
    </tr>
  </thead>
HTML;
        }        
        if($semestre != $groupe['semestre']) {
            afficherGroupe("Semestre $semestre", $tmp);
            $tmp = [];
            $semestre = $groupe['semestre'];
        }
        
        $tmp[] = [ $groupe['intitule'], Groupe::type2String($groupe['type']) ];
        $type = Groupe::type2String($groupe['type']);
    }
    if(count($tmp) > 0) afficherGroupe("Semestre $semestre", $tmp);
    echo "</table>";
}
?>
  </div>
</section>

<section class="bg-light">
  <div class="container">
    <h3 class="mb-4">Mes groupes dans les EC</h3>
    
<?php
if(count($data['groupesEC']) == 0) {
    echo <<<HTML
    <div class='card mb-4 border-primary box-shadow'>
      <div class='card-body'>
        <p class='lead mb-0'>Vous êtes inscrit(e) dans aucun groupe. Contactez le responsable de votre/vos formation(s).</p>
      </div>
    </div>
HTML;
}
else {    
    echo <<<HTML
    <p class="lead mb-2">
        Voici la liste des groupes d'EC dans lesquels vous êtes inscrit(e)s :
    </p>   
<table class="table table-striped table-bordered mb-0" id="tableGroupes">
HTML;

    $i = 0;
    while($i < count($data['groupesEC'])) {
        echo <<<HTML
  <thead>
    <tr>
      <th scope="col" colspan="2" class="text-center table-primary">{$data['groupesEC'][$i]['diplome']}</th>
    </tr>
  </thead>
HTML;
        $s = $i;
        while(($s < count($data['groupesEC'])) &&
              ($data['groupesEC'][$s]['diplome'] == $data['groupesEC'][$i]['diplome'])) {
            $tmpSem = $data['groupesEC'][$s]['semestre'] + $data['groupesEC'][$s]['minSemestre'] - 1;
            echo <<<HTML
    <tr class="table-secondary">
      <th scope="col">Semestre {$tmpSem}</th>
      <th scope="col">Groupe</th>
    </tr>
  </thead>
HTML;
            $j = $s;
            while(($j < count($data['groupesEC'])) && 
                  ($data['groupesEC'][$j]['semestre'] == $data['groupesEC'][$s]['semestre']) &&
                  ($data['groupesEC'][$j]['diplome'] == $data['groupesEC'][$s]['diplome'])) {
                // On cherche les groupes de l'EC
                $k = $j;
                while(($k < count($data['groupesEC'])) && 
                      ($data['groupesEC'][$k]['intitule'] == $data['groupesEC'][$j]['intitule']) &&
                      ($data['groupesEC'][$k]['diplome'] == $data['groupesEC'][$j]['diplome'])) $k++;
                
                $type = Groupe::type2String($data['groupesEC'][$j]['type']);
                $nbGrp = $k - $j;
                echo <<<HTML
        <tr>
          <td rowspan="$nbGrp">{$data['groupesEC'][$j]['intitule']}</td>
          <td>{$data['groupesEC'][$j]['groupe']} ({$type})</td>
        </tr>
HTML;
                $j++;
                while($j < $k) {
                    $type = Groupe::type2String($data['groupesEC'][$j]['type']);
                    echo <<<HTML
        <tr>
          <td>{$data['groupesEC'][$j]['groupe']} ({$type})</td>
        </tr>
HTML;
                    $j++;
                }
            }
            $s = $j;
        }
        $i = $s;
    }
    echo "</table>";
}
?>
  </div>
</section>