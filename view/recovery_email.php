<html lang='fr'>
  <head>
    <title> Récupération de mot de passe </title>
    <meta charset="utf-8">
  </head>
  <body>
    <p>
      Bonjour,
    </p>  
    <p>
      Vous avez validé le formulaire de récupération de mot de passe sur le site '<?php echo SITE_TITLE; ?>'.
      Pour modifiez votre mot de passe, allez sur la page suivante : 
      <a href="<?php echo WEB_PATH; ?>newpassword.php?email=<?php if(isset($data['email'])) echo $data['email']; ?>&key=<?php if(isset($data['key'])) echo $data['key']; ?>"><?php echo WEB_PATH; ?>newpassword.php</a>
    </p>
    <p>Ce mail a été envoyé automatiquement, merci de ne pas y répondre.</p>
    <p>Si vous n'êtes pas à l'initiative de ce mail, contacter le responsable du site.</p>
  </body>
</html>