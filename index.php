
<?php
  session_start();
  $titre = "Accueil";
  $page_class = "home-page";
  
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
  
  include('includes/header.inc.php');
  include('includes/menu.inc.php');
  include('includes/message.inc.php')
?>

<div class="hero-section">
<div class="container hero-content">
<h1>Bienvenue chez LiftUp</h1>
<p class="lead">Votre solution de déménagement de confiance</p>

<div class="row g-4 mb-4">
  <div class="col-md-4">
    <div class="card h-100 text-center">
      <div class="card-body">
        <?php if (isset($_SESSION['connecte']) && $_SESSION['connecte'] === true && $_SESSION['role'] == 2): ?>
          <h5 class="card-title">Mes Interventions</h5>
          <p class="card-text">Consultez et gérez vos interventions de déménagement en cours et à venir</p>
          <a href="pages/mes_interventions.php" class="btn btn-primary">Mes interventions</a>
        <?php else: ?>
          <h5 class="card-title">Pour les Clients</h5>
          <p class="card-text">Trouvez facilement des déménageurs qualifiés et obtenez des devis personnalisés</p>
          <?php if (isset($_SESSION['connecte']) && $_SESSION['connecte'] === true && $_SESSION['role'] == 1): ?>
            <a href="pages/creer_demenagement.php" class="btn btn-primary">Créer une demande</a>
          <?php else: ?>
            <a href="auth/inscription.php" class="btn btn-primary">Demander un devis</a>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
  
  <div class="col-md-4">
    <div class="card h-100 text-center">
      <div class="card-body">
        <h5 class="card-title">
          <?php echo (isset($_SESSION['connecte']) && $_SESSION['connecte'] === true) ? 'Annonces Disponibles' : 'Pour les Déménageurs'; ?>
        </h5>
        <p class="card-text">
          <?php if (isset($_SESSION['connecte']) && $_SESSION['connecte'] === true): ?>
            Parcourez les demandes de déménagement et proposez vos services
          <?php else: ?>
            Développez votre activité en rejoignant notre réseau de professionnels
          <?php endif; ?>
        </p>
        <?php if (isset($_SESSION['connecte']) && $_SESSION['connecte'] === true): ?>
          <a href="pages/annonces.php" class="btn btn-success">Voir les annonces</a>
        <?php else: ?>
          <a href="auth/inscription.php" class="btn btn-success">Rejoindre le réseau</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
  
  <div class="col-md-4">
    <div class="card h-100 text-center">
      <div class="card-body">
        <h5 class="card-title">Qualité Garantie</h5>
        <p class="card-text">Tous nos partenaires sont vérifiés et évalués par nos clients</p>
        <a href="pages/a_propos.php" class="btn btn-info">En savoir plus</a>
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

</div>
</div>

<?php
  include('includes/footer.inc.php');
?>