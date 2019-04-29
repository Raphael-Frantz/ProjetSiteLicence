<?php
WebPage::addOnlineScript("var WEB_PATH = '".WEB_PATH."';");
WebPage::addJSScript("public/js/active-tooltips.js");
WebPage::addJSScript("public/js/tools.js");
?>
<section class="mytitle text-center bg-light">
  <div class="container">
    <h2 class="mb-5">Tutorat</h2>
    <p class="lead mb-0">Vous trouverez ici la liste des étudiant(e)s dont vous êtes le tuteur.</p>
  </div>
</section>

<section>
  <div class="container">
<?php
if(UserModel::estTuteur()) {
?>
    <h3 class="mb-4">Etudiant(e)s tutoré(e)s
<?php
if(count($data['etudiants']) > 0) {
    echo <<<HTML
    <a class='btn btn-sm btn-outline-primary mr-2' href='javascript:displayEmailModal()' data-toggle="tooltip" data-placement="top" title="Envoyer un mail à tous les étudiants">
      <i class="icon-envelope"></i>
    </a></h3>
<table class="table table-striped table-bordered" id="tableEtudiants">
  <thead>
    <tr>
      <th scope="col">Numéro</th>
      <th scope="col">Nom - Prénom</th>
      <th scope="col">Adresse mail</th>
      <th scope="col" class="text-right">Actions</th>
    </tr>
  </thead>
HTML;
    foreach($data['etudiants'] as $etudiant) {
        echo <<<HTML
    <tr>
      <td>{$etudiant['numero']}</td>
      <td><a href="javascript:setEtudiant({$etudiant['id']}, 'etudiants/ip.php', 'mesinfos/tutorat.php')">{$etudiant['nom']}</a></td>
      <td data-email='email'>{$etudiant['email']}</td>
      <td class="text-right">
        <a class='btn btn-sm btn-outline-primary mr-2' href='mailto:{$etudiant['email']}' data-toggle="tooltip" data-placement="top" title="Envoyer un mail">
          <i class="icon-envelope"></i>
        </a>
      </td>
    </tr>
HTML;
    }
    echo "</table>";
}
else {
?>
    <p class="lead mb-2">
        Vous n'avez aucun étudiant en tutorat.
    </p>
<?php
}
?>
  </div>
<?php
}
else {
    echo "<p>Vous n'êtes pas tuteur.</p>";
}
?>
</section>