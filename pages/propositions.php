<?php
  session_start();
  
  // Vérifier si l'utilisateur est connecté et est un client
  if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true || $_SESSION['role'] != 1) {
    $_SESSION['erreur'] = "Accès non autorisé.";
    header('Location: ../auth/connexion.php');
    exit();
  }
  
  // Récupérer l'ID du déménagement
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
    die("Erreur de connexion à la base de données");
  }
  
  // Vérifier que le déménagement appartient au client
  $query = "SELECT * FROM demenagement WHERE id = ? AND client_id = ?";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("ii", $demenagement_id, $client_id);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows === 0) {
    $_SESSION['erreur'] = "Déménagement non trouvé.";
    $stmt->close();
    $mysqli->close();
    header('Location: mes_demenagements.php');
    exit();
  }
  
  $demenagement = $result->fetch_assoc();
  $stmt->close();
  
  // Récupérer toutes les propositions avec les infos du déménageur
  $query = "SELECT p.*, c.nom, c.prenom, c.email 
            FROM proposition p 
            JOIN compte c ON p.demenageur_id = c.id 
            WHERE p.demenagement_id = ? 
            ORDER BY p.date_proposition DESC";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $demenagement_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $propositions = $result->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  
  $titre = "Propositions - " . htmlspecialchars($demenagement['titre']);
  include('../includes/header.inc.php');
  include('../includes/menu.inc.php');
  include('../includes/message.inc.php');
?>

