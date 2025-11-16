<?php
  session_start();
    // Vérifier si l'utilisateur est connecté
  if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true) {
    $_SESSION['erreur'] = "Vous devez être connecté pour accéder au tableau de bord.";
    header('Location: ../auth/connexion.php');
    exit();
  }

  require_once("../config/param.inc.php");
  $titre = "Tableau de bord";
  include('../includes/header.inc.php');
  include('../includes/menu.inc.php');
  
  // Si l'utilisateur est un déménageur, récupérer ses statistiques d'avis
  $stats_avis = null;
  if ($_SESSION['role'] == 2) {
    $mysqli = new mysqli($host, $login, $passwd, $dbname);
    
    if (!$mysqli->connect_error) {
      $mysqli->set_charset("utf8");
      
      $stats_query = "SELECT 
                      COUNT(*) as nb_avis,
                      AVG(note) as moyenne_note,
                      SUM(CASE WHEN note = 5 THEN 1 ELSE 0 END) as nb_5_etoiles,
                      SUM(CASE WHEN note = 4 THEN 1 ELSE 0 END) as nb_4_etoiles,
                      SUM(CASE WHEN note = 3 THEN 1 ELSE 0 END) as nb_3_etoiles,
                      SUM(CASE WHEN note = 2 THEN 1 ELSE 0 END) as nb_2_etoiles,
                      SUM(CASE WHEN note = 1 THEN 1 ELSE 0 END) as nb_1_etoile
                      FROM avis 
                      WHERE demenageur_id = ?";
      $stats_stmt = $mysqli->prepare($stats_query);
      $stats_stmt->bind_param("i", $_SESSION['user_id']);
      $stats_stmt->execute();
      $stats_result = $stats_stmt->get_result();
      $stats_avis = $stats_result->fetch_assoc();
      $stats_stmt->close();
      $mysqli->close();
    }
  }
?>

