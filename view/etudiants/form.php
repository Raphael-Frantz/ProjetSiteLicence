  <div class="form-group row">
    <label for="inputNom" class="col-sm-2 col-form-label">Nom</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="inputNom" name="inputNom" placeholder="Saisir le nom"
<?php if(isset($data['etudiant']) && ($data['etudiant']->getNom() != "")) echo " value=\"".htmlspecialchars($data['etudiant']->getNom())."\""; ?>>
    </div>
  </div>
  <div class="form-group row">
    <label for="inputPrenom" class="col-sm-2 col-form-label">Prénom</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="inputPrenom" name="inputPrenom" placeholder="Saisir le prénom"
<?php if(isset($data['etudiant']) && ($data['etudiant']->getPrenom() != "")) echo " value=\"".htmlspecialchars($data['etudiant']->getPrenom())."\""; ?>>
    </div>
  </div>
  <div class="form-group row">
    <label for="inputNumero" class="col-sm-2 col-form-label">Numéro</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="inputNumero" name="inputNumero" placeholder="Saisir le numéro d'étudiant"
<?php if(isset($data['etudiant']) && ($data['etudiant']->getNumero() != -1)) echo " value=\"".$data['etudiant']->getNumero()."\""; ?>>
    </div>
  </div>
  <div class="form-group row">
    <label for="inputMail" class="col-sm-2 col-form-label">Mail</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="inputEmail" name="inputEmail" placeholder="Saisir l'adresse mail"
<?php if(isset($data['etudiant']) && ($data['etudiant']->getEmail() != "")) echo " value=\"".htmlspecialchars($data['etudiant']->getEmail())."\""; ?>>
    </div>
  </div>