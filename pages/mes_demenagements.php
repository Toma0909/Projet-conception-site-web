<?php
  session_start();
  
  // Vérifier si l'utilisateur est connecté et est un client
  if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true || $_SESSION['role'] != 1) {
    $_SESSION['erreur'] = "Accès non autorisé.";
    header('Location: ../auth/connexion.php');
    exit();
  }
  
  $client_id = $_SESSION['user_id'];
  
  // Connexion BDD
  require_once("../config/param.inc.php");
  $mysqli = new mysqli($host, $login, $passwd, $dbname);
  
  if ($mysqli->connect_error) {
    die("Erreur de connexion à la base de données");
  }
  
  // Récupérer les déménagements du client
  $query = "SELECT d.* 
            FROM demenagement d 
            WHERE d.client_id = ? 
            ORDER BY d.date_creation DESC";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $client_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $demenagements = $result->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  
  $titre = "Mes déménagements";
  include('../includes/header.inc.php');
  include('../includes/menu.inc.php');
  include('../includes/message.inc.php');
?>

<div class="row mb-5">
  <div class="col-lg-10 mx-auto">
    <div class="row mb-4">
      <div class="col-md-8">
        <h1>Mes demandes de déménagement</h1>
      </div>
      <div class="col-md-4 text-md-end">
        <a href="creer_demenagement.php" class="btn btn-primary">
          <i class="bi bi-plus-circle"></i> Nouvelle demande
        </a>
      </div>
    </div>