<div class="row mb-5">
  <div class="col-lg-10 mx-auto">
    <div class="row mb-4">
      <div class="col-md-8">
        <h1>Propositions reçues</h1>
        <p class="lead"><?php echo htmlspecialchars($demenagement['titre']); ?></p>
      </div>
      <div class="col-md-4 text-md-end">
        <a href="mes_demenagements.php" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-left"></i> Retour
        </a>
      </div>
    </div>

  <?php if (count($propositions) > 0): ?>
    <div class="row g-4">
      <?php foreach ($propositions as $prop): 
        // Récupérer l'historique des contre-offres pour cette proposition
        $co_query = "SELECT co.*, c.nom, c.prenom, c.role 
                     FROM contre_offre co
                     JOIN compte c ON co.auteur_id = c.id
                     WHERE co.proposition_id = ? 
                     ORDER BY co.date_creation ASC";
        $co_stmt = $mysqli->prepare($co_query);
        $co_stmt->bind_param("i", $prop['id']);
        $co_stmt->execute();
        $contre_offres = $co_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $co_stmt->close();
        
        // Déterminer le prix actuel (dernière contre-offre acceptée ou prix initial)
        $prix_actuel = $prop['prix'];
        $derniere_co = null;
        foreach ($contre_offres as $co) {
          if ($co['statut'] == 'accepte') {
            $prix_actuel = $co['prix_propose'];
            $derniere_co = $co;
          }
        }
        
        // Compter les contre-offres en attente
        $co_en_attente = array_filter($contre_offres, function($co) {
          return $co['statut'] == 'en_attente';
        });
      ?>
      <div class="col-lg-6">
        <div class="card h-100 <?php echo $prop['statut'] == 'accepte' ? 'border-success' : ''; ?>">
          <div class="card-header <?php echo $prop['statut'] == 'accepte' ? 'bg-success text-white' : 'bg-light'; ?>">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="mb-0">
                <i class="bi bi-person-circle"></i>
                <?php echo htmlspecialchars($prop['prenom'] . ' ' . $prop['nom']); ?>
              </h5>
              <?php
                $badge_class = '';
                $badge_text = '';
                switch($prop['statut']) {
                  case 'en_attente':
                    $badge_class = 'bg-warning';
                    $badge_text = 'En attente';
                    break;
                  case 'accepte':
                    $badge_class = 'bg-success';
                    $badge_text = 'Acceptée';
                    break;
                  case 'refuse':
                    $badge_class = 'bg-danger';
                    $badge_text = 'Refusée';
                    break;
                }
              ?>
              <span class="badge <?php echo $badge_class; ?>"><?php echo $badge_text; ?></span>
            </div>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>Prix proposé :</strong>
                <h4 class="mb-0 text-primary"><?php echo number_format($prix_actuel, 2, ',', ' '); ?> €</h4>
              </div>
              <?php if ($prix_actuel != $prop['prix']): ?>
                <small class="text-muted">
                  Prix initial : <del><?php echo number_format($prop['prix'], 2, ',', ' '); ?> €</del>
                </small>
              <?php endif; ?>
            </div>
            
            <?php if (!empty($prop['commentaire'])): ?>
            <div class="mb-3">
              <strong>Commentaire :</strong>
              <p class="mb-0"><?php echo nl2br(htmlspecialchars($prop['commentaire'])); ?></p>
            </div>
            <?php endif; ?>
            
            <div class="mb-3">
              <small class="text-muted">
                <i class="bi bi-calendar"></i> Proposé le <?php echo date('d/m/Y à H:i', strtotime($prop['date_proposition'])); ?>
              </small>
            </div>
            
            <?php if (count($contre_offres) > 0): ?>
            <div class="mb-3">
              <button class="btn btn-sm btn-outline-info w-100" type="button" data-bs-toggle="collapse" data-bs-target="#historique-<?php echo $prop['id']; ?>">
                <i class="bi bi-clock-history"></i> Voir l'historique (<?php echo count($contre_offres); ?> négociation<?php echo count($contre_offres) > 1 ? 's' : ''; ?>)
              </button>
              
              <div class="collapse mt-2" id="historique-<?php echo $prop['id']; ?>">
                <div class="card card-body">
                  <div class="timeline">
                    <?php foreach ($contre_offres as $co): ?>
                    <div class="mb-2 pb-2 border-bottom">
                      <div class="d-flex justify-content-between align-items-start">
                        <div>
                          <strong>
                            <?php echo $co['role'] == 1 ? 'Vous' : htmlspecialchars($co['prenom'] . ' ' . $co['nom']); ?>
                          </strong>
                          <span class="badge badge-sm <?php 
                            echo $co['statut'] == 'accepte' ? 'bg-success' : 
                                 ($co['statut'] == 'refuse' ? 'bg-danger' : 'bg-warning'); 
                          ?>">
                            <?php 
                              echo $co['statut'] == 'accepte' ? 'Acceptée' : 
                                   ($co['statut'] == 'refuse' ? 'Refusée' : 'En attente'); 
                            ?>
                          </span>
                        </div>
                        <strong class="text-primary"><?php echo number_format($co['prix_propose'], 2, ',', ' '); ?> €</strong>
                      </div>
                      <?php if (!empty($co['commentaire'])): ?>
                      <p class="mb-1 small"><?php echo nl2br(htmlspecialchars($co['commentaire'])); ?></p>
                      <?php endif; ?>
                      <small class="text-muted"><?php echo date('d/m/Y à H:i', strtotime($co['date_creation'])); ?></small>
                    </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>
            </div>
            <?php endif; ?>
            
            <?php if ($prop['statut'] == 'en_attente'): ?>
            <div class="d-grid gap-2">
              <?php if (count($co_en_attente) == 0): ?>
              <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#contreOffreModal-<?php echo $prop['id']; ?>">
                <i class="bi bi-chat-square-text"></i> Faire une contre-offre
              </button>
              <?php else: ?>
              <button class="btn btn-outline-warning" disabled>
                <i class="bi bi-hourglass-split"></i> Contre-offre en attente
              </button>
              <?php endif; ?>
              
              <form method="POST" action="tt_accepter_proposition.php" class="d-inline">
                <input type="hidden" name="proposition_id" value="<?php echo $prop['id']; ?>">
                <input type="hidden" name="demenagement_id" value="<?php echo $demenagement_id; ?>">
                <button type="submit" class="btn btn-success w-100">
                  <i class="bi bi-check-circle"></i> Accepter cette proposition
                </button>
              </form>
              
              <button type="button" class="btn btn-outline-danger">
                <i class="bi bi-x-circle"></i> Refuser
              </button>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      
      <!-- Modal pour contre-offre -->
      <div class="modal fade" id="contreOffreModal-<?php echo $prop['id']; ?>" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <form method="POST" action="tt_contre_offre.php">
              <div class="modal-header">
                <h5 class="modal-title">Faire une contre-offre</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <input type="hidden" name="proposition_id" value="<?php echo $prop['id']; ?>">
                <input type="hidden" name="demenagement_id" value="<?php echo $demenagement_id; ?>">
                
                <div class="alert alert-info">
                  <strong>Prix actuel :</strong> <?php echo number_format($prix_actuel, 2, ',', ' '); ?> €
                </div>
                
                <div class="mb-3">
                  <label for="prix_propose-<?php echo $prop['id']; ?>" class="form-label">Votre contre-proposition (€) *</label>
                  <input type="number" step="0.01" class="form-control" 
                         id="prix_propose-<?php echo $prop['id']; ?>" 
                         name="prix_propose" 
                         max="<?php echo $prix_actuel; ?>"
                         required>
                  <div class="form-text">Le prix doit être inférieur au prix actuel</div>
                </div>
                
                <div class="mb-3">
                  <label for="commentaire-<?php echo $prop['id']; ?>" class="form-label">Message (optionnel)</label>
                  <textarea class="form-control" id="commentaire-<?php echo $prop['id']; ?>" 
                            name="commentaire" rows="3" 
                            placeholder="Expliquez votre contre-proposition..."></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-primary">Envoyer la contre-offre</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="alert alert-info">
      <h5>Aucune proposition pour le moment</h5>
      <p>Les déménageurs peuvent consulter votre annonce et vous faire des propositions.</p>
    </div>
  <?php endif; ?>
  </div>
</div>

<?php
  $mysqli->close();
  include('../includes/footer.inc.php');
?>
