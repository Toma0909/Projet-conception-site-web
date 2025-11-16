<?php
  session_start();
  
  // Récupérer l'ID de l'utilisateur à afficher
  $profil_id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0);
  
  if ($profil_id <= 0) {
    $_SESSION['erreur'] = "Utilisateur non trouvé.";
    header('Location: tableau_bord.php');
    exit();
  }
  
  // Connexion BDD
  require_once("../config/param.inc.php");
  $mysqli = new mysqli($host, $login, $passwd, $dbname);
  
  if ($mysqli->connect_error) {
    die("Erreur de connexion à la base de données");
  }
  
  $mysqli->set_charset("utf8");
  
  // Récupérer les informations de l'utilisateur
  $query = "SELECT id, nom, prenom, email, role FROM compte WHERE id = ?";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $profil_id);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows == 0) {
    $_SESSION['erreur'] = "Utilisateur non trouvé.";
    $stmt->close();
    $mysqli->close();
    header('Location: tableau_bord.php');
    exit();
  }
  
  $user = $result->fetch_assoc();
  $stmt->close();
  
  $is_own_profile = (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $profil_id);
  $is_admin = (isset($_SESSION['role']) && $_SESSION['role'] == 3);
  
  // Statistiques selon le rôle
  $stats = [];
  
  if ($user['role'] == 1) {
    // Client : nombre de déménagements
    $stats_query = "SELECT 
                    COUNT(*) as nb_total,
                    SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) as nb_attente,
                    SUM(CASE WHEN statut = 'en_cours' THEN 1 ELSE 0 END) as nb_en_cours,
                    SUM(CASE WHEN statut = 'termine' THEN 1 ELSE 0 END) as nb_termine
                    FROM demenagement 
                    WHERE client_id = ?";
    $stats_stmt = $mysqli->prepare($stats_query);
    $stats_stmt->bind_param("i", $profil_id);
    $stats_stmt->execute();
    $stats_result = $stats_stmt->get_result();
    $stats = $stats_result->fetch_assoc();
    $stats_stmt->close();
    
  } elseif ($user['role'] == 2) {
    // Déménageur : nombre d'interventions et moyenne des avis
    $stats_query = "SELECT 
                    COUNT(DISTINCT dd.demenagement_id) as nb_interventions,
                    COUNT(a.id) as nb_avis,
                    AVG(a.note) as moyenne_note
                    FROM demenagement_demenageur dd
                    LEFT JOIN avis a ON a.demenageur_id = dd.demenageur_id AND a.demenagement_id = dd.demenagement_id
                    WHERE dd.demenageur_id = ?";
    $stats_stmt = $mysqli->prepare($stats_query);
    $stats_stmt->bind_param("i", $profil_id);
    $stats_stmt->execute();
    $stats_result = $stats_stmt->get_result();
    $stats = $stats_result->fetch_assoc();
    $stats_stmt->close();
  }
  
  $titre = "Profil de " . htmlspecialchars($user['prenom'] . ' ' . $user['nom']);
  include('../includes/header.inc.php');
  include('../includes/menu.inc.php');
  include('../includes/message.inc.php');
?>

