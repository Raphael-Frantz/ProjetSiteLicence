var request = null;
var mode = 1;
var annee = 0;

function supprimer(id) {
    $('#idSupp').val(id);
    $('#supprimerForm').submit();
}

function modifier(id) {
    $('#idModi').val(id);
    $('#modifierForm').submit();
}

function activer(mode, id) {
    if(request == null) {
        request = $.ajax({
                type: 'POST',
                url: WEB_PATH + 'actualites/activer.php',
                data: { 'mode' : mode, 'id' : id },
                dataType: 'json'
            });
        request.done(valider);
        request.fail(function (jqXHR, textStatus, errorThrown){
                console.log(jqXHR);
                request = null;
            });       
    }
}
function valider(response, textStatus, jqXHR) {
    request = null;
    if(response['code'] == 1)
        changeMode(-1, -1);
    else
        console.log(jqXHR);
}
function changeMode(newMode, newAnnee) {
  if(newMode != -1) mode = newMode;
  if(newAnnee != -1) annee = newAnnee;

  if(request == null) {
    request = $.ajax({
      type: 'POST',
      url: WEB_PATH + 'actualites/get.php',
      data: { 'mode' : mode, 'annee' : annee },
      dataType: 'json'
    });
    request.done(function (response, textStatus, jqXHR) {
      if(response['mode'] == 0) {
        $("#cartes").empty();
        contenu = "<div class='card-deck mb-3 text-center'>";
        nb = 0;
        if(("ajouter" in response) && (response["ajouter"])) {
            contenu += "<div class='card mb-4 border-success box-shadow'>" +
                       "<div class='card-header'>" +
                       "<h4>Nouvelle activité</h4>" +
                       "</div>" +
                       "<div class='card-body'>" +
                       "<a class='btn btn-outline-success' href='" + WEB_PATH + "actualites/ajouter.php'>" +
                       "Ajouter une nouvelle activité</a></div></div>";
            nb = 1;
        }
        if((nb == 0) && (response["liste"].length == 0)) {
            contenu = "<div class='card mb-4 border-primary box-shadow'>" +
                      "<div class='card-body'>" +
                      "<p class='lead mb-0'>Il n'y a aucune actualité actuellement.</p></div></div>";
        }

        for(i = 0; i < response["liste"].length; i++) {
            if((i > 0) && (nb % 3) == 0) {
                contenu += "</div>";
                $("#cartes").append(contenu);
                contenu = "<div class='card-deck mb-3 text-center'>";
                nb = 0;
            }
            if(response["liste"][i]["active"])
                contenu += "<div class='card mb-4 box-shadow'>";
            else
                contenu += "<div class='card mb-4 box-shadow border-danger'>";
            contenu += "<div class='card-header'>" +
                       "<div class='d-flex justify-content-between align-items-center'>" +
                       "<h4 class='my-0 font-weight-normal'>" + response["liste"][i]["date"] + "</h4>" +
                       "<span>";
            if("modifier" in response["liste"][i]) {
                if(response["liste"][i]["active"])
                    contenu += "<a href='javascript:activer(0, "+response["liste"][i]["modifier"]+");'>"+
                               "<img class='zoom' src='" + WEB_PATH + "public/pictures/ban.png'/></a> ";
                else
                    contenu += "<a href='javascript:activer(1, "+response["liste"][i]["modifier"]+");'>"+
                               "<img class='zoom' src='" + WEB_PATH + "public/pictures/eye.png'/></a> ";
            }
            if("supprimer" in response["liste"][i])
              contenu += "<a href='javascript:supprimer("+response["liste"][i]["supprimer"]+");'>"+
                         "<img class='zoom' src='" + WEB_PATH + "public/pictures/trash.png'/></a> ";
            if("modifier" in response["liste"][i])
              contenu += "<a href='javascript:modifier("+response["liste"][i]["modifier"]+");'>"+
                         "<img class='zoom' src='" + WEB_PATH + "public/pictures/edit.png'/></a>";
            contenu += "</span></div></div>" +
                       "<div class='card-body'>"+
                       "<h3 class='card-title pricing-card-title'>"+response["liste"][i]["titre"]+"</h3>"+
                       "<p class='lead mb-0'>"+response["liste"][i]["contenu"]+"</p>";
            if("lien" in response["liste"][i]) {
                contenu += "<p class='lead mb-0' style='padding-top: 10px'><a class='btn btn-outline-primary' href='" +
                           response["liste"][i]["lien"]["lien"] + "'";
                if(response["liste"][i]["lien"]["externe"]) contenu += " target='_blank'";
                contenu += ">" + response["liste"][i]["lien"]["titre"] + "</a></p>";
            }
            contenu += "</div></div>";

            nb++;
        }
        contenu += "</div>";
        $("#cartes").append(contenu);
      }
      else {
        
      }
      request = null;
    });
    request.fail(function (jqXHR, textStatus, errorThrown){
      console.log(jqXHR);
      request = null;
    });   
  }
}