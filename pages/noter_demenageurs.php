<?php
session_start();
require_once '../config/param.inc.php';

// Vérifier que l'utilisateur est connecté et est un client
if (!isset($_SESSION['connecte']) || !$_SESSION['connecte'] || $_SESSION['role'] != 1) {
    $_SESSION['erreur'] = "Vous devez être connecté en tant que client.";
    header("Location: ../auth/connexion.php");
    exit();
}

// Vérifier que l'ID du déménagement est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['erreur'] = "ID de déménagement manquant.";
    header("Location: mes_demenagements.php");
    exit();
}

$demenagement_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Connexion à la base de données
$mysqli = new mysqli($host, $login, $passwd, $dbname);
if ($mysqli->connect_error) {
    die("Erreur de connexion à la base de données : " . $mysqli->connect_error);
}
$mysqli->set_charset("utf8");

// Vérifier que le déménagement appartient bien au client connecté et est terminé
$query = "SELECT * FROM demenagement WHERE id = ? AND client_id = ? AND statut = 'termine'";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("ii", $demenagement_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['erreur'] = "Déménagement introuvable, non autorisé ou non terminé.";
    $stmt->close();
    $mysqli->close();
    header("Location: mes_demenagements.php");
    exit();
}

$demenagement = $result->fetch_assoc();
$stmt->close();

// Récupérer les déménageurs qui ont participé au déménagement
$demenageurs_query = "SELECT dd.demenageur_id, c.nom, c.prenom, c.email, p.prix,
                      a.id as avis_id, a.note, a.commentaire
                      FROM demenagement_demenageur dd
                      JOIN compte c ON dd.demenageur_id = c.id
                      JOIN proposition p ON dd.proposition_id = p.id
                      LEFT JOIN avis a ON a.demenagement_id = dd.demenagement_id AND a.demenageur_id = dd.demenageur_id
                      WHERE dd.demenagement_id = ?";
$demenageurs_stmt = $mysqli->prepare($demenageurs_query);
$demenageurs_stmt->bind_param("i", $demenagement_id);
$demenageurs_stmt->execute();
$demenageurs_result = $demenageurs_stmt->get_result();
$demenageurs = $demenageurs_result->fetch_all(MYSQLI_ASSOC);
$demenageurs_stmt->close();

$titre = "Noter les déménageurs";
include('../includes/header.inc.php');
include('../includes/menu.inc.php');
include('../includes/message.inc.php');
?>

