<?php
// @need EC l'EC en cours
// @need resp la liste des responsables de l'EC
?>
<script>
function ajouter() {
    id = $('#intervenant').val();
    if(id != -1) {
        if($('#int_' + id).length == 0) {
            $("#intervenants").append("<div class='form-group form-row' id='int_" + id + "'>" +
                                   "<div class='offset-sm-2 col'>" + 
                                   "<input type='text' readonly class='form-control-plaintext' value='" +
                                   $('#intervenant option:selected').text() +
                                   "'></div>" +
                                   "<div class='col'>" +
                                   "<input type='hidden' value='" + id + "' name='int[" + id + "]'/>" +
                                   "<button type='button' class='btn btn-outline-primary' " + 
                                   "onclick='javascript:supprimer(" + id + ")'>Supprimer</button>" +
                                   "</div></div>");
            $("#intervenant option[value='" + id + "']").hide();
            $('#intervenant').val(-1);
        }
    }
}
function supprimer(id) {
    $('#int_' + id).remove();
    $("#intervenant option[value=" + id + "]").show();
}
</script>

  <div class="form-group row">
    <label for="inputNom" class="col-sm-2 col-form-label">Intitulé</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="inputIntitule" name="inputIntitule" placeholder="Nom"
<?php if(isset($data['groupe']) && ($data['groupe']->getIntitule() != "")) echo " value='".$data['groupe']->getIntitule()."'"; ?>>
      <small class="form-text text-muted">Saisir l'intitulé du groupe</small>
    </div>
  </div>
  <div class="form-group row">
    <label for="inputType" class="col-sm-2 col-form-label">Type du groupe</label>
    <div class="col-sm-10">
      <select class="form-control" id="inputType" name="inputType" <?php if(isset($data['groupe']) && ($data['groupe']->getId() != -1)) echo " disabled"; ?>>
        <option value="<?php echo Groupe::GRP_CM; ?>" <?php if(isset($data['groupe']) && $data['groupe']->getType() == Groupe::GRP_CM) echo "selected=\"selected\""; ?>>CM</option>
        <option value="<?php echo Groupe::GRP_TD; ?>" <?php if(isset($data['groupe']) && $data['groupe']->getType() == Groupe::GRP_TD) echo "selected=\"selected\""; ?>>TD</option>
        <option value="<?php echo Groupe::GRP_TP; ?>" <?php if(isset($data['groupe']) && $data['groupe']->getType() == Groupe::GRP_TP) echo "selected=\"selected\""; ?>>TP</option>
      </select>
<?php
if(isset($data['groupe']) && ($data['groupe']->getId() != -1))
    echo "<small class='form-text text-muted'>Le type de groupe ne peut être modifié</small>";
else
    echo "<small class='form-text text-muted'>Spécifiez le type du groupe ; une fois créé, le type d'un groupe ne peut plus être modifié</small>";
?>
    </div>    
  </div>
  
  <div class="form-group form-row">
    <label for="intervenant" class="col-sm-2 col-form-label">Intervenant(s)</label>
<?php
if(isset($data['users']) && (count($data['users']) > 0)) {
    echo "<div class='col'>";
    echo "<select class='form-control' id='intervenant'>";
    echo "<option value='-1'>Choisir un enseignant</option>";
    foreach($data['users'] as $user) {
        echo "<option value='{$user['id']}'>{$user['nom']} {$user['prenom']}</option>";
    }
    echo <<<HTML
    </select>
    <small class="form-text text-muted">Choisissez l'intervenant puis cliquez sur le bouton Ajouter</small>
  </div>
  <div class='col'>
    <button type='button' class='btn btn-outline-primary' onclick='javascript:ajouter()'>Ajouter</button>
  </div>
HTML;
}
else {
    echo "<div class='col'>";
    echo "<input type='text' readonly class='form-control-plaintext' value=\"Aucun enseignant dans la base.\">";
    echo "</div>";
}
?>
  </div>
  <div id='intervenants'>
<?php
if(isset($data['int']) && (count($data['int']) > 0)) {
    foreach($data['int'] as $user) {
        echo <<<HTML
  <div class="form-group form-row" id="int_{$user['id']}">
    <div class="offset-sm-2 col">
      <input type="text" readonly class="form-control-plaintext" value="{$user['nom']} {$user['prenom']}">
    </div>
    <div class="col">
      <input type='hidden' value='{$user['id']}' name='int[{$user['id']}]'/>
      <button type='button' class='btn btn-outline-primary' onclick='javascript:supprimer({$user['id']})'>Supprimer</button>
    </div>
  </div>
HTML;
    }
}
?>
  </div>