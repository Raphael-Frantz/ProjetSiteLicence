<?php
// @need actualites la liste des actualités
?>
<section style="background-color: #777777;">
  <div id="myCarousel" class="carousel slide" data-ride="carousel">
    <ol class="carousel-indicators">
<?php
$i = 0;
foreach($data['actualites'] as $actualite) {
  if($i == 0)
    echo "      <li data-target='#myCarousel' data-slide-to='$i' class='active'></li>\n";
  else
    echo "      <li data-target='#myCarousel' data-slide-to='$i'></li>\n";
  $i++;
}
?>    </ol>
    <div class="carousel-inner">
<?php
$i = 0;
foreach($data['actualites'] as $actualite) {
  if($i == 0)
    echo "<div class='carousel-item active'>\n";
  else
    echo "<div class='carousel-item'>\n";
  echo "  <div class='text-center'>\n";
  echo "    <div class='features-icons-icon d-flex'>\n";
  echo "      <img class='m-auto' alt='' src='".WEB_PATH."public/pictures/carousel.png'/>\n";
  echo "    </div>\n";
  echo "  </div>\n";
  echo "  <div class='container'>\n";
  echo "    <div class='carousel-caption'>\n";
  echo "      <h3>{$actualite->getTitre()}</h3>";
  echo "      <p class='lead mb-2'>{$actualite->getContenu()}</p>";
  if($actualite->getLien() != "") {
    echo "      <p class='lead mb-0'><a class='btn btn-primary' href='";
    if(substr($actualite->getLien(), 0, 4) == "http")
        echo $actualite->getLien()."' target='_blank'>";
    else
        echo WEB_PATH.$actualite->getLien()."'>";
    if($actualite->getTitreLien() != "")
        echo $actualite->getTitreLien()."</a></p>\n";
    else
        echo "Aller sur le site</a></p>\n";
  }  
  echo "    </div>\n";
  echo "  </div>\n";
  echo "</div>\n";
  $i++;
}
WebPage::addOnReady("$('.carousel').carousel({ interval: 4000 })");
?>
    <a class="carousel-control-prev" href="#myCarousel" role="button" data-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="sr-only">Previous</span>
    </a>
    <a class="carousel-control-next" href="#myCarousel" role="button" data-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="sr-only">Next</span>
    </a>
  </div>
  </div>
</section>