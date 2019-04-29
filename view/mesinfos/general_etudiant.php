<?php
WebPage::addJSScript("public/js/active-tooltips.js");
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Mes informations</h2>
    <p class="lead mb-0">Bienvenue <?php echo UserModel::getCurrentUser(); ?>.<br/>Retrouvez ici la liste des diplômes dans lesquels vous êtes inscrit, ainsi que vos tuteurs.</p>
  </div>
</section>

<section>
  <div class="container">
    <h3 class="mb-4">Diplômes</h3>
<?php
if(count($data['diplomes']) > 0) {
    echo <<<HTML
    <p class="lead mb-2">
        Voici la liste des diplômes dans lesquels vous êtes inscrit(e) :
    </p>
    <table class="table table-striped table-bordered">
    <tr>
      <th scope="col">Diplôme</th>
      <th scope="col">Responsable(s)</th>
      <th scope="col" class="text-right">Actions</th>
    </tr>
HTML;
    $i = 0;
    while($i < count($data['diplomes'])) {
        $email = $data['diplomes'][$i]['email'];
        $nom = $data['diplomes'][$i]['nom'];
        $j = $i + 1;
        $cc = "";
        while(($j < count($data['diplomes'])) && ($data['diplomes'][$i]['diplome'] === $data['diplomes'][$j]['diplome'])) {
            if($cc != "")
                $cc .= ",";
            $cc .= $data['diplomes'][$j]['email'];
            $nom .= ", ".$data['diplomes'][$j]['nom'];
            $j++;
        }
        
        if($cc != "")
            $email .= "?cc=".$cc;
        
        echo <<<HTML
    <tr>
        <td>{$data['diplomes'][$i]['diplome']}</td>
        <td>{$nom}</td>
        <td class="text-right">
        <a class='btn btn-sm btn-outline-primary mr-2' href='mailto:{$email}' data-toggle="tooltip" data-placement="top" title="Envoyer un mail aux responsables">
            <i class="icon-envelope"></i>
        </a>
        </td>
    </tr>
HTML;
        $i = $j;
    }
    echo "</table>";
}
else {
    echo <<<HTML
    <div class='card mb-4 border-primary box-shadow'>
      <div class='card-body'>
        <p class='lead mb-0'>Vous n'êtes inscrit dans aucun diplôme actuellement.</p>
      </div>
    </div>
HTML;
}
?>
  </div>
</section>

<section>
  <div class="container">
    <h3 class="mb-4">Tutorat</h3>
<?php
if(count($data['tuteurs']) > 0) {
    echo <<<HTML
    <p class="lead mb-2">
        Voici la liste de vos tuteurs :
    </p>
    <table class="table table-striped table-bordered">
    <tr>
      <th scope="col">Diplôme</th>
      <th scope="col">Tuteur</th>
      <th scope="col" class="text-right">Actions</th>
    </tr>
HTML;
    foreach($data['tuteurs'] as $tuteur) {
        echo <<<HTML
    <tr>
        <td>{$tuteur['diplome']}</td>
        <td>{$tuteur['nom']}</td>
        <td class="text-right">
        <a class='btn btn-sm btn-outline-primary mr-2' href='mailto:{$tuteur['email']}' data-toggle="tooltip" data-placement="top" title="Envoyer un mail à ce tuteur">
            <i class="icon-envelope"></i>
        </a>
        </td>
    </tr>
HTML;
    }
    echo "</table>";
}
else {
    echo <<<HTML
    <div class='card mb-4 border-primary box-shadow'>
      <div class='card-body'>
        <p class='lead mb-0'>Vous n'avez pas de tuteur. Contactez le responsable de votre/vos formation(s).</p>
      </div>
    </div>
HTML;
}
?>
  </div>
</section>