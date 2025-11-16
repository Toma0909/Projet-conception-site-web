<?php
  session_start();
  
  // Vérifier si l'utilisateur est connecté et est un client
  if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true || $_SESSION['role'] != 1) {
    $_SESSION['erreur'] = "Accès non autorisé.";
    header('Location: ../auth/connexion.php');
    exit();
  }
  
  // Vérifier que l'ID est présent
  if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['erreur'] = "Déménagement non trouvé.";
    header('Location: mes_demenagements.php');
    exit();
  }
  
  $demenagement_id = intval($_GET['id']);
  $client_id = $_SESSION['user_id'];
  
  // Connexion BDD
  require_once("../config/param.inc.php");
  $mysqli = new mysqli($host, $login, $passwd, $dbname);
  
  if ($mysqli->connect_error) {
    $_SESSION['erreur'] = "Problème de connexion à la base de données !";
    header('Location: mes_demenagements.php');
    exit();
  }
  
  // Récupérer les informations du déménagement
  $query = "SELECT * FROM demenagement WHERE id = ? AND client_id = ?";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("ii", $demenagement_id, $client_id);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows === 0) {
    $_SESSION['erreur'] = "Déménagement non trouvé ou vous n'avez pas les droits pour le supprimer.";
    $stmt->close();
    $mysqli->close();
    header('Location: mes_demenagements.php');
    exit();
  }
  
  $demenagement = $result->fetch_assoc();
  $stmt->close();
  
  // Compter les propositions
  $count_query = "SELECT COUNT(*) as total FROM proposition WHERE demenagement_id = ?";
  $count_stmt = $mysqli->prepare($count_query);
  $count_stmt->bind_param("i", $demenagement_id);
  $count_stmt->execute();
  $count_result = $count_stmt->get_result();
  $counts = $count_result->fetch_assoc();
  $count_stmt->close();
  
  $mysqli->close();
  
  $titre = "Confirmer la suppression";
  include('../includes/header.inc.php');
  include('../includes/menu.inc.php');
?>

<div class="row">
  <div class="col-lg-8 mx-auto">
    <div class="card border-danger">
      <div class="card-header bg-danger text-white">
        <h4 class="mb-0">Confirmer la suppression</h4>
      </div>
      <div class="card-body">
        <div class="alert alert-warning">
          <strong>Attention !</strong> Cette action est irréversible.
        </div>
        
        <h5>Êtes-vous sûr de vouloir supprimer ce déménagement ?</h5>
        
        <div class="card mt-3 mb-3">
          <div class="card-body">
            <h6 class="card-title"><?php echo htmlspecialchars($demenagement['titre']); ?></h6>
            <p class="card-text">
              <strong>De :</strong> <?php echo htmlspecialchars($demenagement['ville_depart']); ?><br>
              <strong>Vers :</strong> <?php echo htmlspecialchars($demenagement['ville_arrivee']); ?><br>
              <strong>Date :</strong> <?php echo date('d/m/Y à H:i', strtotime($demenagement['date_demenagement'] . ' ' . $demenagement['heure_debut'])); ?>
            </p>
            <?php if ($counts['total'] > 0): ?>
              <div class="alert alert-info mb-0">
                <strong>Note :</strong> Ce déménagement a <?php echo $counts['total']; ?> proposition(s) qui sera/seront également supprimée(s).
              </div>
            <?php endif; ?>
          </div>
        </div>
        
        <form method="POST" action="supprimer_demenagement.php">
          <input type="hidden" name="id" value="<?php echo $demenagement['id']; ?>">
          <input type="hidden" name="confirmer" value="1">
          
          <div class="d-flex gap-2 justify-content-end">
            <a href="mes_demenagements.php" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-danger">Oui, supprimer définitivement</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php
  include('../includes/footer.inc.php');
?>
