<?php
// @need structure la structure d'un diplôme (UE/EC)

if(!isset($data['structure']['semestre']))
    $sem = 0;
else
    $sem = $data['structure']['semestre'];

if ($sem == 0){
  if(!isset($data['structure']['semestre']))
      $sem = 1;
  else
      $sem = $data['structure']['semestre'];

?>

  <table class="table table-bordered">
    <thead class="thead-dark">
      <tr>
        <th scope="col"></th>
<?php

  for($numUE = 1; $numUE <= 5 ; $numUE++) {
    $max = 1;
    for($semestre = 1; $semestre <= count($data['structure']); $semestre++) {
        if (isset($data['structure'][$semestre][$numUE]['EC'])){
          if ($max < count($data['structure'][$semestre][$numUE]['EC'])){
            $max *= count($data['structure'][$semestre][$numUE]['EC']);
          }
        }
      }
      echo <<<HTML
        <th scope="col" colspan = "$max">UE $numUE</th>
HTML;
    }
 ?>
    </tr>
  </thead>
<tbody>

<?php
    for($sem = 1; $sem <= count($data['structure']); $sem++) {
        echo <<<HTML
            <tr>
                <th scope="row">
                    <span>Semestre $sem</span>
                </th>
HTML;
        if(isset($data['structure'][$sem])) {
            for($numUE = 1; $numUE <= count($data['structure'][$sem]); $numUE++) {
                $ue = $data['structure'][$sem][$numUE];
                    if($numUE < count($data['structure'][$sem])){
                        echo <<<HTML
                            </td>
HTML;
                    }

                    $max = 1;
                    for($semestre = 1; $semestre <= count($data['structure']); $semestre++) {
                        if (isset($data['structure'][$semestre][$numUE]['EC'])){
                          if ($max < count($data['structure'][$semestre][$numUE]['EC'])){
                            $max *= count($data['structure'][$semestre][$numUE]['EC']);
                          }
                        }
                      }


                    for($numEC = 0; $numEC < count($data['structure'][$sem][$numUE]['EC']); $numEC++) {
                        $ec = $data['structure'][$sem][$numUE]['EC'][$numEC];
                        $count = count($data['structure'][$sem][$numUE]['EC']);
                        $t = $max / $count;
                        echo <<<HTML
                            <td colspan="$t">
                                <span id='EC_{$ec['id']}'>{$ec['code']} - {$ec['intitule']}</span>
HTML;
                        if($numEC < count($data['structure'][$sem][$numUE]['EC']) - 1){
                            echo <<<HTML
                                </td>
HTML;
                        }
                    }
              }
        }
    }
?>
  </tbody>
</table>



<table class="table table-bordered">
  <thead class="thead-dark">
    <tr>
      <th scope="col">UE</th>
      <th scope="col" colspan="2">EC</th>
      <th scope="col" colspan="3">Session 1</th>
      <th scope="col" colspan="3">Session 2</th>
    </tr>
  </thead>
  <tbody>
    <?php
      $annee = 0;
      for($semestre = 1; $semestre <= count($data['structure']); $semestre++) {
        if ($semestre % 2 != 0) $annee++;
        echo <<<HTML
        <tr>
            <th scope="col" colspan="9">Année $annee</th>
        </tr>
        <tr>
            <th scope="col" colspan="9">Semestre $semestre</th>
        </tr>
HTML;
      for($numUE = 1; $numUE <= 5 ; $numUE++) {
          $max = 1;
          for($sem = 1; $sem <= count($data['structure']); $sem++){
              if (isset($data['structure'][$sem][$numUE]['EC'])){
                if ($max < count($data['structure'][$sem][$numUE]['EC'])){
                  $max *= count($data['structure'][$sem][$numUE]['EC']);
                }
              }
            }
        if(isset($data['structure'][$semestre][$numUE]['EC'])){
          $count = count($data['structure'][$semestre][$numUE]['EC']);
        }
        echo <<<HTML
            <tr>
                <th scope="row" rowspan="$count">
                    <span>$numUE</span>
                </th>
HTML;
      if (isset($data['structure'][$semestre][$numUE])){
      $ue = $data['structure'][$semestre][$numUE];
    }
    if (isset($data['structure'][$semestre][$numUE]['EC'])){
      for ($numEC = 0 ; $numEC < count($data['structure'][$semestre][$numUE]['EC']) ; $numEC++){
        $ec = $data['structure'][$semestre][$numUE]['EC'][$numEC];
        $count = count($data['structure'][$semestre][$numUE]['EC']);
        $t = $max / $count;
        echo <<<HTML
          <td>
            <span id='EC_{$ec['id']}'>{$ec['code']}</span>
          </td>
          <td>
            <span id='EC_{$ec['id']}'>{$ec['intitule']}</span>
          </td>
          <td>
            <span id='EPREUVE'>
HTML;

          foreach (EpreuveModel::getList($ec['id']) as $epreuve){
            if (($epreuve['session1'] != 0) || ($epreuve['session1disp'] != 0)){
            echo <<<HTML
              {$epreuve['intitule']} +

HTML;
}
}
            echo <<<HTML
              </span>
            </td>
            <td>
              <span id='EPREUVE'>
HTML;
          foreach (EpreuveModel::getList($ec['id']) as $epreuve){
            if ($epreuve['session1'] != 0){
            echo <<<HTML
            {$epreuve['session1']} +
HTML;
          }
        }
          echo <<<HTML
          </span>
            </td>
            <td>
              <span id='EPREUVE'>
HTML;
        foreach (EpreuveModel::getList($ec['id']) as $epreuve){
          if ($epreuve['session1disp'] != 0){
          echo <<<HTML
          {$epreuve['session1disp']} +
HTML;
        }
      }
          echo <<<HTML
          </span>
            </td>
            <td>
              <span id='EPREUVE'>
HTML;
          foreach (EpreuveModel::getList($ec['id']) as $epreuve){
            if (($epreuve['session2'] != 0) || ($epreuve['session2disp'] != 0)){
            echo <<<HTML
              {$epreuve['intitule']} +
HTML;
          }
          }
            echo <<<HTML
              </span>
            </td>
            <td>
              <span id='EPREUVE'>
HTML;

          foreach (EpreuveModel::getList($ec['id']) as $epreuve){
            if ($epreuve['session2'] != 0){
            echo <<<HTML
            {$epreuve['session2']} +
HTML;
          }
        }
            echo <<<HTML
            </span>
            </td>
            <td>
              <span id='EPREUVE'>
HTML;
          foreach (EpreuveModel::getList($ec['id']) as $epreuve){
            if ($epreuve['session2disp'] != 0){
            echo <<<HTML
            {$epreuve['session2disp']} +
HTML;
          }
        }
          echo <<<HTML
          </span>
            </td>
HTML;
      if ($numEC < count($data['structure'][$semestre][$numUE]['EC']) - 1){
        echo <<<HTML
      </td>
    </tr>
HTML;
}
    }
  }
}
}

     ?>
  </tbody>
</table>

<?php
}
// @need structure la structure d'un diplôme (UE/EC)

