<?php
  session_start();
  $titre = "Accueil";
  include('header.inc.php');
  include('menu.inc.php');
  include('message.inc.php')
?>

<h1>Bienvenue chez LiftUp</h1>
<p class="lead">Votre solution de déménagement de confiance</p>

<div class="row g-4 mb-4">
  <div class="col-md-4">
    <div class="card h-100 text-center">
      <div class="card-body">
        <h5 class="card-title">Pour les Clients</h5>
        <p class="card-text">Trouvez facilement des déménageurs qualifiés et obtenez des devis personnalisés</p>
        <a href="inscription.php" class="btn btn-primary">Demander un devis</a>
      </div>
    </div>
  </div>
  
  <div class="col-md-4">
    <div class="card h-100 text-center">
      <div class="card-body">
        <h5 class="card-title">Pour les Déménageurs</h5>
        <p class="card-text">Développez votre activité en rejoignant notre réseau de professionnels</p>
        <a href="inscription.php" class="btn btn-success">Rejoindre le réseau</a>
      </div>
    </div>
  </div>
  
  <div class="col-md-4">
    <div class="card h-100 text-center">
      <div class="card-body">
        <h5 class="card-title">Qualité Garantie</h5>
        <p class="card-text">Tous nos partenaires sont vérifiés et évalués par nos clients</p>
        <a href="#" class="btn btn-outline-info">En savoir plus</a>
      </div>
    </div>
  </div>
</div>

<?php
  include('footer.inc.php');
?>