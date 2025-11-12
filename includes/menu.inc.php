<?php 
// Démarrer la session seulement si elle n'est pas déjà active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="mb-2 navbar navbar-expand-md navbar-custom border-bottom border-body" data-bs-theme="light">
  <div class="container-fluid">    <!-- Partie gauche de la barre -->    <a href="../index.php">
      <img src="../assets/images/Logo.png" alt="Logo LiftUp" width="60" height="60" class="d-inline-block align-text-top me-1">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarText">      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="../pages/annonces.php">Annonces</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../pages/page.php">Qui sommes nous ?</a>
        </li>
      </ul><!-- Partie droite -->
      <ul class="navbar-nav">
          <?php if (isset($_SESSION['connecte']) && $_SESSION['connecte'] === true): ?>              <!-- Utilisateur connecté -->
              <li class="nav-item">
                <a class="nav-link" href="../pages/tableau_bord.php">Tableau de bord</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="../auth/deconnexion.php">Déconnexion</a>
              </li>
          <?php else: ?>
              <!-- Utilisateur non connecté -->
              <li class="nav-item">
                <a class="nav-link" aria-current="page" href="../auth/inscription.php">Inscription</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="../auth/connexion.php">Connexion</a>
              </li>
          <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container">