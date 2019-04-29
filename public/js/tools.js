function displayWaitModal() {
    $("body").append(
        "<div class='modal' id='waitModal' tabindex='-1' role='dialog' aria-labelledby='waitModalLabel' aria-hidden='true'>" +
        "<div class='modal-dialog modal-dialog-centered' role='document'>" +
        "<div class='modal-content'>" +
        "<div class='modal-body text-center'>" +
        "<img src='" + WEB_PATH + "public/pictures/urca.gif'/><br/>" +
        "Patience, traitement en cours..." +
        "</div></div></div></div>");
    $('#waitModal').on('hidden.bs.modal', function (e) {
        $('#waitModal').remove();
    })        
    $('#waitModal').modal("show");    
}
function hideWaitModal() {
    $('#waitModal').modal("hide");
}
function displayInfoModal(title, msg) {
    $("body").append(
        "<div class='modal fade' id='infoModal' tabindex='-1' role='dialog' aria-labelledby='infoModalLabel' aria-hidden='true'>" +
        "<div class='modal-dialog modal-dialog-centered' role='document'>" +
        "<div class='modal-content'>" +
        "<div class='modal-header'>" +
        "<h5 class='modal-title'>" + title + "</h5>" +
        "<button type='button' class='close' data-dismiss='modal' aria-label='Close'>" +
        "<span aria-hidden='true'>&times;</span>" +
        "</button></div>" +
        "<div class='modal-body'>" + msg +
        "</div><div class='modal-footer'>" +
        "<button type='button' class='btn btn-primary' data-dismiss='modal'>Fermer</button>" +
        "</div></div></div></div>");
    $('#infoModal').on('hidden.bs.modal', function (e) {
        $('#infoModal').remove();
    })        
    $('#infoModal').modal("show");
}
function displayErrorModal(msg) {
    $("body").append(
        "<div class='modal fade' id='erreurModal' tabindex='-1' role='dialog' aria-labelledby='erreurModalLabel' aria-hidden='true'>" +
        "<div class='modal-dialog modal-dialog-centered' role='document'>" +
        "<div class='modal-content'>" +
        "<div class='modal-header'>" +
        "<h5 class='modal-title'>Erreur</h5>" +
        "<button type='button' class='close' data-dismiss='modal' aria-label='Close'>" +
        "<span aria-hidden='true'>&times;</span>" +
        "</button></div>" +
        "<div class='modal-body'>" + msg +
        "</div><div class='modal-footer'>" +
        "<button type='button' class='btn btn-primary' data-dismiss='modal'>Fermer</button>" +
        "</div></div></div></div>");
    $('#erreurModal').on('hidden.bs.modal', function (e) {
        $('#erreurModal').remove();
    })        
    $('#erreurModal').modal("show");
}
function displayEmailModal() {
    texte = "";
    $('[data-email="email"]').each(function (index) {
        texte += $(this).text() + ",";
    });    
    $("body").append(
        "<div class='modal fade' id='emailModal' tabindex='-1' role='dialog' aria-labelledby='emailModalLabel' aria-hidden='true'>" +
        "<div class='modal-dialog modal-dialog-centered' role='document'>" +
        "<div class='modal-content'>" +
        "<div class='modal-header'>" +
        "<h5 class='modal-title'>Envoyer un email</h5>" +
        "<button type='button' class='close' data-dismiss='modal' aria-label='Close'>" +
        "<span aria-hidden='true'>&times;</span>" +
        "</button></div>" +
        "<div class='modal-body'>Pour envoyer un mail depuis votre client de messagerie, cliquez sur le lien suivant : <a href='mailto:?bcc=" + 
        texte + "'><i class='icon-envelope'></i></a>. Si ce n'est pas fonctionnel, vous pouvez copier les adresses directement ci-dessous :" +
        "<form><textarea rows='10' class='form-control' name='mailtoall'>" + texte + "</textarea></form>" +
        "<div class='alert alert-danger text-center mt-2' role='alert'>" +
        "Quand vous envoyez un mail à un groupe d'étudiants, utilisez le champ bcc (copie cachée).</div>" +
        "</div><div class='modal-footer'>" +
        "<button type='button' class='btn btn-primary' data-dismiss='modal'>Fermer</button>" +
        "</div></div></div></div>");
    $('#emailModal').on('hidden.bs.modal', function (e) {
        $('#emailModal').remove();
    });
    $('[name="mailtoall"').select();
    document.execCommand("copy");
    $('#emailModal').modal("show");
}
function updateContent(id, content) {
    $('[data-toggle="tooltip"]').tooltip('dispose');
    $('#' + id).empty().html(content);
    $('[data-toggle="tooltip"]').tooltip();
}
function deleteElement(id) {
    $('[data-toggle="tooltip"]').tooltip('dispose');
    $('#' + id).remove();
    $('[data-toggle="tooltip"]').tooltip();    
}
function setEtudiant(id, url, ret) {
    request = $.ajax({
            type: 'POST',
            url: WEB_PATH + 'etudiants/ws.php',
            data: { 'mode' : 7, 'etudiant' : id, 'back' : ret },
            dataType: 'json'
        });
    request.done(function (response, textStatus, jqXHR) {
            document.location.href = WEB_PATH + url;
            request = null;
        });
    request.fail(function (jqXHR, textStatus, errorThrown){
            console.log(jqXHR);
            request = null;
        });   
}
function displayErrorMsg(id,msg) {
    $('#' + id).append("<div class='media alert-danger text-center'>" +
                       "<div class='media-body'>" +
                       "<p class='lead mb-0' id='contenuMessage'>" + msg + "</p>" +
                       "</div></div>");    
}