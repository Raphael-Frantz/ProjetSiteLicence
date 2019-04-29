  <div class="form-group row">
    <label for="inputNom" class="col-sm-2 col-form-label">Nom</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="inputNom" id="inputNom" placeholder="Saisissez le nom"
<?php if(isset($data['user']) && ($data['user']->getName() != "")) echo " value='".$data['user']->getName()."'"; ?>>
    </div>
  </div>
  <div class="form-group row">
    <label for="inputPrenom" class="col-sm-2 col-form-label">Prénom</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="inputPrenom" id="inputPrenom" placeholder="Saisissez le prénom"
<?php if(isset($data['user']) && ($data['user']->getFirstname() != "")) echo " value='".$data['user']->getFirstname()."'"; ?>>
    </div>
  </div>
  <div class="form-group row">
    <label for="inputEmail" class="col-sm-2 col-form-label">Email</label>
    <div class="col-sm-10">
      <input type="mail" class="form-control" name="inputEmail" id="inputEmail" placeholder="Saisissez l'adresse email"
<?php if(isset($data['user']) && ($data['user']->getMail() != "")) echo " value='".$data['user']->getMail()."'"; ?>>
    </div>
  </div>
  <div class="form-group row">
    <label for="inputPassword" class="col-sm-2 col-form-label">Mot de passe</label>
    <div class="col-sm-10">
      <input type="password" class="form-control" name="inputPassword" id="inputPassword" placeholder="Saisissez le mot de passe">
    </div>
  </div>  
  <div class="form-group row">
    <label for="inputConf" class="col-sm-2 col-form-label">Confirmation</label>
    <div class="col-sm-10">
      <input type="password" class="form-control" name="inputConf" id="inputConf" placeholder="Saisissez la confirmation du mot de passe">
    </div>
  </div>