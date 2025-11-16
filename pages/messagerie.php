<?php
  session_start();
  
  // Vérifier si l'utilisateur est connecté
  if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true) {
    $_SESSION['erreur'] = "Vous devez être connecté pour accéder à la messagerie.";
    header('Location: ../auth/connexion.php');
    exit();
  }
  
  // Vérifier que l'ID du déménagement est présent
  if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['erreur'] = "Déménagement non trouvé.";
    header('Location: ' . ($_SESSION['role'] == 1 ? 'mes_demenagements.php' : 'mes_interventions.php'));
    exit();
  }
  
  $demenagement_id = intval($_GET['id']);
  $user_id = $_SESSION['user_id'];
  
  // Connexion BDD
  require_once("../config/param.inc.php");
  $mysqli = new mysqli($host, $login, $passwd, $dbname);
  
  if ($mysqli->connect_error) {
    $_SESSION['erreur'] = "Problème de connexion à la base de données !";
    header('Location: ' . ($_SESSION['role'] == 1 ? 'mes_demenagements.php' : 'mes_interventions.php'));
    exit();
  }
  
  // Récupérer les informations du déménagement
  $query = "SELECT d.*, c.nom as client_nom, c.prenom as client_prenom, c.id as client_id
            FROM demenagement d
            JOIN compte c ON d.client_id = c.id
            WHERE d.id = ?";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $demenagement_id);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows === 0) {
    $_SESSION['erreur'] = "Déménagement non trouvé.";
    $stmt->close();
    $mysqli->close();
    header('Location: ' . ($_SESSION['role'] == 1 ? 'mes_demenagements.php' : 'mes_interventions.php'));
    exit();
  }
  
  $demenagement = $result->fetch_assoc();
  $stmt->close();
  
  // Vérifier que l'utilisateur a le droit d'accéder à cette messagerie
  $is_client = ($user_id == $demenagement['client_id']);
  $is_demenageur = false;
  
  if (!$is_client) {
    // Vérifier si l'utilisateur est un déménageur ayant fait une proposition
    $check_query = "SELECT id FROM proposition WHERE demenagement_id = ? AND demenageur_id = ?";
    $check_stmt = $mysqli->prepare($check_query);
    $check_stmt->bind_param("ii", $demenagement_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $is_demenageur = ($check_result->num_rows > 0);
    $check_stmt->close();
  }
  
  if (!$is_client && !$is_demenageur) {
    $_SESSION['erreur'] = "Vous n'avez pas accès à cette messagerie.";
    $mysqli->close();
    header('Location: ' . ($_SESSION['role'] == 1 ? 'mes_demenagements.php' : 'mes_interventions.php'));
    exit();
  }
  
  // Déterminer l'interlocuteur
  if ($is_client) {
    // Si c'est le client, récupérer le déménageur depuis l'URL
    if (!isset($_GET['demenageur_id'])) {
      $_SESSION['erreur'] = "Interlocuteur non spécifié.";
      $mysqli->close();
      header('Location: mes_demenagements.php');
      exit();
    }
    $interlocuteur_id = intval($_GET['demenageur_id']);
  } else {
    // Si c'est le déménageur, l'interlocuteur est le client
    $interlocuteur_id = $demenagement['client_id'];
  }
  
  // Récupérer les informations de l'interlocuteur
  $interlocuteur_query = "SELECT nom, prenom FROM compte WHERE id = ?";
  $interlocuteur_stmt = $mysqli->prepare($interlocuteur_query);
  $interlocuteur_stmt->bind_param("i", $interlocuteur_id);
  $interlocuteur_stmt->execute();
  $interlocuteur_result = $interlocuteur_stmt->get_result();
  $interlocuteur = $interlocuteur_result->fetch_assoc();
  $interlocuteur_stmt->close();
  
  // Récupérer les messages
  $messages_query = "SELECT m.*, 
                     e.nom as expediteur_nom, e.prenom as expediteur_prenom,
                     d.nom as destinataire_nom, d.prenom as destinataire_prenom
                     FROM message m
                     JOIN compte e ON m.expediteur_id = e.id
                     JOIN compte d ON m.destinataire_id = d.id
                     WHERE m.demenagement_id = ?
                     AND ((m.expediteur_id = ? AND m.destinataire_id = ?)
                          OR (m.expediteur_id = ? AND m.destinataire_id = ?))
                     ORDER BY m.date_envoi ASC";
  $messages_stmt = $mysqli->prepare($messages_query);
  $messages_stmt->bind_param("iiiii", $demenagement_id, $user_id, $interlocuteur_id, $interlocuteur_id, $user_id);
  $messages_stmt->execute();
  $messages_result = $messages_stmt->get_result();
  $messages = $messages_result->fetch_all(MYSQLI_ASSOC);
  $messages_stmt->close();
  
  // Marquer les messages comme lus
  $update_query = "UPDATE message SET lu = 1 WHERE demenagement_id = ? AND destinataire_id = ? AND expediteur_id = ?";
  $update_stmt = $mysqli->prepare($update_query);
  $update_stmt->bind_param("iii", $demenagement_id, $user_id, $interlocuteur_id);
  $update_stmt->execute();
  $update_stmt->close();
  
  $titre = "Messagerie - " . htmlspecialchars($demenagement['titre']);
  include('../includes/header.inc.php');
  include('../includes/menu.inc.php');
  include('../includes/message.inc.php');
?>

<div class="row mb-4">
  <div class="col-lg-10 mx-auto">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h1>Messagerie</h1>
        <p class="text-muted mb-0">
          <strong>Déménagement :</strong> <?php echo htmlspecialchars($demenagement['titre']); ?><br>
          <strong>Conversation avec :</strong> <?php echo htmlspecialchars($interlocuteur['prenom'] . ' ' . $interlocuteur['nom']); ?>
        </p>
      </div>
      <a href="<?php echo $is_client ? 'mes_demenagements.php' : 'mes_interventions.php'; ?>" class="btn btn-outline-secondary">Retour</a>
    </div>
    
    <!-- Zone des messages -->
    <div class="card mb-3">
      <div class="card-body messages-container" id="messagesContainer">
        <?php if (count($messages) > 0): ?>
          <?php foreach ($messages as $msg): ?>
            <?php $is_sender = ($msg['expediteur_id'] == $user_id); ?>
            <div class="mb-3 <?php echo $is_sender ? 'text-end' : ''; ?>">
              <div class="d-inline-block <?php echo $is_sender ? 'bg-primary text-white' : 'bg-light'; ?> p-3 rounded message-bubble">
                <div class="mb-1">
                  <small class="<?php echo $is_sender ? 'text-white-50' : 'text-muted'; ?>">
                    <strong><?php echo htmlspecialchars($msg['expediteur_prenom'] . ' ' . $msg['expediteur_nom']); ?></strong>
                    - <?php echo date('d/m/Y à H:i', strtotime($msg['date_envoi'])); ?>
                  </small>
                </div>
                <div><?php echo nl2br(htmlspecialchars($msg['contenu'])); ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="text-center text-muted py-5">
            <p>Aucun message pour le moment.</p>
            <p>Commencez la conversation en envoyant un message ci-dessous.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
    
    <!-- Formulaire d'envoi -->
    <div class="card">
      <div class="card-body">
        <form method="POST" action="tt_envoyer_message.php">
          <input type="hidden" name="demenagement_id" value="<?php echo $demenagement_id; ?>">
          <input type="hidden" name="destinataire_id" value="<?php echo $interlocuteur_id; ?>">
          
          <div class="mb-3">
            <label for="contenu" class="form-label">Votre message</label>
            <textarea class="form-control" id="contenu" name="contenu" rows="3" placeholder="Tapez votre message..." required></textarea>
          </div>
          
          <div class="text-end">
            <button type="submit" class="btn btn-primary">Envoyer</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php
  $mysqli->close();
  include('../includes/footer.inc.php');
?>
