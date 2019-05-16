<?php
// @need groupes La liste des groupes
//   @need intitule string l'intitulé du groupe
// @need current intitulé du groupe à afficher
// @need week int numéro de la semaine
// @need seances La liste des séances
//   @need ec string nom de l'EC
//   @need type string nom du type
//   @need couleur string couleur de la séance
//   @need salle string nom de la salle
//   @need debut int timestamp du début de la séance
//   @need fin int timestamp de la fin de la séance


// Tableau : 1 cellule verticale toutes les 5 minutes.
// Toutes les cellules vides consécutives sont fusionnées.

WebPage::addCSSScript("vendor/fontawesome/css/all.min.css");
WebPage::addCSSScript("public/css/emploidutemps.css");

function getCreneaux() : array {
    $arr = array();

    for($h = 7; $h < 21; $h++)  {
        for($min = ($h == 7 ? 30 : 0); $min < 60; $min += 5) {

            if($min < 10) {
                $arr[] = $h . ':0' . $min;
            }
            else {
                $arr[] = $h . ':' . $min;
            }
        }
    }

    return $arr;
}

$creneaux = getCreneaux();
$nbCreneaux = count($creneaux);
$jours = array('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi');
$nbJours = count($jours);
$emploiDuTemps = array_fill(0, $nbJours, array_fill(0, $nbCreneaux, array('taille' => 0)));
$dateDebutSemaine = new DateTime();
$dateDebutSemaine->setISODate(date('Y'), $data['week']);
$timeDebutSemaine = $dateDebutSemaine->getTimestamp();
$timePremierCreneau = 7 * 60 + 30;

foreach($data['seances'] as $seance) {

    $day = intval(date('w', $seance['debut'])) - 1;
    $heuresDebut = date('H', $seance['debut']);
    $minutesDebut = date('i', $seance['debut']);
    $timeDebut = $heuresDebut * 60 + $minutesDebut;
    $heuresFin = date('H', $seance['fin']);
    $minutesFin = date('i', $seance['fin']);
    $timeFin = $heuresFin * 60 + $minutesFin;

    $creneau = ($timeDebut - $timePremierCreneau) / 5;
    $emploiDuTemps[$day][$creneau] = $seance;
    $emploiDuTemps[$day][$creneau]['taille'] = ($timeFin - $timeDebut) / 5;
}


// Fusionner les cellules vides

for($jour = 0; $jour < $nbJours; $jour++) {
    $dernierCreneauVide = 0;
    $taille = $emploiDuTemps[$jour][0]['taille'];

    for($creneau = 0; $creneau < $nbCreneaux; $creneau++) {

        if($emploiDuTemps[$jour][$creneau]['taille'] != 0) {

            $creneau += $emploiDuTemps[$jour][$creneau]['taille'];
            $dernierCreneauVide = $creneau + 1;

            if($dernierCreneauVide < $nbCreneaux)
                $taille = $emploiDuTemps[$jour][$dernierCreneauVide]['taille'];
        }
        else if($taille != 0) {
            $dernierCreneauVide = $creneau + 1;

            if($dernierCreneauVide < $nbCreneaux)
                $taille = $emploiDuTemps[$jour][$dernierCreneauVide]['taille'];
        }
        else {
            $emploiDuTemps[$jour][$dernierCreneauVide]['taille']++;
        }
    }
}
?>

<section class="mytitle text-center bg-light" xmlns="http://www.w3.org/1999/html">
    <div class="container">
        <h2 class="mb-2">Emploi du temps</h2>
        <p class="lead">Vous retrouverez ici votre emploi du temps<?php if(!empty($data['current'])) echo ' pour le groupe ' . $data['current']; ?></p>
        <p class="lead">Semaine <?php echo $data["week"]; ?></p>
        <?php
        $dateDebut = date('d/m/y', $timeDebutSemaine);
        $dateFin = date('d/m/y', $timeDebutSemaine + 5 * 24 * 3600);
        echo "Du Lundi $dateDebut au Samedi $dateFin";
        ?>


        <div>Groupes :
            <?php
            $liste = array();
            foreach($data['groupes'] as $grp) {

                $intituleGroupe = $grp['intitule'];
                $type = $grp['type'];
                $intituleType = Groupe::type2String($type);

                if(!empty($grp['planning']))
                    $liste[] = "<a href='{$grp['planning']}'>$intituleGroupe</a>";
                else
                    $liste[] = "$intituleGroupe";

            }

            echo implode(', ', $liste);
            ?></div>
    </div>
</section>

<section>
    <container class="container">
        <div class="row mx-4">
            <div class="col text-right">
                <a class="w-100 btn btn-outline-secondary" href="./emploidutemps.php?week=<?php echo $data['week'] - 1 ?>&group=<?php echo $data['current']; ?>">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </div>
            <div class="col text-left">
                <a class="w-100 btn btn-outline-secondary" href="./emploidutemps.php?week=<?php echo $data['week'] + 1 ?>&group=<?php echo $data['current']; ?>">
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        <div class="row m-4">
            <div class="col">
                <table class="table table-bordered table-hover">
                    <thead>
                    <tr>
                        <?php
                        $percent = 100 / ($nbJours + 1);
                        $first = TRUE;

                        foreach(array_merge(['Jour'], $jours) as $j) {
                            echo "<th scope='col' style='width: {$percent}%'";
                            if(!$first) {
                                echo "class='text-center'";
                            }
                            echo ">$j</th>";
                            $first = FALSE;
                        }
                        ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    for($heureIndex = 0; $heureIndex < $nbCreneaux; $heureIndex++) {
                        echo "<tr>";

                        if($heureIndex % 6 == 0)
                            echo "<th scope='row' class='bg-light pb-4 pt-0' rowspan='6'><small>$creneaux[$heureIndex]</small></th>";

                        for($j = 0; $j < $nbJours; $j++) {

                            $taille = $emploiDuTemps[$j][$heureIndex]['taille'];

                            if(isset($emploiDuTemps[$j][$heureIndex]['id'])) {
                                $seance = $emploiDuTemps[$j][$heureIndex];
                                $starttime = date('G:i', $seance['debut']);
                                $endtime = date('G:i', $seance['fin']);
                                echo<<<HTML
                                    <td class='creneau' rowspan='{$seance['taille']}'
                                          style='background-color: {$seance['couleur']}'>
                                          <div>
                                        {$seance['ec']}<br>
                                        {$seance['type']}<br>
                                        {$seance['salle']}
                                        {$starttime} - 
                                        {$endtime}</div>
                                    </td>
HTML;
                            }
                            else if($taille != 0) {
                                echo<<<HTML
                                    <td class="creneau-vide" rowspan="{$taille}"></td>
HTML;
                            }
                        }
                        echo '</tr>';
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </container>
</section>