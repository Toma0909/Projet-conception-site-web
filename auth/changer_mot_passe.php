<?php
  session_start();
  
  // Vérifier si l'utilisateur est connecté
  if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true) {
    $_SESSION['erreur'] = "Vous devez être connecté pour changer votre mot de passe.";
    header('Location: connexion.php');
    exit();
  }

  $titre = "Changer le mot de passe";
  require_once('../config/param.inc.php');
  include('../includes/header.inc.php');
  include('../includes/menu.inc.php');
?>

<div class="row">
  <div class="col-lg-6 mx-auto">
    <h1>Changer mon mot de passe</h1>
    
    <?php include('../includes/message.inc.php'); ?>
    
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">Modification du mot de passe</h5>
      </div>
      <div class="card-body">
        <form method="POST" action="tt_changer_mot_passe.php">
          
          <div class="mb-3">
            <label for="ancien_password" class="form-label">Mot de passe actuel *</label>
            <input type="password" class="form-control" id="ancien_password" name="ancien_password" placeholder="Votre mot de passe actuel..." required>
          </div>
          
          <div class="mb-3">
            <label for="nouveau_password" class="form-label">Nouveau mot de passe *</label>
            <input type="password" class="form-control" id="nouveau_password" name="nouveau_password" placeholder="Votre nouveau mot de passe..." required minlength="6">
            <div class="form-text">Le mot de passe doit contenir au moins 6 caractères.</div>
          </div>
          
          <div class="mb-3">
            <label for="confirmer_password" class="form-label">Confirmer le nouveau mot de passe *</label>
            <input type="password" class="form-control" id="confirmer_password" name="confirmer_password" placeholder="Confirmez votre nouveau mot de passe..." required minlength="6">
          </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <a href="../pages/tableau_bord.php" class="btn btn-outline-secondary me-md-2">Annuler</a>
            <button type="submit" class="btn btn-warning">Changer le mot de passe</button>
          </div>
          
        </form>
      </div>
    </div>
    
    <div class="mt-3">
      <div class="alert alert-info" role="alert">
        <h6 class="alert-heading">Conseils de sécurité :</h6>
        <ul class="mb-0">
          <li>Utilisez un mot de passe unique et complexe</li>
          <li>Mélangez lettres majuscules, minuscules, chiffres et symboles</li>
          <li>Évitez d'utiliser des informations personnelles</li>
          <li>Changez régulièrement votre mot de passe</li>
        </ul>
      </div>
    </div>
    
  </div>
</div>

<?php
  include('../includes/footer.inc.php');
?>
