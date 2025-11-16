<?php
  session_start();
  
  // Vérifier si l'utilisateur est connecté et est un administrateur
  if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true || $_SESSION['role'] != 3) {
    $_SESSION['erreur'] = "Accès réservé aux administrateurs.";
    header('Location: ../auth/connexion.php');
    exit();
  }
  
  require_once("../config/param.inc.php");
  $titre = "Gestion des comptes";
  include('../includes/header.inc.php');
  include('../includes/menu.inc.php');
  include('../includes/message.inc.php');
  
  // Connexion BDD
  $mysqli = new mysqli($host, $login, $passwd, $dbname);
  
  if ($mysqli->connect_error) {
    die("Erreur de connexion : " . $mysqli->connect_error);
  }
  
  $mysqli->set_charset("utf8");
  
  // Filtres
  $filtre_role = isset($_GET['role']) ? intval($_GET['role']) : -1;
  
  // Récupérer tous les comptes (sauf les admins)
  $query = "SELECT id, nom, prenom, email, role 
            FROM compte 
            WHERE role != 3";
  
  if ($filtre_role >= 0) {
    $query .= " AND role = " . $filtre_role;
  }
  
  $query .= " ORDER BY id DESC";
  
  $result = $mysqli->query($query);
?>

<div class="row mb-5">
  <div class="col-lg-10 mx-auto">
    <h1 class="mb-4"><i class="bi bi-people"></i> Gestion des comptes</h1>
    
    <div class="card mb-4">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-md-8">
            <h5 class="mb-0">Administration des utilisateurs</h5>
            <p class="text-muted mb-0 small">Gérez les comptes clients et déménageurs</p>
          </div>
          <div class="col-md-4 text-end">
            <span class="badge bg-primary"><?php echo $result->num_rows; ?> comptes</span>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Filtres -->
    <div class="card mb-4">
      <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
          <div class="col-md-8">
            <label for="role" class="form-label">Filtrer par rôle</label>
            <select name="role" id="role" class="form-select">
              <option value="-1" <?php echo ($filtre_role == -1) ? 'selected' : ''; ?>>Tous</option>
              <option value="0" <?php echo ($filtre_role == 0) ? 'selected' : ''; ?>>Comptes non activés</option>
              <option value="1" <?php echo ($filtre_role == 1) ? 'selected' : ''; ?>>Clients</option>
              <option value="2" <?php echo ($filtre_role == 2) ? 'selected' : ''; ?>>Déménageurs</option>
            </select>
          </div>
          <div class="col-md-4">
            <button type="submit" class="btn btn-primary w-100">Filtrer</button>
          </div>
        </form>
      </div>
    </div>
    
    <?php if ($result->num_rows == 0): ?>
      <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> Aucun compte trouvé.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Nom</th>
              <th>Email</th>
              <th>Rôle</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($compte = $result->fetch_assoc()): ?>
              <tr>
                <td><?php echo $compte['id']; ?></td>
                <td>
                  <a href="profil.php?id=<?php echo $compte['id']; ?>" class="text-decoration-none">
                    <strong><?php echo htmlspecialchars($compte['prenom'] . ' ' . $compte['nom']); ?></strong>
                  </a>
                </td>
                <td><?php echo htmlspecialchars($compte['email']); ?></td>
                <td>
                  <?php 
                    switch($compte['role']) {
                      case 0:
                        echo '<span class="badge bg-warning">Non activé</span>';
                        break;
                      case 1:
                        echo '<span class="badge bg-success">Client</span>';
                        break;
                      case 2:
                        echo '<span class="badge bg-info">Déménageur</span>';
                        break;
                      default:
                        echo '<span class="badge bg-secondary">Inconnu</span>';
                    }
                  ?>
                </td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <a href="profil.php?id=<?php echo $compte['id']; ?>" 
                       class="btn btn-outline-info">
                      <i class="bi bi-person"></i> Profil
                    </a>
                    <?php if ($compte['role'] != 0): ?>
                      <a href="admin_desactiver_compte.php?id=<?php echo $compte['id']; ?>" 
                         class="btn btn-outline-warning"
                         onclick="return confirm('Désactiver ce compte ? L\'utilisateur ne pourra plus se connecter.');">
                        <i class="bi bi-lock"></i> Désactiver
                      </a>
                    <?php else: ?>
                      <a href="admin_activer_compte.php?id=<?php echo $compte['id']; ?>" 
                         class="btn btn-outline-success">
                        <i class="bi bi-unlock"></i> Activer
                      </a>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
    
    <div class="mt-4">
      <a href="admin_annonces.php" class="btn btn-outline-secondary">
        <i class="bi bi-box-seam"></i> Gérer les annonces
      </a>
      <a href="tableau_bord.php" class="btn btn-outline-primary">
        <i class="bi bi-house"></i> Tableau de bord
      </a>
    </div>
  </div>
</div>

<?php
  $mysqli->close();
  include('../includes/footer.inc.php');
?>
