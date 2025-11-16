<?php
  session_start();
  
  // Configuration
  require_once(__DIR__ . "/../config/param.inc.php");
  
  // Vérifier si l'utilisateur est connecté
  if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true) {
    $_SESSION['erreur'] = "Vous devez être connecté pour modifier une demande de déménagement.";
    header('Location: ../auth/connexion.php');
    exit();
  }
  
  // Vérifier que c'est un client ou un admin
  if ($_SESSION['role'] != 1 && $_SESSION['role'] != 3) {
    $_SESSION['erreur'] = "Seuls les clients et administrateurs peuvent modifier des demandes de déménagement.";
    header('Location: tableau_bord.php');
    exit();
  }
  
  // Récupérer l'ID du déménagement
  if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['erreur'] = "Déménagement non trouvé.";
    $redirect_url = ($_SESSION['role'] == 3) ? 'admin_annonces.php' : 'mes_demenagements.php';
    header('Location: ' . $redirect_url);
    exit();
  }
  
  $demenagement_id = intval($_GET['id']);
  $is_admin = ($_SESSION['role'] == 3);
  
  // Connexion BDD
  $mysqli = new mysqli($host, $login, $passwd, $dbname);
  
  if ($mysqli->connect_error) {
    $_SESSION['erreur'] = "Problème de connexion à la base de données !";
    $redirect_url = $is_admin ? 'admin_annonces.php' : 'mes_demenagements.php';
    header('Location: ' . $redirect_url);
    exit();
  }
  
  // Récupérer les informations du déménagement
  // Admin peut modifier toute annonce, client seulement les siennes
  if ($is_admin) {
    $query = "SELECT * FROM demenagement WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $demenagement_id);
  } else {
    $query = "SELECT * FROM demenagement WHERE id = ? AND client_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ii", $demenagement_id, $_SESSION['user_id']);
  }
  
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows === 0) {
    $_SESSION['erreur'] = "Déménagement non trouvé ou vous n'avez pas les droits pour le modifier.";
    $stmt->close();
    $mysqli->close();
    $redirect_url = $is_admin ? 'admin_annonces.php' : 'mes_demenagements.php';
    header('Location: ' . $redirect_url);
    exit();
  }
  
  $demenagement = $result->fetch_assoc();
  $stmt->close();
  
  // Vérifier s'il y a des propositions acceptées
  $query_acceptee = "SELECT COUNT(*) as nb_acceptees FROM proposition WHERE demenagement_id = ? AND statut = 'accepte'";
  $stmt_acceptee = $mysqli->prepare($query_acceptee);
  $stmt_acceptee->bind_param("i", $demenagement_id);
  $stmt_acceptee->execute();
  $result_acceptee = $stmt_acceptee->get_result();
  $nb_acceptees = $result_acceptee->fetch_assoc()['nb_acceptees'];
  $stmt_acceptee->close();
  
  // Si une proposition a été acceptée, bloquer la modification (sauf pour les admins)
  if ($nb_acceptees > 0 && !$is_admin) {
    $_SESSION['erreur'] = "Vous ne pouvez pas modifier cette demande car vous avez déjà accepté un déménageur.";
    $mysqli->close();
    header('Location: mes_demenagements.php');
    exit();
  }
  
  $mysqli->close();
  
  $titre = "Modifier le déménagement";
  include('../includes/header.inc.php');
  include('../includes/menu.inc.php');
  include('../includes/message.inc.php');
?>