if(!isset($data['structure']['semestre']))
    $sem = 0;
else
    $sem = $data['structure']['semestre'];

if ($sem != 0){

?>


    <ul class="list-group">
<?php
    echo <<<HTML
        <li class="list-group-item list-group-item-dark">
          <div class="d-flex justify-content-between">
            <span>Semestre $sem</span>
            <span><a class='btn btn-sm btn-outline-primary' href='javascript:ajouterUE($sem);' data-toggle="tooltip" data-placement="top" title="Ajouter une UE"><i class="icon-plus"></i></a></span>
          </div>
        </li>
HTML;
    if(isset($data['structure'][$sem])) {
        for($numUE = 1; $numUE <= count($data['structure'][$sem]); $numUE++) {
            $ue = $data['structure'][$sem][$numUE];
            echo <<<HTML
                    <li class="list-group-item list-group-item-secondary">
                      <div class="d-flex justify-content-between">
                        <span>UE $numUE</span>
                        <span>
HTML;
            if($numUE != 1)
                echo <<<HTML
                          <a class='btn btn-sm btn-outline-primary' href='javascript:monterUE({$ue['id']});' data-toggle="tooltip" data-placement="top" title="Monter l'UE"><i class="icon-arrow-up-circle"></i></a>
HTML;
            if($numUE < count($data['structure'][$sem]))
                echo <<<HTML
                          <a class='btn btn-sm btn-outline-primary' href='javascript:descendreUE({$ue['id']});' data-toggle="tooltip" data-placement="top" title="Descendre l'UE"><i class="icon-arrow-down-circle"></i></a>
HTML;
            echo <<<HTML
                          <a class='btn btn-sm btn-outline-primary' href='javascript:ajouterEC({$ue['id']});' data-toggle="tooltip" data-placement="top" title="Ajouter un EC"><i class="icon-plus"></i></a>
                          <a class='btn btn-sm btn-outline-danger' href='javascript:supprimerUE($numUE, {$ue['id']});' data-toggle="tooltip" data-placement="top" title="Supprimer l'UE"><i class="icon-trash"></i></a>
                        </span>
                      </div>
                    </li>
HTML;
            for($numEC = 0; $numEC < count($data['structure'][$sem][$numUE]['EC']); $numEC++) {
                $ec = $data['structure'][$sem][$numUE]['EC'][$numEC];
                echo <<<HTML
                    <li class="list-group-item">
                      <div class="d-flex justify-content-between">
                        <span id='EC_{$ec['id']}'>{$ec['code']} - {$ec['intitule']}</span>
                        <span>
HTML;
                if($numEC != 0)
                    echo <<<HTML
                          <a class='btn btn-sm btn-outline-primary' href='javascript:monterEC({$ue['id']},{$ec['id']});' data-toggle="tooltip" data-placement="top" title="Monter l'EC"><i class="icon-arrow-up-circle"></i></a>
HTML;
                if($numEC < count($data['structure'][$sem][$numUE]['EC']) - 1)
                    echo <<<HTML
                          <a class='btn btn-sm btn-outline-primary' href='javascript:descendreEC({$ue['id']},{$ec['id']});' data-toggle="tooltip" data-placement="top" title="Descendre l'EC"><i class="icon-arrow-down-circle"></i></a>
HTML;
                echo <<<HTML
                          <a class='btn btn-sm btn-outline-danger' href='javascript:supprimerEC({$ue['id']},{$ec['id']});' data-toggle="tooltip" data-placement="top" title="Supprimer l'EC"><i class="icon-trash"></i></a>
                        </span>
                      </div>
                    </li>
HTML;
            }
        }
    }
?>
    </ul>
<?php } ?>