<?php if (count($demenagements) > 0): ?>
  <div class="row g-4">
    <?php foreach ($demenagements as $dem): 
      // Compter les propositions
      $count_query = "SELECT COUNT(*) as total, 
                      SUM(CASE WHEN statut = 'accepte' THEN 1 ELSE 0 END) as acceptees 
                      FROM proposition WHERE demenagement_id = ?";
      $count_stmt = $mysqli->prepare($count_query);
      $count_stmt->bind_param("i", $dem['id']);
      $count_stmt->execute();
      $count_result = $count_stmt->get_result();
      $counts = $count_result->fetch_assoc();
      $count_stmt->close();
      
      // Assurer que acceptees n'est pas NULL
      $counts['acceptees'] = $counts['acceptees'] ?? 0;
    ?>
    <div class="col-md-6 col-lg-4">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <h5 class="card-title"><?php echo htmlspecialchars($dem['titre']); ?></h5>
            <?php 
              $badge_class = '';
              $badge_text = '';
              switch($dem['statut']) {
                case 'en_attente':
                  $badge_class = 'bg-warning';
                  $badge_text = 'En attente';
                  break;
                case 'en_cours':
                  $badge_class = 'bg-info';
                  $badge_text = 'En cours';
                  break;
                case 'termine':
                  $badge_class = 'bg-success';
                  $badge_text = 'Terminé';
                  break;
                case 'annule':
                  $badge_class = 'bg-danger';
                  $badge_text = 'Annulé';
                  break;
              }
            ?>
            <span class="badge <?php echo $badge_class; ?>"><?php echo $badge_text; ?></span>
          </div>
          
          <?php
            // Compter les messages non lus pour ce déménagement
            $unread_query = "SELECT COUNT(*) as nb_non_lus FROM message 
                            WHERE demenagement_id = ? AND destinataire_id = ? AND lu = 0";
            $unread_stmt = $mysqli->prepare($unread_query);
            $unread_stmt->bind_param("ii", $dem['id'], $client_id);
            $unread_stmt->execute();
            $unread_result = $unread_stmt->get_result();
            $unread_data = $unread_result->fetch_assoc();
            $nb_non_lus = $unread_data['nb_non_lus'];
            $unread_stmt->close();
            
            // Récupérer les déménageurs avec qui il y a eu des échanges
            $demenageurs_query = "SELECT DISTINCT 
                                  CASE 
                                    WHEN m.expediteur_id = ? THEN m.destinataire_id
                                    ELSE m.expediteur_id
                                  END as demenageur_id,
                                  c.nom, c.prenom
                                  FROM message m
                                  JOIN compte c ON (
                                    CASE 
                                      WHEN m.expediteur_id = ? THEN m.destinataire_id = c.id
                                      ELSE m.expediteur_id = c.id
                                    END
                                  )
                                  WHERE m.demenagement_id = ? 
                                  AND (m.expediteur_id = ? OR m.destinataire_id = ?)";
            $demenageurs_stmt = $mysqli->prepare($demenageurs_query);
            $demenageurs_stmt->bind_param("iiiii", $client_id, $client_id, $dem['id'], $client_id, $client_id);
            $demenageurs_stmt->execute();
            $demenageurs_result = $demenageurs_stmt->get_result();
            $demenageurs = $demenageurs_result->fetch_all(MYSQLI_ASSOC);
            $demenageurs_stmt->close();
          ?>
          
          <h6 class="card-subtitle mb-2 text-muted">
            <?php echo htmlspecialchars($dem['ville_depart']); ?> → <?php echo htmlspecialchars($dem['ville_arrivee']); ?>
          </h6>
          
          <p class="card-text">
            <?php echo nl2br(htmlspecialchars(substr($dem['description'], 0, 100))); ?>
            <?php if(strlen($dem['description']) > 100) echo '...'; ?>
          </p>
          
          <ul class="list-unstyled mb-3">
            <li><strong>Date :</strong> <?php echo date('d/m/Y à H:i', strtotime($dem['date_demenagement'] . ' ' . $dem['heure_debut'])); ?></li>
            <li><strong>Propositions :</strong> 
              <span class="badge bg-primary"><?php echo $counts['total']; ?></span>
              <?php if ($counts['acceptees'] > 0): ?>
                <span class="badge bg-success"><?php echo $counts['acceptees']; ?> acceptée(s)</span>
              <?php endif; ?>
            </li>
            <?php if ($nb_non_lus > 0): ?>
            <li><strong>Messages :</strong> 
              <span class="badge bg-danger"><?php echo $nb_non_lus; ?> non lu(s)</span>
            </li>
            <?php endif; ?>
          </ul>
          
          <?php if (count($demenageurs) > 0): ?>
          <div class="mb-3">
            <strong>Conversations :</strong>
            <div class="list-group mt-2">
              <?php foreach($demenageurs as $dem_user): ?>
                <a href="messagerie.php?id=<?php echo $dem['id']; ?>&demenageur_id=<?php echo $dem_user['demenageur_id']; ?>" 
                   class="list-group-item list-group-item-action py-1 small">
                  <i class="bi bi-chat-dots"></i> <?php echo htmlspecialchars($dem_user['prenom'] . ' ' . $dem_user['nom']); ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
          
          <div class="d-grid gap-2">
            <a href="detail_annonce.php?id=<?php echo $dem['id']; ?>" class="btn btn-primary btn-sm">Voir les détails</a>
            <?php if ($counts['total'] > 0): ?>
              <a href="propositions.php?id=<?php echo $dem['id']; ?>" class="btn btn-info btn-sm">
                <i class="bi bi-inbox"></i> Voir les propositions (<?php echo $counts['total']; ?>)
              </a>
            <?php endif; ?>
            
            <?php if ($dem['statut'] == 'en_cours'): ?>
              <form method="POST" action="tt_valider_demenagement.php" class="d-inline" onsubmit="return confirm('Confirmez-vous que le déménagement a bien été réalisé ?');">
                <input type="hidden" name="demenagement_id" value="<?php echo $dem['id']; ?>">
                <button type="submit" class="btn btn-success btn-sm w-100">
                  <i class="bi bi-check-circle-fill"></i> Marquer comme terminé
                </button>
              </form>
            <?php endif; ?>
            
            <?php if ($dem['statut'] == 'termine'): ?>
              <a href="noter_demenageurs.php?id=<?php echo $dem['id']; ?>" class="btn btn-warning btn-sm">
                <i class="bi bi-star-fill"></i> Noter les déménageurs
              </a>
            <?php endif; ?>
            
            <?php if ($counts['acceptees'] == 0): ?>
              <a href="modifier_demenagement.php?id=<?php echo $dem['id']; ?>" class="btn btn-outline-secondary btn-sm">Modifier</a>
            <?php else: ?>
              <span class="d-inline-block" tabindex="0" 
                    data-bs-toggle="tooltip" 
                    data-bs-placement="top" 
                    title="Impossible de modifier l'annonce car vous avez déjà accepté un déménageur">
                <button class="btn btn-outline-secondary btn-sm w-100 btn-disabled-no-events" disabled>
                  Modifier
                </button>
              </span>
            <?php endif; ?>
            
            <?php if ($dem['statut'] != 'termine'): ?>
              <a href="confirmer_suppression.php?id=<?php echo $dem['id']; ?>" class="btn btn-outline-danger btn-sm">Supprimer</a>
            <?php endif; ?>
          </div>
        </div>
        <div class="card-footer text-muted">
          <small>Créé le <?php echo date('d/m/Y', strtotime($dem['date_creation'])); ?></small>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <div class="alert alert-info">
    <h5>Vous n'avez pas encore créé de demande de déménagement</h5>
    <p>Commencez par créer votre première demande pour trouver des déménageurs qualifiés.</p>
    <a href="creer_demenagement.php" class="btn btn-primary">Créer ma première demande</a>
  </div>
<?php endif; ?>
  </div>
</div>

<?php
  $mysqli->close();
  include('../includes/footer.inc.php');
?>
