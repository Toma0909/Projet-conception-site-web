<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!defined('BASE_PATH')) {
    require_once(__DIR__ . '/../config/param.inc.php');
}
$base = defined('BASE_PATH') ? BASE_PATH : '';

// Récupérer les notifications si l'utilisateur est connecté
$total_notifications = 0;
$notifications = [];
if (isset($_SESSION['connecte']) && $_SESSION['connecte'] === true) {
    $mysqli_menu = new mysqli($host, $login, $passwd, $dbname);
    if (!$mysqli_menu->connect_error) {
        $user_id = $_SESSION['user_id'];
        $role = $_SESSION['role'];
        
        $msg_query = "SELECT COUNT(*) as nb FROM message WHERE destinataire_id = ? AND lu = 0";
        $msg_stmt = $mysqli_menu->prepare($msg_query);
        $msg_stmt->bind_param("i", $user_id);
        $msg_stmt->execute();
        $nb_messages = $msg_stmt->get_result()->fetch_assoc()['nb'];
        $msg_stmt->close();
        
        if ($nb_messages > 0) {
            $notifications[] = [
                'count' => $nb_messages,
                'text' => $nb_messages . ' message' . ($nb_messages > 1 ? 's' : '') . ' non lu' . ($nb_messages > 1 ? 's' : ''),
                'link' => ($role == 1) ? $base . '/pages/mes_demenagements.php' : $base . '/pages/mes_interventions.php',
                'icon' => 'bi-envelope'
            ];
            $total_notifications += $nb_messages;
        }
        
        if ($role == 1) {
            $prop_query = "SELECT COUNT(*) as nb FROM proposition p JOIN demenagement d ON p.demenagement_id = d.id WHERE d.client_id = ? AND p.statut = 'en_attente'";
            $prop_stmt = $mysqli_menu->prepare($prop_query);
            $prop_stmt->bind_param("i", $user_id);
            $prop_stmt->execute();
            $nb_propositions = $prop_stmt->get_result()->fetch_assoc()['nb'];
            $prop_stmt->close();
            
            if ($nb_propositions > 0) {
                $notifications[] = [
                    'count' => $nb_propositions,
                    'text' => $nb_propositions . ' proposition' . ($nb_propositions > 1 ? 's' : '') . ' en attente',
                    'link' => $base . '/pages/mes_demenagements.php',
                    'icon' => 'bi-inbox'
                ];
                $total_notifications += $nb_propositions;
            }
        }
        
        if ($role == 2) {
            $accept_query = "SELECT COUNT(*) as nb FROM proposition WHERE demenageur_id = ? AND statut = 'accepte' AND id NOT IN (SELECT proposition_id FROM demenagement_demenageur WHERE demenageur_id = ?)";
            $accept_stmt = $mysqli_menu->prepare($accept_query);
            $accept_stmt->bind_param("ii", $user_id, $user_id);
            $accept_stmt->execute();
            $nb_acceptees = $accept_stmt->get_result()->fetch_assoc()['nb'];
            $accept_stmt->close();
            
            if ($nb_acceptees > 0) {
                $notifications[] = [
                    'count' => $nb_acceptees,
                    'text' => $nb_acceptees . ' proposition' . ($nb_acceptees > 1 ? 's' : '') . ' acceptée' . ($nb_acceptees > 1 ? 's' : ''),
                    'link' => $base . '/pages/mes_interventions.php',
                    'icon' => 'bi-check-circle'
                ];
                $total_notifications += $nb_acceptees;
            }
        }
        
        $mysqli_menu->close();
    }
}
?>
<nav class="navbar navbar-expand-md navbar-custom border-bottom border-body sticky-top bg-white shadow-sm" data-bs-theme="light">
  <div class="container-fluid">
    <a href="<?php echo $base; ?>/index.php">
      <img src="<?php echo $base; ?>/assets/images/Logo.png" alt="Logo LiftUp" width="60" height="60" class="d-inline-block align-text-top me-1">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarText">
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="<?php echo $base; ?>/pages/annonces.php">Annonces</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo $base; ?>/pages/qui_sommes_nous.php">Qui sommes nous ?</a></li>
      </ul>
      <ul class="navbar-nav mb-2 mb-lg-0">
        <?php if (isset($_SESSION['connecte']) && $_SESSION['connecte'] === true): ?>
          <li class="nav-item dropdown">
            <a class="nav-link position-relative px-3" href="#" id="notifDropdown" data-bs-toggle="dropdown">
              <i class="bi bi-bell fs-5"></i>
              <?php if ($total_notifications > 0): ?>
                <span class="position-absolute badge rounded-pill bg-danger notification-badge"><?php echo $total_notifications > 9 ? '9+' : $total_notifications; ?></span>
              <?php endif; ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <?php if (count($notifications) > 0): ?>
                <li><h6 class="dropdown-header">Notifications</h6></li>
                <?php foreach($notifications as $n): ?>
                  <li><a class="dropdown-item d-flex justify-content-between align-items-center" href="<?php echo $n['link']; ?>">
                    <span><i class="bi <?php echo $n['icon']; ?> me-2"></i><?php echo $n['text']; ?></span>
                    <span class="badge bg-primary rounded-pill ms-3"><?php echo $n['count']; ?></span>
                  </a></li>
                <?php endforeach; ?>
              <?php else: ?>
                <li><span class="dropdown-item text-muted">Aucune notification</span></li>
              <?php endif; ?>
            </ul>
          </li>
          <li class="nav-item"><a class="nav-link" href="<?php echo $base; ?>/pages/tableau_bord.php">Tableau de bord</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo $base; ?>/auth/deconnexion.php">Déconnexion</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?php echo $base; ?>/auth/inscription.php">Inscription</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo $base; ?>/auth/connexion.php">Connexion</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
