<!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="<?php echo SITE_DESCRIPTION; ?>">
        <meta name="author" content="<?php echo SITE_AUTHOR; ?>">
        <link rel="icon" href="<?php echo WEB_PATH; ?>favicon.png">
        <title><?php echo WebPage::getTitle(); ?></title>
        <link href="<?php echo WEB_PATH; ?>vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="<?php echo WEB_PATH; ?>vendor/simple-line-icons/css/simple-line-icons.css" rel="stylesheet" type="text/css">
        <link href="<?php echo WEB_PATH; ?>public/css/landing-page.css?v=20022019" rel="stylesheet" type="text/css">
        <?php WebPage::displayCSS(); ?>
    </head>

    <body>    
    
        <button onclick="topFunction()" id="upBoutton" title="Go to top"><i class='icon-arrow-up'></i></button>
        
        <?php 
        // Display the menu
        require("menu.php"); 
        ?>
        
        <?php
        // Display information/error message
$pageError = WebPage::getCurrentErrorMsg();
$pageMessage = WebPage::getCurrentMsg();

if(($pageError != "") || ($pageMessage != "")) {
    echo <<<HTML
    <div class="bg-light p-2" id='globalMsg'>
      <div class="container">
HTML;
    if($pageError != "") {
        echo <<<HTML
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          $pageError
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
HTML;
    }
    if($pageMessage != "") {
        echo <<<HTML
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          $pageMessage
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
HTML;
    }
    echo <<<HTML
      </div>
    </div>
HTML;
}
?>

        <?php 
        // Display the page content
        echo WebPage::getContent(); 
        ?>

        <footer class="footer bg-light">
          <div class="container">
            <div class="row">
              <div class="col-lg-6 h-100 text-center text-lg-left my-auto">
                <ul class="list-inline mb-2">
                  <li class="list-inline-item">
                    <a class="m-auto" href="<?php echo WEB_PATH; ?>contacts.php">Contacts</a>
                  </li>
                  <li class="list-inline-item">&sdot;</li>
                  <li class="list-inline-item">
                    <a class="m-auto" href="<?php echo WEB_PATH; ?>acces.php">Accès</a>
                  </li>
                </ul>
                <p class="text-muted small mb-4 mb-lg-0">&copy; <?php echo SITE_TITLE." ".SITE_DATE; ?>. Tous droits réservés.</p>
              </div>              
              <div class="col-lg-6 h-100 text-center text-lg-right my-auto">
                <ul class="list-inline mb-0">
                  <li class="list-inline-item mr-3">
                    <a href="https://www.facebook.com/groups/213445229493092/" target="_blank">
                      <img alt='facebook' class="zoom" src="<?php echo WEB_PATH; ?>public/pictures/facebook.png" />
                    </a>
                    <a href="https://twitter.com/linforeims/" target="_blank">
                      <img alt='twitter' class="zoom" src="<?php echo WEB_PATH; ?>public/pictures/twitter.png" />
                    </a>                    
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </footer>        
        
        <script src="<?php echo WEB_PATH; ?>vendor/jquery/jquery.min.js"></script>
        <script src="<?php echo WEB_PATH; ?>vendor/bootstrap/js/bootstrap.bundle.min.js"></script>    
        <script src="<?php echo WEB_PATH; ?>public/js/up.js"></script>    
        <?php WebPage::displayJS(); ?>
    </body>
</html>