<div class="row mb-5">
  <div class="col-lg-10 mx-auto">
    <h1 class="mb-4">Tableau de bord</h1>    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">Informations de votre compte</h5>
      </div>
      <div class="card-body">        <div class="row mb-3">
          <div class="col-sm-3">
            <strong>Nom :</strong>
          </div>
          <div class="col-sm-9">
            <?php echo htmlspecialchars($_SESSION['nom']); ?>
          </div>
        </div>
        
        <div class="row mb-3">
          <div class="col-sm-3">
            <strong>Prénom :</strong>
          </div>
          <div class="col-sm-9">
            <?php echo htmlspecialchars($_SESSION['prenom']); ?>
          </div>
        </div>
        
        <div class="row mb-3">
          <div class="col-sm-3">
            <strong>Email :</strong>
          </div>
          <div class="col-sm-9">
            <?php echo htmlspecialchars($_SESSION['email']); ?>
          </div>
        </div>
        
        <div class="row mb-3">
          <div class="col-sm-3">
            <strong>Type de compte :</strong>
          </div>
          <div class="col-sm-9">
            <?php 
              switch($_SESSION['role']) {
                case 0:
                  echo '<span class="badge bg-warning">Compte non activé</span>';
                  break;
                case 1:
                  echo '<span class="badge bg-success">Client</span>';
                  break;
                case 2:
                  echo '<span class="badge bg-info">Déménageur</span>';
                  break;
                case 3:
                  echo '<span class="badge bg-danger">Administrateur</span>';
                  break;
                default:
                  echo '<span class="badge bg-secondary">Inconnu</span>';
              }
            ?>
          </div>
        </div>
      </div>
    </div>
    
    <?php if ($_SESSION['role'] == 2): // Si c'est un déménageur ?>
    <div class="mt-4">
      <div class="card border-info">
        <div class="card-header bg-info text-white">
          <h6 class="mb-0">Espace Déménageur</h6>
        </div>
        <div class="card-body">
          <p>Gérez votre activité de déménageur :</p>
          <div class="row g-3">
            <div class="col-md-6">
              <div class="card">
                <div class="card-body">
                  <h6 class="card-title">📋 Mes interventions</h6>
                  <p class="card-text small">Consultez les déménagements pour lesquels vous avez été accepté</p>
                  <a href="mes_interventions.php" class="btn btn-info btn-sm w-100">Voir mes interventions</a>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card">
                <div class="card-body">
                  <h6 class="card-title">🔍 Annonces disponibles</h6>
                  <p class="card-text small">Trouvez de nouveaux clients et proposez vos services</p>
                  <a href="annonces.php" class="btn btn-info btn-sm w-100">Voir les annonces</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="mt-4">
      <div class="card border-warning mb-4">
        <div class="card-header bg-warning text-dark">
          <h6 class="mb-0"><i class="bi bi-star-fill"></i> Mes avis clients</h6>
        </div>
        <div class="card-body">
          <?php if ($stats_avis && $stats_avis['nb_avis'] > 0): ?>
            <div class="row align-items-center mb-3">
              <div class="col-md-4 text-center">
                <div class="display-4 fw-bold text-warning">
                  <?php echo number_format($stats_avis['moyenne_note'], 1, ',', ' '); ?>
                  <i class="bi bi-star-fill"></i>
                </div>
                <p class="text-muted mb-0">sur 5</p>
                <p class="text-muted small"><?php echo $stats_avis['nb_avis']; ?> avis</p>
              </div>
              <div class="col-md-8">
                <div class="mb-2">
                  <div class="d-flex align-items-center">
                    <span class="me-2 stats-avis-label">5 <i class="bi bi-star-fill text-warning"></i></span>
                    <div class="progress flex-grow-1 me-2 stats-avis-progress">
                      <div class="progress-bar bg-warning" role="progressbar" 
                           style="width: <?php echo $stats_avis['nb_avis'] > 0 ? ($stats_avis['nb_5_etoiles'] / $stats_avis['nb_avis'] * 100) : 0; ?>%">
                      </div>
                    </div>
                    <span class="text-muted stats-avis-count"><?php echo $stats_avis['nb_5_etoiles']; ?></span>
                  </div>
                </div>
                <div class="mb-2">
                  <div class="d-flex align-items-center">
                    <span class="me-2 stats-avis-label">4 <i class="bi bi-star-fill text-warning"></i></span>
                    <div class="progress flex-grow-1 me-2 stats-avis-progress">
                      <div class="progress-bar bg-warning" role="progressbar" 
                           style="width: <?php echo $stats_avis['nb_avis'] > 0 ? ($stats_avis['nb_4_etoiles'] / $stats_avis['nb_avis'] * 100) : 0; ?>%">
                      </div>
                    </div>
                    <span class="text-muted stats-avis-count"><?php echo $stats_avis['nb_4_etoiles']; ?></span>
                  </div>
                </div>
                <div class="mb-2">
                  <div class="d-flex align-items-center">
                    <span class="me-2 stats-avis-label">3 <i class="bi bi-star-fill text-warning"></i></span>
                    <div class="progress flex-grow-1 me-2 stats-avis-progress">
                      <div class="progress-bar bg-warning" role="progressbar" 
                           style="width: <?php echo $stats_avis['nb_avis'] > 0 ? ($stats_avis['nb_3_etoiles'] / $stats_avis['nb_avis'] * 100) : 0; ?>%">
                      </div>
                    </div>
                    <span class="text-muted stats-avis-count"><?php echo $stats_avis['nb_3_etoiles']; ?></span>
                  </div>
                </div>
                <div class="mb-2">
                  <div class="d-flex align-items-center">
                    <span class="me-2 stats-avis-label">2 <i class="bi bi-star-fill text-warning"></i></span>
                    <div class="progress flex-grow-1 me-2 stats-avis-progress">
                      <div class="progress-bar bg-warning" role="progressbar" 
                           style="width: <?php echo $stats_avis['nb_avis'] > 0 ? ($stats_avis['nb_2_etoiles'] / $stats_avis['nb_avis'] * 100) : 0; ?>%">
                      </div>
                    </div>
                    <span class="text-muted stats-avis-count"><?php echo $stats_avis['nb_2_etoiles']; ?></span>
                  </div>
                </div>
                <div class="mb-0">
                  <div class="d-flex align-items-center">
                    <span class="me-2 stats-avis-label">1 <i class="bi bi-star-fill text-warning"></i></span>
                    <div class="progress flex-grow-1 me-2 stats-avis-progress">
                      <div class="progress-bar bg-warning" role="progressbar" 
                           style="width: <?php echo $stats_avis['nb_avis'] > 0 ? ($stats_avis['nb_1_etoile'] / $stats_avis['nb_avis'] * 100) : 0; ?>%">
                      </div>
                    </div>
                    <span class="text-muted stats-avis-count"><?php echo $stats_avis['nb_1_etoile']; ?></span>
                  </div>
                </div>
              </div>
            </div>
            <a href="mes_interventions.php" class="btn btn-outline-warning btn-sm">
              <i class="bi bi-eye"></i> Voir tous mes avis
            </a>
          <?php else: ?>
            <div class="text-center py-3">
              <i class="bi bi-star display-1 text-muted"></i>
              <p class="text-muted mb-0">Aucun avis pour le moment</p>
              <p class="text-muted small">Complétez des déménagements pour recevoir vos premiers avis !</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php elseif ($_SESSION['role'] == 1): // Si c'est un client ?>
    <div class="mt-4">
      <div class="card border-success">
        <div class="card-header bg-success text-white">
          <h6 class="mb-0">Espace Client</h6>
        </div>
        <div class="card-body">
          <p>Gérez vos demandes de déménagement :</p>
          <div class="row g-3">
            <div class="col-md-6">
              <div class="card">
                <div class="card-body">
                  <h6 class="card-title">📦 Mes déménagements</h6>
                  <p class="card-text small">Consultez et gérez vos demandes de déménagement</p>
                  <a href="mes_demenagements.php" class="btn btn-success btn-sm w-100">Voir mes demandes</a>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card">
                <div class="card-body">
                  <h6 class="card-title">➕ Nouvelle demande</h6>
                  <p class="card-text small">Créez une nouvelle demande de déménagement</p>
                  <a href="creer_demenagement.php" class="btn btn-success btn-sm w-100">Créer une demande</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php elseif ($_SESSION['role'] == 3): // Si c'est un administrateur ?>
    <div class="mt-4">
      <div class="card border-danger">
        <div class="card-header bg-danger text-white">
          <h6 class="mb-0"><i class="bi bi-shield-check"></i> Espace Administrateur</h6>
        </div>
        <div class="card-body">
          <p>Gérez la plateforme :</p>
          <div class="row g-3">
            <div class="col-md-6">
              <div class="card">
                <div class="card-body">
                  <h6 class="card-title">📋 Gestion des annonces</h6>
                  <p class="card-text small">Consultez et supprimez les annonces de déménagement</p>
                  <a href="admin_annonces.php" class="btn btn-danger btn-sm w-100">Gérer les annonces</a>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card">
                <div class="card-body">
                  <h6 class="card-title">👥 Gestion des comptes</h6>
                  <p class="card-text small">Activez ou désactivez les comptes utilisateurs</p>
                  <a href="admin_comptes.php" class="btn btn-danger btn-sm w-100">Gérer les comptes</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
    
    <div class="mt-4">
      <div class="card">
        <div class="card-body text-center">
          <h6 class="card-title">Changer mot de passe</h6>
          <p class="card-text">Modifier votre mot de passe de connexion</p>
          <a href="../auth/changer_mot_passe.php" class="btn btn-outline-warning">Changer</a>
        </div>
      </div>
    </div>
    
  </div>
</div>

<?php
  include('../includes/footer.inc.php');
?>
