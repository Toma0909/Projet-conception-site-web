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
  
  // Récupérer les déménagements où le déménageur a été accepté (en cours uniquement)
  $query = "SELECT d.*, c.nom as client_nom, c.prenom as client_prenom, c.email as client_email,
            p.prix as prix_accepte, dd.date_selection
            FROM demenagement d
            JOIN demenagement_demenageur dd ON d.id = dd.demenagement_id
            JOIN compte c ON d.client_id = c.id
            JOIN proposition p ON dd.proposition_id = p.id
            WHERE dd.demenageur_id = ? AND d.statut != 'termine'
            ORDER BY d.date_demenagement ASC";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $demenageur_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $demenagements_acceptes = $result->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  
  // Récupérer les déménagements terminés
  $termine_query = "SELECT d.*, c.nom as client_nom, c.prenom as client_prenom, c.email as client_email,
                    p.prix as prix_accepte, dd.date_selection
                    FROM demenagement d
                    JOIN demenagement_demenageur dd ON d.id = dd.demenagement_id
                    JOIN compte c ON d.client_id = c.id
                    JOIN proposition p ON dd.proposition_id = p.id
                    WHERE dd.demenageur_id = ? AND d.statut = 'termine'
                    ORDER BY d.date_demenagement DESC";
  $termine_stmt = $mysqli->prepare($termine_query);
  $termine_stmt->bind_param("i", $demenageur_id);
  $termine_stmt->execute();
  $termine_result = $termine_stmt->get_result();
  $demenagements_termines = $termine_result->fetch_all(MYSQLI_ASSOC);
  $termine_stmt->close();
  
  // Récupérer les propositions en attente
  $prop_query = "SELECT d.*, c.nom as client_nom, c.prenom as client_prenom,
                 p.id as proposition_id, p.prix, p.statut, p.date_proposition
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

<div class="row mb-5">
  <div class="col-lg-10 mx-auto">
    <h1 class="mb-4">Espace Déménageur</h1>