<div class="row">
  <div class="col-lg-10 mx-auto">
    <h1>Modifier la demande de déménagement</h1>
    <p class="lead">Modifiez les informations de votre annonce</p>
    
    <form method="POST" action="tt_modifier_demenagement.php" enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?php echo $demenagement['id']; ?>">
      
      <!-- Informations générales -->
      <div class="card mb-4">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">Informations générales</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label for="titre" class="form-label">Titre de l'annonce *</label>
            <input type="text" class="form-control" id="titre" name="titre" 
                   value="<?php echo htmlspecialchars($demenagement['titre']); ?>" required>
          </div>
          
          <div class="mb-3">
            <label for="description" class="form-label">Description *</label>
            <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($demenagement['description']); ?></textarea>
            <div class="form-text">Indiquez tous les détails importants pour les déménageurs.</div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="date_demenagement" class="form-label">Date du déménagement *</label>
              <input type="date" class="form-control" id="date_demenagement" name="date_demenagement" 
                     value="<?php echo $demenagement['date_demenagement']; ?>"
                     min="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="heure_debut" class="form-label">Heure de début *</label>
              <input type="time" class="form-control" id="heure_debut" name="heure_debut" 
                     value="<?php echo $demenagement['heure_debut']; ?>" required>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="ville_depart" class="form-label">Ville de départ *</label>
              <input type="text" class="form-control" id="ville_depart" name="ville_depart" 
                     value="<?php echo htmlspecialchars($demenagement['ville_depart']); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="ville_arrivee" class="form-label">Ville d'arrivée *</label>
              <input type="text" class="form-control" id="ville_arrivee" name="ville_arrivee" 
                     value="<?php echo htmlspecialchars($demenagement['ville_arrivee']); ?>" required>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Détails du lieu de départ -->
      <div class="card mb-4">
        <div class="card-header bg-info text-white">
          <h5 class="mb-0">Lieu de départ</h5>
        </div>
        <div class="card-body">
          <input type="radio" class="btn-check" name="depart_type" id="depart_maison" 
                 value="maison" autocomplete="off" <?php echo ($demenagement['depart_type'] == 'maison') ? 'checked' : ''; ?> required>
          <input type="radio" class="btn-check" name="depart_type" id="depart_appartement" 
                 value="appartement" autocomplete="off" <?php echo ($demenagement['depart_type'] == 'appartement') ? 'checked' : ''; ?> required>
          
          <div class="mb-3">
            <label class="form-label">Type de logement *</label>
            <div class="btn-group w-100" role="group">
              <label class="btn btn-outline-primary" for="depart_maison">Maison</label>
              <label class="btn btn-outline-primary" for="depart_appartement">Appartement</label>
            </div>
          </div>
          
          <div id="depart_details" class="<?php echo ($demenagement['depart_type'] != 'appartement') ? 'logement-details-hidden' : ''; ?>">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="depart_etage" class="form-label">Étage</label>
                <input type="number" class="form-control" id="depart_etage" name="depart_etage" 
                       min="0" max="50" value="<?php echo $demenagement['depart_etage'] ?? ''; ?>">
              </div>
              <div class="col-md-6 mb-3">
                <label for="depart_ascenseur" class="form-label">Ascenseur disponible ?</label>
                <select class="form-select" id="depart_ascenseur" name="depart_ascenseur">
                  <option value="0" <?php echo ($demenagement['depart_ascenseur'] == 0) ? 'selected' : ''; ?>>Non</option>
                  <option value="1" <?php echo ($demenagement['depart_ascenseur'] == 1) ? 'selected' : ''; ?>>Oui</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Détails du lieu d'arrivée -->
      <div class="card mb-4">
        <div class="card-header bg-info text-white">
          <h5 class="mb-0">Lieu d'arrivée</h5>
        </div>
        <div class="card-body">
          <input type="radio" class="btn-check" name="arrivee_type" id="arrivee_maison" 
                 value="maison" autocomplete="off" <?php echo ($demenagement['arrivee_type'] == 'maison') ? 'checked' : ''; ?> required>
          <input type="radio" class="btn-check" name="arrivee_type" id="arrivee_appartement" 
                 value="appartement" autocomplete="off" <?php echo ($demenagement['arrivee_type'] == 'appartement') ? 'checked' : ''; ?> required>
          
          <div class="mb-3">
            <label class="form-label">Type de logement *</label>
            <div class="btn-group w-100" role="group">
              <label class="btn btn-outline-primary" for="arrivee_maison">Maison</label>
              <label class="btn btn-outline-primary" for="arrivee_appartement">Appartement</label>
            </div>
          </div>
          
          <div id="arrivee_details" class="<?php echo ($demenagement['arrivee_type'] != 'appartement') ? 'logement-details-hidden' : ''; ?>">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="arrivee_etage" class="form-label">Étage</label>
                <input type="number" class="form-control" id="arrivee_etage" name="arrivee_etage" 
                       min="0" max="50" value="<?php echo $demenagement['arrivee_etage'] ?? ''; ?>">
              </div>
              <div class="col-md-6 mb-3">
                <label for="arrivee_ascenseur" class="form-label">Ascenseur disponible ?</label>
                <select class="form-select" id="arrivee_ascenseur" name="arrivee_ascenseur">
                  <option value="0" <?php echo ($demenagement['arrivee_ascenseur'] == 0) ? 'selected' : ''; ?>>Non</option>
                  <option value="1" <?php echo ($demenagement['arrivee_ascenseur'] == 1) ? 'selected' : ''; ?>>Oui</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Volume et nombre de déménageurs -->
      <div class="card mb-4">
        <div class="card-header bg-warning">
          <h5 class="mb-0">Volume et équipe</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-4 mb-3">
              <label for="volume" class="form-label">Volume approximatif (m³)</label>
              <input type="number" step="0.1" class="form-control" id="volume" name="volume" 
                     value="<?php echo $demenagement['volume'] ?? ''; ?>">
              <div class="form-text">Facultatif</div>
            </div>
            <div class="col-md-4 mb-3">
              <label for="poids" class="form-label">Poids approximatif (kg)</label>
              <input type="number" step="0.1" class="form-control" id="poids" name="poids" 
                     value="<?php echo $demenagement['poids'] ?? ''; ?>">
              <div class="form-text">Facultatif</div>
            </div>
            <div class="col-md-4 mb-3">
              <label for="nombre_demenageurs" class="form-label">Nombre de déménageurs *</label>
              <input type="number" class="form-control" id="nombre_demenageurs" name="nombre_demenageurs" 
                     min="1" max="10" value="<?php echo $demenagement['nombre_demenageurs']; ?>" required>
            </div>
          </div>
        </div>
      </div>
      
      <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-4">
        <?php if ($is_admin): ?>
          <a href="admin_annonces.php" class="btn btn-outline-secondary me-md-2">Annuler</a>
        <?php else: ?>
          <a href="mes_demenagements.php" class="btn btn-outline-secondary me-md-2">Annuler</a>
        <?php endif; ?>
        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
      </div>
    </form>
  </div>
</div>

<?php
  include('../includes/footer.inc.php');
?>
