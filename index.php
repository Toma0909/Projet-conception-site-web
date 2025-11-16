
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ... votre code PHP habituel commence ici ...
// (par exemple : include 'header.php', session_start(), etc.)
  session_start();
  $titre = "Accueil";
  
  // Connexion BDD pour afficher quelques annonces
  require_once("config/param.inc.php");
  $mysqli = new mysqli($host, $login, $passwd, $dbname);
  
  $annonces_recentes = [];
  if (!$mysqli->connect_error) {
    $query = "SELECT d.id, d.titre, d.ville_depart, d.ville_arrivee, d.date_demenagement 
              FROM demenagement d 
              WHERE d.statut = 'en_attente' 
              ORDER BY d.date_creation DESC 
              LIMIT 3";
    $result = $mysqli->query($query);
    if ($result) {
      $annonces_recentes = $result->fetch_all(MYSQLI_ASSOC);
    }
  }
  
  include('includes/header_root.inc.php');
  include('includes/menu_root.inc.php');
  include('includes/message.inc.php')
?>

<h1>Bienvenue chez LiftUp</h1>
<p class="lead">Votre solution de déménagement de confiance</p>

<div class="row g-4 mb-4">
  <div class="col-md-4">
    <div class="card h-100 text-center">
      <div class="card-body">
        <h5 class="card-title">Pour les Clients</h5>
        <p class="card-text">Trouvez facilement des déménageurs qualifiés et obtenez des devis personnalisés</p>
        <a href="auth/inscription.php" class="btn btn-primary">Demander un devis</a>
      </div>
    </div>
  </div>
  
  <div class="col-md-4">
    <div class="card h-100 text-center">
      <div class="card-body">
        <h5 class="card-title">Pour les Déménageurs</h5>
        <p class="card-text">Développez votre activité en rejoignant notre réseau de professionnels</p>
        <a href="auth/inscription.php" class="btn btn-success">Rejoindre le réseau</a>
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
    </div>  </div>
</div>

<!-- Section des annonces récentes -->
<?php if (count($annonces_recentes) > 0): ?>
<div class="mt-5">
  <h2 class="mb-4">Dernières annonces de déménagement</h2>
  <div class="row g-4">
    <?php foreach ($annonces_recentes as $annonce): ?>
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-body">
          <h5 class="card-title"><?php echo htmlspecialchars($annonce['titre']); ?></h5>
          <p class="card-text">
            <strong>Trajet :</strong> <?php echo htmlspecialchars($annonce['ville_depart']); ?> 
            → <?php echo htmlspecialchars($annonce['ville_arrivee']); ?><br>
            <strong>Date :</strong> <?php echo date('d/m/Y', strtotime($annonce['date_demenagement'])); ?>
          </p>
          <a href="pages/detail_annonce.php?id=<?php echo $annonce['id']; ?>" class="btn btn-outline-primary btn-sm">Voir les détails</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="text-center mt-4">
    <a href="pages/annonces.php" class="btn btn-primary">Voir toutes les annonces</a>
  </div>
</div>
<?php endif; ?>

<?php
  if (!$mysqli->connect_error) {
    $mysqli->close();
  }
  include('includes/footer.inc.php');
?>