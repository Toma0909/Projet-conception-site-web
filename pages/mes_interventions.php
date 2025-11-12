<?php
  session_start();
  
  // Vérifier si l'utilisateur est connecté et est un déménageur
  if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true || $_SESSION['role'] != 2) {
    $_SESSION['erreur'] = "Accès non autorisé.";
    header('Location: ../auth/connexion.php');
    exit();
  }
  
  $demenageur_id = $_SESSION['user_id'];
  
  // Connexion BDD
  require_once("../config/param.inc.php");
  $mysqli = new mysqli($host, $login, $passwd, $dbname);
  
  if ($mysqli->connect_error) {
    die("Erreur de connexion à la base de données");
  }
  
  // Récupérer les déménagements où le déménageur a été accepté
  $query = "SELECT d.*, c.nom as client_nom, c.prenom as client_prenom, c.email as client_email,
            p.prix as prix_accepte, dd.date_selection
            FROM demenagement d
            JOIN demenagement_demenageur dd ON d.id = dd.demenagement_id
            JOIN compte c ON d.client_id = c.id
            JOIN proposition p ON dd.proposition_id = p.id
            WHERE dd.demenageur_id = ?
            ORDER BY d.date_demenagement ASC";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $demenageur_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $demenagements_acceptes = $result->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  
  // Récupérer les propositions en attente
  $prop_query = "SELECT d.*, c.nom as client_nom, c.prenom as client_prenom,
                 p.prix, p.statut, p.date_proposition
                 FROM proposition p
                 JOIN demenagement d ON p.demenagement_id = d.id
                 JOIN compte c ON d.client_id = c.id
                 WHERE p.demenageur_id = ? AND p.statut = 'en_attente'
                 ORDER BY p.date_proposition DESC";
  $prop_stmt = $mysqli->prepare($prop_query);
  $prop_stmt->bind_param("i", $demenageur_id);
  $prop_stmt->execute();
  $prop_result = $prop_stmt->get_result();
  $propositions_en_attente = $prop_result->fetch_all(MYSQLI_ASSOC);
  $prop_stmt->close();
  
  $titre = "Mes interventions";
  include('../includes/header.inc.php');
  include('../includes/menu.inc.php');
  include('../includes/message.inc.php');
?>

<h1>Espace Déménageur</h1>

<!-- Onglets -->
<ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="acceptes-tab" data-bs-toggle="tab" data-bs-target="#acceptes" 
            type="button" role="tab">
      Déménagements acceptés <span class="badge bg-success"><?php echo count($demenagements_acceptes); ?></span>
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="attente-tab" data-bs-toggle="tab" data-bs-target="#attente" 
            type="button" role="tab">
      Propositions en attente <span class="badge bg-warning"><?php echo count($propositions_en_attente); ?></span>
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="annonces-tab" data-bs-toggle="tab" data-bs-target="#annonces" 
            type="button" role="tab">
      Toutes les annonces
    </button>
  </li>
</ul>

<div class="tab-content" id="myTabContent">
  <!-- Déménagements acceptés -->
  <div class="tab-pane fade show active" id="acceptes" role="tabpanel">
    <?php if (count($demenagements_acceptes) > 0): ?>
      <div class="row g-4">
        <?php foreach ($demenagements_acceptes as $dem): ?>
        <div class="col-md-6 col-lg-4">
          <div class="card h-100 border-success">
            <div class="card-header bg-success text-white">
              <strong><?php echo htmlspecialchars($dem['titre']); ?></strong>
            </div>
            <div class="card-body">
              <h6 class="card-subtitle mb-2 text-muted">
                <?php echo htmlspecialchars($dem['ville_depart']); ?> → <?php echo htmlspecialchars($dem['ville_arrivee']); ?>
              </h6>
              
              <ul class="list-unstyled mb-3">
                <li><strong>Date :</strong> <?php echo date('d/m/Y à H:i', strtotime($dem['date_demenagement'] . ' ' . $dem['heure_debut'])); ?></li>
                <li><strong>Client :</strong> <?php echo htmlspecialchars($dem['client_prenom'] . ' ' . $dem['client_nom']); ?></li>
                <li><strong>Email :</strong> <?php echo htmlspecialchars($dem['client_email']); ?></li>
                <li><strong>Prix accepté :</strong> <span class="text-success fw-bold"><?php echo $dem['prix_accepte']; ?> €</span></li>
                <li><strong>Statut :</strong> 
                  <?php 
                    switch($dem['statut']) {
                      case 'en_cours':
                        echo '<span class="badge bg-info">En cours</span>';
                        break;
                      case 'termine':
                        echo '<span class="badge bg-success">Terminé</span>';
                        break;
                      default:
                        echo '<span class="badge bg-secondary">' . $dem['statut'] . '</span>';
                    }
                  ?>
                </li>
              </ul>
              
              <a href="detail_annonce.php?id=<?php echo $dem['id']; ?>" class="btn btn-primary btn-sm w-100">Voir les détails</a>
            </div>
            <div class="card-footer text-muted">
              <small>Accepté le <?php echo date('d/m/Y', strtotime($dem['date_selection'])); ?></small>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="alert alert-info">
        <h5>Aucun déménagement accepté pour le moment</h5>
        <p>Consultez les annonces disponibles et proposez vos services pour commencer à travailler.</p>
        <a href="annonces.php" class="btn btn-primary">Voir les annonces</a>
      </div>
    <?php endif; ?>
  </div>
  
  <!-- Propositions en attente -->
  <div class="tab-pane fade" id="attente" role="tabpanel">
    <?php if (count($propositions_en_attente) > 0): ?>
      <div class="row g-4">
        <?php foreach ($propositions_en_attente as $prop): ?>
        <div class="col-md-6 col-lg-4">
          <div class="card h-100 border-warning">
            <div class="card-header bg-warning">
              <strong><?php echo htmlspecialchars($prop['titre']); ?></strong>
            </div>
            <div class="card-body">
              <h6 class="card-subtitle mb-2 text-muted">
                <?php echo htmlspecialchars($prop['ville_depart']); ?> → <?php echo htmlspecialchars($prop['ville_arrivee']); ?>
              </h6>
              
              <ul class="list-unstyled mb-3">
                <li><strong>Date :</strong> <?php echo date('d/m/Y à H:i', strtotime($prop['date_demenagement'] . ' ' . $prop['heure_debut'])); ?></li>
                <li><strong>Votre prix :</strong> <span class="fw-bold"><?php echo $prop['prix']; ?> €</span></li>
                <li><strong>Proposé le :</strong> <?php echo date('d/m/Y à H:i', strtotime($prop['date_proposition'])); ?></li>
              </ul>
              
              <a href="detail_annonce.php?id=<?php echo $prop['id']; ?>" class="btn btn-primary btn-sm w-100">Voir les détails</a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="alert alert-info">
        <h5>Aucune proposition en attente</h5>
        <p>Consultez les annonces disponibles pour faire de nouvelles propositions.</p>
        <a href="annonces.php" class="btn btn-primary">Voir les annonces</a>
      </div>
    <?php endif; ?>
  </div>
  
  <!-- Toutes les annonces -->
  <div class="tab-pane fade" id="annonces" role="tabpanel">
    <p class="lead">Consultez toutes les annonces de déménagement disponibles</p>
    <a href="annonces.php" class="btn btn-primary">Voir toutes les annonces</a>
  </div>
</div>

<?php
  $mysqli->close();
  include('../includes/footer.inc.php');
?>
