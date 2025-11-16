<?php
  session_start();
  
  // Vérifier qu'un ID est fourni
  if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['erreur'] = "Annonce non trouvée.";
    header('Location: annonces.php');
    exit();
  }
  
  $annonce_id = intval($_GET['id']);
  
  // Connexion BDD
  require_once("../config/param.inc.php");
  $mysqli = new mysqli($host, $login, $passwd, $dbname);
  
  if ($mysqli->connect_error) {
    die("Erreur de connexion à la base de données");
  }
  
  // Récupérer l'annonce
  $query = "SELECT d.*, c.prenom, c.nom, c.email 
            FROM demenagement d 
            JOIN compte c ON d.client_id = c.id 
            WHERE d.id = ?";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $annonce_id);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows == 0) {
    $_SESSION['erreur'] = "Annonce non trouvée.";
    $stmt->close();
    $mysqli->close();
    header('Location: annonces.php');
    exit();
  }
  
  $annonce = $result->fetch_assoc();
  $stmt->close();
  
  // Vérifier s'il y a des propositions acceptées
  $acceptee_query = "SELECT COUNT(*) as nb_acceptees FROM proposition WHERE demenagement_id = ? AND statut = 'accepte'";
  $acceptee_stmt = $mysqli->prepare($acceptee_query);
  $acceptee_stmt->bind_param("i", $annonce_id);
  $acceptee_stmt->execute();
  $acceptee_result = $acceptee_stmt->get_result();
  $nb_acceptees = $acceptee_result->fetch_assoc()['nb_acceptees'];
  $acceptee_stmt->close();
  
  // Récupérer les images
  $images_query = "SELECT * FROM demenagement_image WHERE demenagement_id = ?";
  $images_stmt = $mysqli->prepare($images_query);
  $images_stmt->bind_param("i", $annonce_id);
  $images_stmt->execute();
  $images_result = $images_stmt->get_result();
  $images = $images_result->fetch_all(MYSQLI_ASSOC);
  $images_stmt->close();
  
  // Récupérer les propositions si l'utilisateur est le client propriétaire
  $propositions = [];
  if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $annonce['client_id']) {
    $prop_query = "SELECT p.*, c.nom, c.prenom, c.email 
                   FROM proposition p 
                   JOIN compte c ON p.demenageur_id = c.id 
                   WHERE p.demenagement_id = ? 
                   ORDER BY p.date_proposition DESC";
    $prop_stmt = $mysqli->prepare($prop_query);
    $prop_stmt->bind_param("i", $annonce_id);
    $prop_stmt->execute();
    $prop_result = $prop_stmt->get_result();
    $propositions = $prop_result->fetch_all(MYSQLI_ASSOC);
    $prop_stmt->close();
  }
  
  // Vérifier si l'utilisateur déménageur a déjà fait une proposition
  $user_proposition = null;
  if (isset($_SESSION['user_id']) && $_SESSION['role'] == 2) {
    $user_prop_query = "SELECT * FROM proposition WHERE demenagement_id = ? AND demenageur_id = ?";
    $user_prop_stmt = $mysqli->prepare($user_prop_query);
    $user_prop_stmt->bind_param("ii", $annonce_id, $_SESSION['user_id']);
    $user_prop_stmt->execute();
    $user_prop_result = $user_prop_stmt->get_result();
    if ($user_prop_result->num_rows > 0) {
      $user_proposition = $user_prop_result->fetch_assoc();
    }
    $user_prop_stmt->close();
  }
  
  $titre = $annonce['titre'];
  include('../includes/header.inc.php');
  include('../includes/menu.inc.php');
  include('../includes/message.inc.php');
?>

