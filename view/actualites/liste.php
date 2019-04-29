<?php
$json = array();
$json['mode'] = 0;

if(UserModel::estAdmin())
    $json['ajouter'] = true;

$json['liste'] = array();
foreach($data['actualites'] as $actualite) {
    $new = array("active" => $actualite->estActive(),
                 "date" => DateTools::timestamp2Date($actualite->getDate()),
                 "titre" => $actualite->getTitre(),
                 "contenu" => $actualite->getContenu());

    if($actualite->getLien() != "") {
        $lien = array();
        if(substr($actualite->getLien(), 0, 4) == "http") {
            $lien["externe"] = true;
            $lien["lien"] = $actualite->getLien();
        }
        else {
            $lien["externe"] = false;
            $lien["lien"] = WEB_PATH.$actualite->getLien();
        }
        if($actualite->getTitreLien() != "")
            $lien["titre"] = $actualite->getTitreLien();
        else
            $lien["titre"] = "Allez sur le site";
        $new["lien"] = $lien;
    }

    if(UserModel::estAdmin()) {
        $new["supprimer"] = $actualite->getId();
        $new["modifier"] = $actualite->getId();
    }

    $json['liste'][] = $new;
}

header("Content-type: application/json");
echo json_encode($json);