<!-- Onglets -->
<ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="acceptes-tab" data-bs-toggle="tab" data-bs-target="#acceptes" 
            type="button" role="tab">
      Déménagements en cours <span class="badge bg-success"><?php echo count($demenagements_acceptes); ?></span>
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="termines-tab" data-bs-toggle="tab" data-bs-target="#termines" 
            type="button" role="tab">
      Déménagements terminés <span class="badge bg-secondary"><?php echo count($demenagements_termines); ?></span>
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
  <!-- Déménagements en cours -->
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
              
              <?php
                // Compter les messages non lus pour ce déménagement
                $unread_query = "SELECT COUNT(*) as nb_non_lus FROM message 
                                WHERE demenagement_id = ? AND destinataire_id = ? AND lu = 0";
                $unread_stmt = $mysqli->prepare($unread_query);
                $unread_stmt->bind_param("ii", $dem['id'], $demenageur_id);
                $unread_stmt->execute();
                $unread_result = $unread_stmt->get_result();
                $unread_data = $unread_result->fetch_assoc();
                $nb_non_lus = $unread_data['nb_non_lus'];
                $unread_stmt->close();
              ?>
              
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
                      case 'en_attente':
                        echo '<span class="badge bg-warning">En attente</span>';
                        break;
                      default:
                        echo '<span class="badge bg-secondary">' . $dem['statut'] . '</span>';
                    }
                  ?>
                </li>
                <?php if ($nb_non_lus > 0): ?>
                <li><strong>Messages :</strong> 
                  <span class="badge bg-danger"><?php echo $nb_non_lus; ?> non lu(s)</span>
                </li>
                <?php endif; ?>
              </ul>
              
              <div class="d-grid gap-2">
                <a href="detail_annonce.php?id=<?php echo $dem['id']; ?>" class="btn btn-primary btn-sm">Voir les détails</a>
                <a href="messagerie.php?id=<?php echo $dem['id']; ?>" class="btn btn-outline-primary btn-sm">
                  <i class="bi bi-chat-dots"></i> Messagerie
                  <?php if ($nb_non_lus > 0): ?>
                    <span class="badge bg-danger"><?php echo $nb_non_lus; ?></span>
                  <?php endif; ?>
                </a>
              </div>
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
        <h5>Aucun déménagement en cours</h5>
        <p>Consultez les annonces disponibles et proposez vos services pour commencer à travailler.</p>
        <a href="annonces.php" class="btn btn-primary">Voir les annonces</a>
      </div>
    <?php endif; ?>
  </div>
  
  <!-- Déménagements terminés -->
  <div class="tab-pane fade" id="termines" role="tabpanel">
    <?php if (count($demenagements_termines) > 0): ?>
      <div class="row g-4">
        <?php foreach ($demenagements_termines as $dem): 
          // Récupérer l'avis du client pour ce déménagement
          $avis_query = "SELECT a.*, c.nom, c.prenom 
                         FROM avis a
                         JOIN compte c ON a.client_id = c.id
                         WHERE a.demenagement_id = ? AND a.demenageur_id = ?";
          $avis_stmt = $mysqli->prepare($avis_query);
          $avis_stmt->bind_param("ii", $dem['id'], $demenageur_id);
          $avis_stmt->execute();
          $avis_result = $avis_stmt->get_result();
          $avis = $avis_result->fetch_assoc();
          $avis_stmt->close();
        ?>
        <div class="col-md-6 col-lg-4">
          <div class="card h-100 border-secondary">
            <div class="card-header bg-secondary text-white">
              <strong><?php echo htmlspecialchars($dem['titre']); ?></strong>
            </div>
            <div class="card-body">
              <h6 class="card-subtitle mb-2 text-muted">
                <?php echo htmlspecialchars($dem['ville_depart']); ?> → <?php echo htmlspecialchars($dem['ville_arrivee']); ?>
              </h6>
              
              <ul class="list-unstyled mb-3">
                <li><strong>Date :</strong> <?php echo date('d/m/Y', strtotime($dem['date_demenagement'])); ?></li>
                <li><strong>Client :</strong> <?php echo htmlspecialchars($dem['client_prenom'] . ' ' . $dem['client_nom']); ?></li>
                <li><strong>Prix :</strong> <span class="fw-bold"><?php echo $dem['prix_accepte']; ?> €</span></li>
                <li><span class="badge bg-success">Terminé</span></li>
              </ul>
              
              <?php if ($avis): ?>
              <div class="alert alert-light">
                <h6 class="mb-2"><i class="bi bi-star-fill text-warning"></i> Avis du client</h6>
                <div class="mb-2">
                  <?php for($i = 1; $i <= 5; $i++): ?>
                    <i class="bi bi-star<?php echo ($i <= $avis['note']) ? '-fill text-warning' : ''; ?>"></i>
                  <?php endfor; ?>
                  <strong>(<?php echo $avis['note']; ?>/5)</strong>
                </div>
                <?php if ($avis['commentaire']): ?>
                <p class="mb-0 small fst-italic">"<?php echo nl2br(htmlspecialchars($avis['commentaire'])); ?>"</p>
                <?php endif; ?>
              </div>
              <?php else: ?>
              <div class="alert alert-warning">
                <small><i class="bi bi-hourglass-split"></i> En attente de l'avis du client</small>
              </div>
              <?php endif; ?>
              
              <a href="detail_annonce.php?id=<?php echo $dem['id']; ?>" class="btn btn-outline-secondary btn-sm w-100">Voir les détails</a>
            </div>
            <div class="card-footer text-muted">
              <small>Réalisé le <?php echo date('d/m/Y', strtotime($dem['date_demenagement'])); ?></small>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="alert alert-info">
        <h5>Aucun déménagement terminé</h5>
        <p>Vos déménagements terminés apparaîtront ici avec les avis des clients.</p>
      </div>
    <?php endif; ?>
  </div>
  
  <!-- Propositions en attente -->
  <div class="tab-pane fade" id="attente" role="tabpanel">
    <?php if (count($propositions_en_attente) > 0): ?>
      <div class="row g-4">
        <?php foreach ($propositions_en_attente as $prop): 
          // Récupérer les contre-offres pour cette proposition
          $co_query = "SELECT co.*, c.nom, c.prenom, c.role 
                       FROM contre_offre co
                       JOIN compte c ON co.auteur_id = c.id
                       WHERE co.proposition_id = ? 
                       ORDER BY co.date_creation DESC";
          $co_stmt = $mysqli->prepare($co_query);
          $co_stmt->bind_param("i", $prop['proposition_id']);
          $co_stmt->execute();
          $contre_offres = $co_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
          $co_stmt->close();
          
          // Vérifier s'il y a une contre-offre du client en attente
          $co_client_attente = null;
          foreach ($contre_offres as $co) {
            if ($co['role'] == 1 && $co['statut'] == 'en_attente') {
              $co_client_attente = $co;
              break;
            }
          }
        ?>
        <div class="col-md-6 col-lg-4">
          <div class="card h-100 <?php echo $co_client_attente ? 'border-info' : 'border-warning'; ?>">
            <div class="card-header <?php echo $co_client_attente ? 'bg-info text-white' : 'bg-warning'; ?>">
              <strong><?php echo htmlspecialchars($prop['titre']); ?></strong>
            </div>
            <div class="card-body">
              <h6 class="card-subtitle mb-2 text-muted">
                <?php echo htmlspecialchars($prop['ville_depart']); ?> → <?php echo htmlspecialchars($prop['ville_arrivee']); ?>
              </h6>
              
              <ul class="list-unstyled mb-3">
                <li><strong>Date :</strong> <?php echo date('d/m/Y à H:i', strtotime($prop['date_demenagement'] . ' ' . $prop['heure_debut'])); ?></li>
                <li><strong>Client :</strong> <?php echo htmlspecialchars($prop['client_prenom'] . ' ' . $prop['client_nom']); ?></li>
                <li><strong>Votre prix :</strong> <span class="fw-bold"><?php echo number_format($prop['prix'], 2, ',', ' '); ?> €</span></li>
                <li><strong>Proposé le :</strong> <?php echo date('d/m/Y', strtotime($prop['date_proposition'])); ?></li>
              </ul>
              
              <?php if ($co_client_attente): ?>
              <div class="alert alert-info mb-3">
                <h6 class="alert-heading"><i class="bi bi-info-circle"></i> Contre-offre du client</h6>
                <p class="mb-1"><strong>Prix proposé :</strong> <?php echo number_format($co_client_attente['prix_propose'], 2, ',', ' '); ?> €</p>
                <?php if (!empty($co_client_attente['commentaire'])): ?>
                <p class="mb-0 small"><?php echo nl2br(htmlspecialchars($co_client_attente['commentaire'])); ?></p>
                <?php endif; ?>
                <hr>
                <div class="d-grid gap-2">
                  <form method="POST" action="tt_repondre_contre_offre.php" class="d-inline">
                    <input type="hidden" name="contre_offre_id" value="<?php echo $co_client_attente['id']; ?>">
                    <input type="hidden" name="demenagement_id" value="<?php echo $prop['id']; ?>">
                    <input type="hidden" name="action" value="accepter">
                    <button type="submit" class="btn btn-success btn-sm w-100">
                      <i class="bi bi-check-circle"></i> Accepter cette contre-offre
                    </button>
                  </form>
                  <form method="POST" action="tt_repondre_contre_offre.php" class="d-inline">
                    <input type="hidden" name="contre_offre_id" value="<?php echo $co_client_attente['id']; ?>">
                    <input type="hidden" name="demenagement_id" value="<?php echo $prop['id']; ?>">
                    <input type="hidden" name="action" value="refuser">
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                      <i class="bi bi-x-circle"></i> Refuser
                    </button>
                  </form>
                  <button type="button" class="btn btn-outline-warning btn-sm w-100" 
                          data-bs-toggle="modal" data-bs-target="#contreOffreModal-<?php echo $prop['proposition_id']; ?>">
                    <i class="bi bi-chat-square-text"></i> Faire une contre-contre-offre
                  </button>
                </div>
              </div>
              <?php endif; ?>
              
              <?php if (count($contre_offres) > 0): ?>
              <button class="btn btn-sm btn-outline-secondary w-100 mb-2" type="button" 
                      data-bs-toggle="collapse" data-bs-target="#historique-<?php echo $prop['proposition_id']; ?>">
                <i class="bi bi-clock-history"></i> Historique des négociations (<?php echo count($contre_offres); ?>)
              </button>
              
              <div class="collapse" id="historique-<?php echo $prop['proposition_id']; ?>">
                <div class="card card-body mb-2">
                  <?php foreach ($contre_offres as $co): ?>
                  <div class="mb-2 pb-2 border-bottom">
                    <div class="d-flex justify-content-between">
                      <strong><?php echo $co['role'] == 1 ? 'Client' : 'Vous'; ?></strong>
                      <span class="text-primary"><?php echo number_format($co['prix_propose'], 2, ',', ' '); ?> €</span>
                    </div>
                    <span class="badge badge-sm <?php 
                      echo $co['statut'] == 'accepte' ? 'bg-success' : 
                           ($co['statut'] == 'refuse' ? 'bg-danger' : 'bg-warning'); 
                    ?>">
                      <?php 
                        echo $co['statut'] == 'accepte' ? 'Acceptée' : 
                             ($co['statut'] == 'refuse' ? 'Refusée' : 'En attente'); 
                      ?>
                    </span>
                    <?php if (!empty($co['commentaire'])): ?>
                    <p class="mb-1 small"><?php echo nl2br(htmlspecialchars($co['commentaire'])); ?></p>
                    <?php endif; ?>
                    <small class="text-muted"><?php echo date('d/m/Y à H:i', strtotime($co['date_creation'])); ?></small>
                  </div>
                  <?php endforeach; ?>
                </div>
              </div>
              <?php endif; ?>
              
              <a href="detail_annonce.php?id=<?php echo $prop['id']; ?>" class="btn btn-primary btn-sm w-100">Voir les détails</a>
            </div>
          </div>
        </div>
        
        <!-- Modal pour contre-offre du déménageur -->
        <div class="modal fade" id="contreOffreModal-<?php echo $prop['proposition_id']; ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="POST" action="tt_contre_offre.php">
                <div class="modal-header">
                  <h5 class="modal-title">Faire une contre-proposition</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" name="proposition_id" value="<?php echo $prop['proposition_id']; ?>">
                  <input type="hidden" name="demenagement_id" value="<?php echo $prop['id']; ?>">
                  
                  <div class="alert alert-info">
                    <strong>Votre prix initial :</strong> <?php echo number_format($prop['prix'], 2, ',', ' '); ?> €<br>
                    <?php if ($co_client_attente): ?>
                    <strong>Contre-offre du client :</strong> <?php echo number_format($co_client_attente['prix_propose'], 2, ',', ' '); ?> €
                    <?php endif; ?>
                  </div>
                  
                  <div class="mb-3">
                    <label for="prix_propose-<?php echo $prop['proposition_id']; ?>" class="form-label">Nouveau prix proposé (€) *</label>
                    <input type="number" step="0.01" class="form-control" 
                           id="prix_propose-<?php echo $prop['proposition_id']; ?>" 
                           name="prix_propose" 
                           <?php if ($co_client_attente): ?>
                           min="<?php echo $co_client_attente['prix_propose']; ?>"
                           <?php endif; ?>
                           required>
                    <div class="form-text">Proposez un prix entre la contre-offre du client et votre prix initial</div>
                  </div>
                  
                  <div class="mb-3">
                    <label for="commentaire-<?php echo $prop['proposition_id']; ?>" class="form-label">Message (optionnel)</label>
                    <textarea class="form-control" id="commentaire-<?php echo $prop['proposition_id']; ?>" 
                              name="commentaire" rows="3" 
                              placeholder="Expliquez votre proposition..."></textarea>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                  <button type="submit" class="btn btn-primary">Envoyer</button>
                </div>
              </form>
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
  </div>
</div>

<?php
  $mysqli->close();
  include('../includes/footer.inc.php');
?>
