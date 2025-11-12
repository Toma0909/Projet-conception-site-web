<?php  session_start();
  $titre = "Annonces de déménagement";
  
  // Connexion BDD
  require_once(__DIR__ . "/../config/param.inc.php");
  $mysqli = new mysqli($host, $login, $passwd, $dbname);
  
  if ($mysqli->connect_error) {
    die("Erreur de connexion à la base de données");
  }
  
  // Récupérer les annonces avec informations du client
  $query = "SELECT d.*, c.prenom, c.nom 
            FROM demenagement d 
            JOIN compte c ON d.client_id = c.id 
            WHERE d.statut = 'en_attente' 
            ORDER BY d.date_creation DESC";
    $result = $mysqli->query($query);
  
  include('../includes/header.inc.php');
  include('../includes/menu.inc.php');
  include('../includes/message.inc.php');
?>

<h1>Annonces de déménagement</h1>
<p class="lead">Découvrez les demandes de déménagement en cours</p>

<?php if (isset($_SESSION['connecte']) && $_SESSION['connecte'] === true && $_SESSION['role'] == 1): ?>
  <div class="alert alert-info">
    <strong>Vous êtes client :</strong> <a href="creer_demenagement.php" class="alert-link">Créer une nouvelle demande de déménagement</a>
  </div>
<?php elseif (isset($_SESSION['connecte']) && $_SESSION['connecte'] === true && $_SESSION['role'] == 2): ?>
  <div class="alert alert-success">
    <strong>Vous êtes déménageur :</strong> Consultez les annonces ci-dessous et proposez vos services !
  </div>
<?php else: ?>
  <div class="alert alert-warning">
    <strong>Vous n'êtes pas connecté :</strong> <a href="../auth/connexion.php" class="alert-link">Connectez-vous</a> pour créer une demande ou proposer vos services.
  </div>
<?php endif; ?>

<div class="row g-4 mt-2">
  <?php
  if ($result && $result->num_rows > 0) {
    while ($annonce = $result->fetch_assoc()) {
      // Compter le nombre de propositions
      $count_query = "SELECT COUNT(*) as nb_propositions FROM proposition WHERE demenagement_id = ?";
      $count_stmt = $mysqli->prepare($count_query);
      $count_stmt->bind_param("i", $annonce['id']);
      $count_stmt->execute();
      $count_result = $count_stmt->get_result();
      $nb_propositions = $count_result->fetch_assoc()['nb_propositions'];
      $count_stmt->close();
  ?>
    <div class="col-md-6 col-lg-4">
      <div class="card h-100">
        <div class="card-body">
          <h5 class="card-title"><?php echo htmlspecialchars($annonce['titre']); ?></h5>
          <h6 class="card-subtitle mb-2 text-muted">
            <?php echo htmlspecialchars($annonce['ville_depart']); ?> → <?php echo htmlspecialchars($annonce['ville_arrivee']); ?>
          </h6>
          <p class="card-text">
            <?php echo nl2br(htmlspecialchars(substr($annonce['description'], 0, 150))); ?>
            <?php if(strlen($annonce['description']) > 150) echo '...'; ?>
          </p>
          <ul class="list-unstyled">
            <li><strong>Date :</strong> <?php echo date('d/m/Y', strtotime($annonce['date_demenagement'])); ?></li>
            <li><strong>Heure :</strong> <?php echo date('H:i', strtotime($annonce['heure_debut'])); ?></li>
            <li><strong>Déménageurs recherchés :</strong> <?php echo $annonce['nombre_demenageurs']; ?></li>
            <li><strong>Propositions :</strong> <span class="badge bg-info"><?php echo $nb_propositions; ?></span></li>
          </ul>
          <a href="detail_annonce.php?id=<?php echo $annonce['id']; ?>" class="btn btn-primary btn-sm">Voir les détails</a>
        </div>
        <div class="card-footer text-muted">
          <small>Publié le <?php echo date('d/m/Y', strtotime($annonce['date_creation'])); ?></small>
        </div>
      </div>
    </div>
  <?php
    }
  } else {
    echo '<div class="col-12"><div class="alert alert-info">Aucune annonce disponible pour le moment.</div></div>';
  }
  
  $mysqli->close();
  ?>
</div>

<?php
  include('../includes/footer.inc.php');
?>
