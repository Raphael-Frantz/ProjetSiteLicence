<script>
function ajouter() {
    id = $('#responsable').val();
    if(id != -1) {
        if($('#resp_' + id).length == 0) {
            $("#responsables").append("<div class='form-group form-row' id='resp_" + id + "'>" +
                                   "<div class='offset-sm-2 col'>" + 
                                   "<input type='text' readonly class='form-control-plaintext' value='" +
                                   $('#responsable option:selected').text() +
                                   "'></div>" +
                                   "<div class='col'>" +
                                   "<input type='hidden' value='" + id + "' name='resp[" + id + "]'/>" +
                                   "<button type='button' class='btn btn-outline-primary' " + 
                                   "onclick='javascript:supprimer(" + id + ")'>Supprimer</button>" +
                                   "</div></div>");
            $("#responsable option[value='" + id + "']").hide();
            $('#responsable').val(-1);
        }
    }
}
function supprimer(id) {
    $('#resp_' + id).remove();
    $("#responsable option[value=" + id + "]").show();
}
</script>

  <div class="form-group row">
    <label for="inputIntitule" class="col-sm-2 col-form-label">Intitulé</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="inputIntitule" id="inputIntitule" placeholder="Intitulé"
<?php if(isset($data['diplome']) && ($data['diplome']->getIntitule() != "")) echo " value='".$data['diplome']->getIntitule()."'"; ?>>
    </div>
  </div>
  <div class="form-group row">
    <label for="inputMinSemestre" class="col-sm-2 col-form-label">Semestre min.</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="inputMinSemestre" id="inputMinSemestre" placeholder="Semestre minimum"
<?php if(isset($data['diplome']) && ($data['diplome']->getMinSemestre() != "")) echo " value='".$data['diplome']->getMinSemestre()."'"; ?>>
    </div>
  </div>
  
  <div class="form-group form-row">
    <label for="responsable" class="col-sm-2 col-form-label">Responsable(s)</label>
<?php
if(isset($data['users']) && (count($data['users']) > 0)) {
    echo "<div class='col'>";
    echo "<select class='form-control' id='responsable'>";
    echo "<option value='-1'>Choisir un enseignant</option>";
    foreach($data['users'] as $user) {
        echo "<option value='{$user['id']}'>{$user['nom']} {$user['prenom']}</option>";
    }
    echo "</select>";
    echo "</div>";
    echo "<div class='col'>";
    echo "<button type='button' class='btn btn-outline-primary' onclick='javascript:ajouter()'>Ajouter</button>";
    echo "</div>";
}
else {
    echo "<div class='col'>";
    echo "<input type='text' readonly class='form-control-plaintext' value=\"Aucun enseignant dans la base.\">";
    echo "</div>";
}
?>
  </div>
  <div id='responsables'>
<?php
if(isset($data['resp']) && (count($data['resp']) > 0)) {
    foreach($data['resp'] as $user) {
        echo <<<HTML
  <div class="form-group form-row" id="resp_{$user['id']}">
    <div class="offset-sm-2 col">
      <input type="text" readonly class="form-control-plaintext" value="{$user['nom']} {$user['prenom']}">
    </div>
    <div class="col">
      <input type='hidden' value='{$user['id']}' name='resp[{$user['id']}]'/>
      <button type='button' class='btn btn-outline-primary' onclick='javascript:supprimer({$user['id']})'>Supprimer</button>
    </div>
  </div>
HTML;
    }
}
?>
  </div>