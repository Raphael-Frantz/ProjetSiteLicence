<?php
// @need justificatif le justificatif à afficher

if(!isset($data['justificatif']) || ($data['justificatif'] === null)) {
    echo <<<HTML
    <div class="alert alert-danger lead" role="alert">
      Le justificatif demandé n'a pas pu être chargé.
    </div> 
HTML;
}
else {
    $heure = (date('G', $data['justificatif']->getDateDebut()) != 0);
    $dateDebut = DateTools::timestamp2Date($data['justificatif']->getDateDebut(), $heure);
    $dateFin = DateTools::timestamp2Date($data['justificatif']->getDateFin(), $heure);
    $dateSaisie = DateTools::timestamp2Date($data['justificatif']->getDateSaisie(), true);
    
    echo <<<HTML
    <div class="container">
        <div class="row">
            <div class="col-4">Motif :</div>
            <div class="col">{$data['justificatif']->getMotif()}</div>
        </div>
        <div class="row">
            <div class="col-4">Début :</div>
            <div class="col">{$dateDebut}</div>
        </div>
        <div class="row">
            <div class="col-4">Fin :</div>
            <div class="col">{$dateFin}</div>
        </div>
        <div class="row">
            <div class="col-4">Remarque :</div>
            <div class="col">{$data['justificatif']->getRemarque()}</div>
        </div>
        <div class="row">
            <div class="col-4">Editeur :</div>
            <div class="col">{$data['editeur']}</div>
        </div>        
        <div class="row">
            <div class="col-4">Saisie :</div>
            <div class="col">{$dateSaisie}</div>
        </div>        
    </div>
HTML;
}