<div class="row mb-5">
  <div class="col-lg-10 mx-auto">
    <div class="row">
      <div class="col-lg-8">
        <h1><?php echo htmlspecialchars($annonce['titre']); ?></h1>
    
    <div class="card mb-4">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Informations générales</h5>
      </div>
      <div class="card-body">
        <div class="row mb-3">
          <div class="col-md-6">
            <strong>Date du déménagement :</strong><br>
            <?php echo date('d/m/Y', strtotime($annonce['date_demenagement'])); ?>
          </div>
          <div class="col-md-6">
            <strong>Heure de début :</strong><br>
            <?php echo date('H:i', strtotime($annonce['heure_debut'])); ?>
          </div>
        </div>
        
        <div class="row mb-3">
          <div class="col-md-6">
            <strong>Ville de départ :</strong><br>
            <?php echo htmlspecialchars($annonce['ville_depart']); ?>
          </div>
          <div class="col-md-6">
            <strong>Ville d'arrivée :</strong><br>
            <?php echo htmlspecialchars($annonce['ville_arrivee']); ?>
          </div>
        </div>
        
        <div class="mb-3">
          <strong>Description :</strong><br>
          <p><?php echo nl2br(htmlspecialchars($annonce['description'])); ?></p>
        </div>
        
        <div class="row mb-3">
          <div class="col-md-4">
            <strong>Nombre de déménageurs :</strong><br>
            <?php echo $annonce['nombre_demenageurs']; ?>
          </div>
          <?php if ($annonce['volume']): ?>
          <div class="col-md-4">
            <strong>Volume :</strong><br>
            <?php echo $annonce['volume']; ?> m³
          </div>
          <?php endif; ?>
          <?php if ($annonce['poids']): ?>
          <div class="col-md-4">
            <strong>Poids :</strong><br>
            <?php echo $annonce['poids']; ?> kg
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    
    <div class="card mb-4">
      <div class="card-header bg-info text-white">
        <h5 class="mb-0">Détails du logement</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <h6>Départ</h6>
            <ul>
              <li><strong>Type :</strong> <?php echo ucfirst($annonce['depart_type']); ?></li>
              <?php if ($annonce['depart_type'] == 'appartement'): ?>
                <li><strong>Étage :</strong> <?php echo $annonce['depart_etage']; ?></li>
                <li><strong>Ascenseur :</strong> <?php echo $annonce['depart_ascenseur'] ? 'Oui' : 'Non'; ?></li>
              <?php endif; ?>
            </ul>
          </div>
          <div class="col-md-6">
            <h6>Arrivée</h6>
            <ul>
              <li><strong>Type :</strong> <?php echo ucfirst($annonce['arrivee_type']); ?></li>
              <?php if ($annonce['arrivee_type'] == 'appartement'): ?>
                <li><strong>Étage :</strong> <?php echo $annonce['arrivee_etage']; ?></li>
                <li><strong>Ascenseur :</strong> <?php echo $annonce['arrivee_ascenseur'] ? 'Oui' : 'Non'; ?></li>
              <?php endif; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>
    
    <?php if (count($images) > 0): ?>
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Photos</h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <?php foreach($images as $image): ?>
          <div class="col-md-4">
            <img src="<?php echo htmlspecialchars($image['chemin']); ?>" 
                 class="img-fluid rounded" 
                 alt="Photo du déménagement">
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>
    
    <!-- Section pour les déménageurs : faire une proposition -->
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 2 && $_SESSION['user_id'] != $annonce['client_id']): ?>
    <div class="card mb-4 border-success">
      <div class="card-header bg-success text-white">
        <h5 class="mb-0">Faire une proposition</h5>
      </div>
      <div class="card-body">
        <?php if ($user_proposition): ?>
          <div class="alert alert-info">
            <strong>Vous avez déjà fait une proposition pour ce déménagement :</strong><br>
            Prix : <?php echo $user_proposition['prix']; ?> €<br>
            Statut : 
            <?php 
              switch($user_proposition['statut']) {
                case 'en_attente': echo '<span class="badge bg-warning">En attente</span>'; break;
                case 'accepte': echo '<span class="badge bg-success">Acceptée</span>'; break;
                case 'refuse': echo '<span class="badge bg-danger">Refusée</span>'; break;
              }
            ?>
          </div>
          
          <!-- Bouton de messagerie pour les déménageurs ayant fait une proposition -->
          <a href="messagerie.php?id=<?php echo $annonce_id; ?>" class="btn btn-primary w-100">
            <i class="bi bi-chat-dots"></i> Contacter le client
          </a>
        <?php else: ?>
          <form method="POST" action="tt_proposition.php">
            <input type="hidden" name="demenagement_id" value="<?php echo $annonce['id']; ?>">
            
            <div class="mb-3">
              <label for="prix" class="form-label">Votre prix (€) *</label>
              <input type="number" step="0.01" class="form-control" id="prix" name="prix" required>
            </div>
            
            <div class="mb-3">
              <label for="commentaire" class="form-label">Commentaire (optionnel)</label>
              <textarea class="form-control" id="commentaire" name="commentaire" rows="3" 
                        placeholder="Ajoutez des informations complémentaires..."></textarea>
            </div>
            
            <button type="submit" class="btn btn-success">Envoyer ma proposition</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
    
    <!-- Section pour le client : voir les propositions -->
    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $annonce['client_id']): ?>
    <div class="card mb-4 border-primary">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Propositions reçues (<?php echo count($propositions); ?>)</h5>
      </div>
      <div class="card-body">
        <?php if (count($propositions) > 0): ?>
          <?php foreach($propositions as $prop): ?>
          <div class="card mb-3">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <h6><?php echo htmlspecialchars($prop['prenom'] . ' ' . $prop['nom']); ?></h6>
                  <p class="mb-1"><strong>Prix proposé :</strong> <?php echo $prop['prix']; ?> €</p>
                  <?php if ($prop['commentaire']): ?>
                    <p class="mb-1"><strong>Commentaire :</strong> <?php echo nl2br(htmlspecialchars($prop['commentaire'])); ?></p>
                  <?php endif; ?>
                  <p class="mb-0"><small class="text-muted">Proposé le <?php echo date('d/m/Y à H:i', strtotime($prop['date_proposition'])); ?></small></p>
                </div>
                <div class="text-end">
                  <?php if ($prop['statut'] == 'en_attente'): ?>
                    <form method="POST" action="tt_accepter_proposition.php" class="mb-2">
                      <input type="hidden" name="proposition_id" value="<?php echo $prop['id']; ?>">
                      <input type="hidden" name="demenagement_id" value="<?php echo $annonce['id']; ?>">
                      <button type="submit" class="btn btn-success btn-sm">Accepter</button>
                    </form>
                  <?php elseif ($prop['statut'] == 'accepte'): ?>
                    <span class="badge bg-success">Acceptée</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Refusée</span>
                  <?php endif; ?>
                  
                  <a href="messagerie.php?id=<?php echo $annonce_id; ?>&demenageur_id=<?php echo $prop['demenageur_id']; ?>" 
                     class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-chat-dots"></i> Message
                  </a>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-muted">Aucune proposition pour le moment.</p>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
      </div>
      <!-- Fin col-lg-8 -->
  
      <!-- Sidebar -->
      <div class="col-lg-4">
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Informations</h5>
      </div>
      <div class="card-body">
        <p><strong>Statut :</strong> 
          <?php 
            switch($annonce['statut']) {
              case 'en_attente': echo '<span class="badge bg-warning">En attente</span>'; break;
              case 'en_cours': echo '<span class="badge bg-info">En cours</span>'; break;
              case 'termine': echo '<span class="badge bg-success">Terminé</span>'; break;
              case 'annule': echo '<span class="badge bg-danger">Annulé</span>'; break;
            }
          ?>
        </p>
        <p><strong>Publié le :</strong><br><?php echo date('d/m/Y à H:i', strtotime($annonce['date_creation'])); ?></p>
        
        <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $annonce['client_id'] || $_SESSION['role'] == 3)): ?>
        <hr>
        <div class="d-grid gap-2">
          <?php if ($_SESSION['role'] != 3): ?>
            <a href="mes_demenagements.php" class="btn btn-outline-primary">Mes déménagements</a>
          <?php endif; ?>
          
          <?php if ($annonce['statut'] == 'en_cours' && $_SESSION['user_id'] == $annonce['client_id']): ?>
            <form method="POST" action="tt_valider_demenagement.php" class="d-inline" onsubmit="return confirm('Confirmez-vous que le déménagement a bien été réalisé ?');">
              <input type="hidden" name="demenagement_id" value="<?php echo $annonce['id']; ?>">
              <button type="submit" class="btn btn-success w-100">
                <i class="bi bi-check-circle-fill"></i> Marquer comme terminé
              </button>
            </form>
          <?php endif; ?>
          
          <?php if ($annonce['statut'] == 'termine' && $_SESSION['user_id'] == $annonce['client_id']): ?>
            <a href="noter_demenageurs.php?id=<?php echo $annonce['id']; ?>" class="btn btn-warning w-100">
              <i class="bi bi-star-fill"></i> Noter les déménageurs
            </a>
          <?php endif; ?>
          
          <?php if ($nb_acceptees == 0 || $_SESSION['role'] == 3): ?>
            <a href="modifier_demenagement.php?id=<?php echo $annonce['id']; ?>" class="btn btn-outline-warning w-100">
              <i class="bi bi-pencil"></i> Modifier
            </a>
          <?php elseif ($_SESSION['user_id'] == $annonce['client_id']): ?>
            <span class="d-inline-block" tabindex="0" 
                  data-bs-toggle="tooltip" 
                  data-bs-placement="top" 
                  title="Impossible de modifier l'annonce car vous avez déjà accepté un déménageur">
              <button class="btn btn-outline-secondary w-100 btn-disabled-no-events" disabled>
                Modifier
              </button>
            </span>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
    
    <div class="card">
      <div class="card-body">
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 3): ?>
          <a href="admin_annonces.php" class="btn btn-secondary w-100">← Retour à la gestion des annonces</a>
        <?php else: ?>
          <a href="annonces.php" class="btn btn-secondary w-100">← Retour aux annonces</a>
        <?php endif; ?>
      </div>
    </div>
      </div>
      <!-- Fin col-lg-4 -->
    </div>
    <!-- Fin row interne -->
  </div>
  <!-- Fin col-lg-10 mx-auto -->
</div>
<!-- Fin row mb-5 -->

<?php
  $mysqli->close();
  include('../includes/footer.inc.php');
?>