<div class="row mb-5">
  <div class="col-lg-10 mx-auto">
    <h2 class="mb-4">Noter les déménageurs</h2>
    <p class="text-muted">Déménagement : <?php echo htmlspecialchars($demenagement['titre']); ?></p>
    
    <?php if (count($demenageurs) > 0): ?>
      <?php foreach($demenageurs as $demenageur): ?>
      <div class="card mb-4">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">
            <?php echo htmlspecialchars($demenageur['prenom'] . ' ' . $demenageur['nom']); ?>
          </h5>
          <small>Prix : <?php echo $demenageur['prix']; ?> €</small>
        </div>
        <div class="card-body">
          <?php if ($demenageur['avis_id']): ?>
            <!-- Avis déjà donné -->
            <div class="alert alert-success">
              <h6><i class="bi bi-check-circle-fill"></i> Avis déjà enregistré</h6>
              <div class="mb-2">
                <strong>Note :</strong> 
                <?php for($i = 1; $i <= 5; $i++): ?>
                  <i class="bi bi-star<?php echo ($i <= $demenageur['note']) ? '-fill text-warning' : ''; ?>"></i>
                <?php endfor; ?>
                (<?php echo $demenageur['note']; ?>/5)
              </div>
              <?php if ($demenageur['commentaire']): ?>
                <div>
                  <strong>Commentaire :</strong><br>
                  <?php echo nl2br(htmlspecialchars($demenageur['commentaire'])); ?>
                </div>
              <?php endif; ?>
            </div>
            
            <!-- Formulaire de modification -->
            <button class="btn btn-outline-warning btn-sm" type="button" data-bs-toggle="collapse" 
                    data-bs-target="#editForm<?php echo $demenageur['demenageur_id']; ?>">
              <i class="bi bi-pencil"></i> Modifier mon avis
            </button>
            
            <div class="collapse mt-3" id="editForm<?php echo $demenageur['demenageur_id']; ?>">
              <form method="POST" action="tt_avis.php">
                <input type="hidden" name="demenagement_id" value="<?php echo $demenagement_id; ?>">
                <input type="hidden" name="demenageur_id" value="<?php echo $demenageur['demenageur_id']; ?>">
                <input type="hidden" name="action" value="modifier">
                
                <div class="mb-3">
                  <label class="form-label">Note <span class="text-danger">*</span></label>
                  <div class="rating-stars" data-rating="<?php echo $demenageur['note']; ?>">
                    <?php for($i = 5; $i >= 1; $i--): ?>
                      <input type="radio" name="note" value="<?php echo $i; ?>" id="edit_star<?php echo $demenageur['demenageur_id']; ?>_<?php echo $i; ?>" 
                             <?php echo ($i == $demenageur['note']) ? 'checked' : ''; ?> required>
                      <label for="edit_star<?php echo $demenageur['demenageur_id']; ?>_<?php echo $i; ?>" class="star-label">
                        <i class="bi bi-star-fill"></i>
                      </label>
                    <?php endfor; ?>
                  </div>
                </div>
                
                <div class="mb-3">
                  <label for="edit_commentaire<?php echo $demenageur['demenageur_id']; ?>" class="form-label">Commentaire</label>
                  <textarea class="form-control" id="edit_commentaire<?php echo $demenageur['demenageur_id']; ?>" 
                            name="commentaire" rows="3" 
                            placeholder="Partagez votre expérience..."><?php echo htmlspecialchars($demenageur['commentaire']); ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-warning">
                  <i class="bi bi-pencil"></i> Modifier l'avis
                </button>
              </form>
            </div>
          <?php else: ?>
            <!-- Formulaire pour donner un avis -->
            <form method="POST" action="tt_avis.php">
              <input type="hidden" name="demenagement_id" value="<?php echo $demenagement_id; ?>">
              <input type="hidden" name="demenageur_id" value="<?php echo $demenageur['demenageur_id']; ?>">
              <input type="hidden" name="action" value="ajouter">
              
              <div class="mb-3">
                <label class="form-label">Note <span class="text-danger">*</span></label>
                <div class="rating-stars">
                  <?php for($i = 5; $i >= 1; $i--): ?>
                    <input type="radio" name="note" value="<?php echo $i; ?>" id="star<?php echo $demenageur['demenageur_id']; ?>_<?php echo $i; ?>" required>
                    <label for="star<?php echo $demenageur['demenageur_id']; ?>_<?php echo $i; ?>" class="star-label">
                      <i class="bi bi-star-fill"></i>
                    </label>
                  <?php endfor; ?>
                </div>
              </div>
              
              <div class="mb-3">
                <label for="commentaire<?php echo $demenageur['demenageur_id']; ?>" class="form-label">Commentaire</label>
                <textarea class="form-control" id="commentaire<?php echo $demenageur['demenageur_id']; ?>" 
                          name="commentaire" rows="3" 
                          placeholder="Partagez votre expérience..."></textarea>
              </div>
              
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-send-fill"></i> Envoyer l'avis
              </button>
            </form>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
      
      <a href="mes_demenagements.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Retour à mes déménagements
      </a>
    <?php else: ?>
      <div class="alert alert-warning">
        Aucun déménageur n'a été accepté pour ce déménagement.
      </div>
      <a href="mes_demenagements.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Retour
      </a>
    <?php endif; ?>
  </div>
</div>

  </div>
</div>

<?php
  $mysqli->close();
  include('../includes/footer.inc.php');
?>