<div class="row mb-5">
  <div class="col-lg-10 mx-auto">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1>
        <i class="bi bi-person-circle"></i> 
        Profil <?php echo $is_own_profile ? '' : 'de ' . htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?>
      </h1>
      <?php if ($is_admin && !$is_own_profile): ?>
        <a href="admin_comptes.php" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-left"></i> Retour aux comptes
        </a>
      <?php endif; ?>
    </div>
    
    <div class="row">
      <div class="col-md-4">
        <div class="card mb-4">
          <div class="card-body text-center">
            <div class="mb-3">
              <i class="bi bi-person-circle display-1 text-primary"></i>
            </div>
            <h4><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h4>
            <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
            <div class="mt-3">
              <?php 
                switch($user['role']) {
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
                }
              ?>
            </div>
          </div>
        </div>
        
        <?php if ($is_admin && !$is_own_profile): ?>
        <div class="card border-warning">
          <div class="card-header bg-warning text-dark">
            <h6 class="mb-0"><i class="bi bi-shield-exclamation"></i> Actions administrateur</h6>
          </div>
          <div class="card-body">
            <div class="d-grid gap-2">
              <?php if ($user['role'] != 0): ?>
                <a href="admin_desactiver_compte.php?id=<?php echo $user['id']; ?>" 
                   class="btn btn-outline-danger btn-sm"
                   onclick="return confirm('Désactiver ce compte ?');">
                  <i class="bi bi-lock"></i> Désactiver le compte
                </a>
              <?php else: ?>
                <a href="admin_activer_compte.php?id=<?php echo $user['id']; ?>" 
                   class="btn btn-outline-success btn-sm">
                  <i class="bi bi-unlock"></i> Activer le compte
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>
      
      <div class="col-md-8">
        <?php if ($user['role'] == 1): ?>
        <!-- Statistiques client -->
        <div class="card mb-4">
          <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-box-seam"></i> Activité en tant que client</h5>
          </div>
          <div class="card-body">
            <div class="row text-center">
              <div class="col-md-3">
                <div class="mb-2">
                  <h3 class="mb-0 text-primary"><?php echo $stats['nb_total']; ?></h3>
                  <small class="text-muted">Total</small>
                </div>
              </div>
              <div class="col-md-3">
                <div class="mb-2">
                  <h3 class="mb-0 text-warning"><?php echo $stats['nb_attente']; ?></h3>
                  <small class="text-muted">En attente</small>
                </div>
              </div>
              <div class="col-md-3">
                <div class="mb-2">
                  <h3 class="mb-0 text-info"><?php echo $stats['nb_en_cours']; ?></h3>
                  <small class="text-muted">En cours</small>
                </div>
              </div>
              <div class="col-md-3">
                <div class="mb-2">
                  <h3 class="mb-0 text-success"><?php echo $stats['nb_termine']; ?></h3>
                  <small class="text-muted">Terminés</small>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Liste des annonces du client -->
        <?php
          $annonces_query = "SELECT id, titre, ville_depart, ville_arrivee, date_demenagement, statut 
                            FROM demenagement 
                            WHERE client_id = ? 
                            ORDER BY date_creation DESC 
                            LIMIT 10";
          $annonces_stmt = $mysqli->prepare($annonces_query);
          $annonces_stmt->bind_param("i", $profil_id);
          $annonces_stmt->execute();
          $annonces_result = $annonces_stmt->get_result();
        ?>
        
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">Dernières annonces</h5>
          </div>
          <div class="card-body">
            <?php if ($annonces_result->num_rows > 0): ?>
              <div class="list-group">
                <?php while ($annonce = $annonces_result->fetch_assoc()): ?>
                  <a href="detail_annonce.php?id=<?php echo $annonce['id']; ?>" 
                     class="list-group-item list-group-item-action">
                    <div class="d-flex justify-content-between align-items-start">
                      <div>
                        <h6 class="mb-1"><?php echo htmlspecialchars($annonce['titre']); ?></h6>
                        <small class="text-muted">
                          <?php echo htmlspecialchars($annonce['ville_depart']); ?> 
                          → <?php echo htmlspecialchars($annonce['ville_arrivee']); ?>
                          • <?php echo date('d/m/Y', strtotime($annonce['date_demenagement'])); ?>
                        </small>
                      </div>
                      <span class="badge <?php 
                        echo $annonce['statut'] == 'termine' ? 'bg-success' : 
                            ($annonce['statut'] == 'en_cours' ? 'bg-info' : 'bg-warning');
                      ?>">
                        <?php 
                          echo $annonce['statut'] == 'termine' ? 'Terminé' : 
                              ($annonce['statut'] == 'en_cours' ? 'En cours' : 'En attente');
                        ?>
                      </span>
                    </div>
                  </a>
                <?php endwhile; ?>
              </div>
            <?php else: ?>
              <p class="text-muted mb-0">Aucune annonce pour le moment.</p>
            <?php endif; ?>
          </div>
        </div>
        <?php $annonces_stmt->close(); ?>
        
        <?php elseif ($user['role'] == 2): ?>
        <!-- Statistiques déménageur -->
        <div class="card mb-4">
          <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="bi bi-truck"></i> Activité en tant que déménageur</h5>
          </div>
          <div class="card-body">
            <div class="row text-center">
              <div class="col-md-4">
                <div class="mb-2">
                  <h3 class="mb-0 text-primary"><?php echo $stats['nb_interventions']; ?></h3>
                  <small class="text-muted">Interventions</small>
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-2">
                  <h3 class="mb-0 text-warning">
                    <?php echo $stats['nb_avis'] > 0 ? number_format($stats['moyenne_note'], 1) : '-'; ?>
                    <?php if ($stats['nb_avis'] > 0): ?>
                      <i class="bi bi-star-fill"></i>
                    <?php endif; ?>
                  </h3>
                  <small class="text-muted">Note moyenne</small>
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-2">
                  <h3 class="mb-0 text-info"><?php echo $stats['nb_avis']; ?></h3>
                  <small class="text-muted">Avis reçus</small>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Derniers avis -->
        <?php
          $avis_query = "SELECT a.note, a.commentaire, a.date_avis, d.titre 
                        FROM avis a
                        JOIN demenagement d ON a.demenagement_id = d.id
                        WHERE a.demenageur_id = ? 
                        ORDER BY a.date_avis DESC 
                        LIMIT 5";
          $avis_stmt = $mysqli->prepare($avis_query);
          $avis_stmt->bind_param("i", $profil_id);
          $avis_stmt->execute();
          $avis_result = $avis_stmt->get_result();
        ?>
        
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">Derniers avis</h5>
          </div>
          <div class="card-body">
            <?php if ($avis_result->num_rows > 0): ?>
              <?php while ($avis = $avis_result->fetch_assoc()): ?>
                <div class="mb-3 pb-3 border-bottom">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                      <strong><?php echo htmlspecialchars($avis['titre']); ?></strong>
                    </div>
                    <div class="text-warning">
                      <?php for ($i = 0; $i < $avis['note']; $i++): ?>
                        <i class="bi bi-star-fill"></i>
                      <?php endfor; ?>
                      <?php for ($i = $avis['note']; $i < 5; $i++): ?>
                        <i class="bi bi-star"></i>
                      <?php endfor; ?>
                    </div>
                  </div>
                  <?php if (!empty($avis['commentaire'])): ?>
                    <p class="mb-1 small"><?php echo nl2br(htmlspecialchars($avis['commentaire'])); ?></p>
                  <?php endif; ?>
                  <small class="text-muted"><?php echo date('d/m/Y', strtotime($avis['date_avis'])); ?></small>
                </div>
              <?php endwhile; ?>
            <?php else: ?>
              <p class="text-muted mb-0">Aucun avis pour le moment.</p>
            <?php endif; ?>
          </div>
        </div>
        <?php $avis_stmt->close(); ?>
        
        <?php else: ?>
        <div class="card">
          <div class="card-body text-center py-5">
            <i class="bi bi-info-circle display-1 text-muted mb-3"></i>
            <p class="text-muted">Ce compte n'a pas encore été activé.</p>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php
  $mysqli->close();
  include('../includes/footer.inc.php');
?>
