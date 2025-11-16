<?php
  session_start();
  
  // Vérifier si l'utilisateur est connecté et est un administrateur
  if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true || $_SESSION['role'] != 3) {
    $_SESSION['erreur'] = "Accès réservé aux administrateurs.";
    header('Location: ../auth/connexion.php');
    exit();
  }
  
  require_once("../config/param.inc.php");
  $titre = "Gestion des annonces";
  include('../includes/header.inc.php');
  include('../includes/menu.inc.php');
  include('../includes/message.inc.php');
  
  // Connexion BDD
  $mysqli = new mysqli($host, $login, $passwd, $dbname);
  
  if ($mysqli->connect_error) {
    die("Erreur de connexion : " . $mysqli->connect_error);
  }
  
  $mysqli->set_charset("utf8");
  
  // Récupérer toutes les annonces avec informations du client
  $query = "SELECT d.*, c.id as client_id, c.nom, c.prenom, c.email,
            (SELECT COUNT(*) FROM proposition WHERE demenagement_id = d.id) as nb_propositions,
            (SELECT COUNT(*) FROM proposition WHERE demenagement_id = d.id AND statut = 'accepte') as nb_acceptes
            FROM demenagement d
            JOIN compte c ON d.client_id = c.id
            ORDER BY d.date_creation DESC";
  
  $result = $mysqli->query($query);
?>

<div class="row mb-5">
  <div class="col-lg-10 mx-auto">
    <h1 class="mb-4"><i class="bi bi-shield-check"></i> Gestion des annonces</h1>
    
    <div class="card mb-4">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-md-8">
            <h5 class="mb-0">Administration des annonces</h5>
            <p class="text-muted mb-0 small">Consultez et gérez toutes les annonces de déménagement</p>
          </div>
          <div class="col-md-4 text-end">
            <span class="badge bg-primary"><?php echo $result->num_rows; ?> annonces</span>
          </div>
        </div>
      </div>
    </div>
    
    <?php if ($result->num_rows == 0): ?>
      <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> Aucune annonce pour le moment.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Titre</th>
              <th>Client</th>
              <th>Date création</th>
              <th>Date déménagement</th>
              <th>Statut</th>
              <th>Propositions</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($annonce = $result->fetch_assoc()): ?>
              <tr>
                <td><?php echo $annonce['id']; ?></td>
                <td>
                  <a href="detail_annonce.php?id=<?php echo $annonce['id']; ?>" class="text-decoration-none">
                    <?php echo htmlspecialchars($annonce['titre']); ?>
                  </a>
                  <br>
                  <small class="text-muted">
                    <?php echo htmlspecialchars($annonce['ville_depart']); ?> 
                    → <?php echo htmlspecialchars($annonce['ville_arrivee']); ?>
                  </small>
                </td>
                <td>
                  <a href="profil.php?id=<?php echo $annonce['client_id']; ?>" class="text-decoration-none">
                    <strong><?php echo htmlspecialchars($annonce['prenom'] . ' ' . $annonce['nom']); ?></strong>
                  </a>
                  <br>
                  <small class="text-muted"><?php echo htmlspecialchars($annonce['email']); ?></small>
                </td>
                <td><?php echo date('d/m/Y', strtotime($annonce['date_creation'])); ?></td>
                <td><?php echo date('d/m/Y', strtotime($annonce['date_demenagement'])); ?></td>
                <td>
                  <?php 
                    $statut_badges = [
                      'en_attente' => 'bg-warning',
                      'en_cours' => 'bg-info',
                      'termine' => 'bg-success',
                      'annule' => 'bg-danger'
                    ];
                    $statut_labels = [
                      'en_attente' => 'En attente',
                      'en_cours' => 'En cours',
                      'termine' => 'Terminé',
                      'annule' => 'Annulé'
                    ];
                    $badge_class = $statut_badges[$annonce['statut']] ?? 'bg-secondary';
                    $label = $statut_labels[$annonce['statut']] ?? $annonce['statut'];
                  ?>
                  <span class="badge <?php echo $badge_class; ?>">
                    <?php echo $label; ?>
                  </span>
                </td>
                <td>
                  <span class="badge bg-light text-dark">
                    <?php echo $annonce['nb_acceptes']; ?>/<?php echo $annonce['nombre_demenageurs']; ?> acceptés
                  </span>
                  <br>
                  <small class="text-muted"><?php echo $annonce['nb_propositions']; ?> total</small>
                </td>
                <td>
                  <div class="btn-group-vertical btn-group-sm">
                    <a href="detail_annonce.php?id=<?php echo $annonce['id']; ?>" 
                       class="btn btn-outline-info btn-sm">
                      <i class="bi bi-eye"></i> Voir
                    </a>
                    <a href="admin_supprimer_annonce.php?id=<?php echo $annonce['id']; ?>" 
                       class="btn btn-outline-danger btn-sm"
                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette annonce ?');">
                      <i class="bi bi-trash"></i> Supprimer
                    </a>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
    
    <div class="mt-4">
      <a href="admin_comptes.php" class="btn btn-outline-secondary">
        <i class="bi bi-people"></i> Gérer les comptes
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
