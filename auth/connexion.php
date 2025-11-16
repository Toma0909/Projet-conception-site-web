<?php
  session_start();
  $titre = "Connexion";
  require_once('../config/param.inc.php');
  include('../includes/header.inc.php');
  include('../includes/menu.inc.php');
?>

<div class="row mb-5">
  <div class="col-lg-10 mx-auto">
    <h1 class="mb-4">Connexion à votre compte</h1>
  
    <?php include('../includes/message.inc.php'); ?>
  
  <form  method="POST" action="tt_connexion.php">
    <div class="row my-3">
      <div class="col-md-6">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control " id="email" name="email" placeholder="Votre email..." required>
      </div>
      <div class="col-md-6">
        <label for="password" class="form-label">Mot de passe</label>
        <input type="password" class="form-control " id="password" name="password" placeholder="Votre mot de passe..." required>
      </div>
    </div>
    <div class="row my-3">
      <div class="d-grid d-md-block">
      <button class="btn btn-outline-primary" type="submit">Connexion</button></div>   
    </div>
  </form>
  </div>
</div>

<?php
  include('../includes/footer.inc.php');
?>