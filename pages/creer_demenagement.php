<?php
  session_start();
  
  // Configuration
  require_once(__DIR__ . "/../config/param.inc.php");
  
  // Vérifier si l'utilisateur est connecté et est un client
  if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true) {
    $_SESSION['erreur'] = "Vous devez être connecté pour créer une demande de déménagement.";
    header('Location: ../auth/connexion.php');
    exit();
  }
  
  if ($_SESSION['role'] != 1) {
    $_SESSION['erreur'] = "Seuls les clients peuvent créer des demandes de déménagement.";
    header('Location: tableau_bord.php');
    exit();
  }
  
  $titre = "Créer une demande de déménagement";
  include('../includes/header.inc.php');
  include('../includes/menu.inc.php');
  include('../includes/message.inc.php');
?>

<div class="row">
  <div class="col-lg-10 mx-auto">
    <h1>Créer une demande de déménagement</h1>
    <p class="lead">Remplissez le formulaire ci-dessous pour publier votre annonce</p>
    
    <form method="POST" action="tt_creer_demenagement.php" enctype="multipart/form-data">
      
      <!-- Informations générales -->
      <div class="card mb-4">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">Informations générales</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label for="titre" class="form-label">Titre de l'annonce *</label>
            <input type="text" class="form-control" id="titre" name="titre" 
                   placeholder="Ex: Déménagement appartement 3 pièces" required>
          </div>
          
          <div class="mb-3">
            <label for="description" class="form-label">Description *</label>
            <textarea class="form-control" id="description" name="description" rows="4" 
                      placeholder="Décrivez votre déménagement en détail..." required></textarea>
            <div class="form-text">Indiquez tous les détails importants pour les déménageurs.</div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="date_demenagement" class="form-label">Date du déménagement *</label>
              <input type="date" class="form-control" id="date_demenagement" name="date_demenagement" 
                     min="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="heure_debut" class="form-label">Heure de début *</label>
              <input type="time" class="form-control" id="heure_debut" name="heure_debut" required>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="ville_depart" class="form-label">Ville de départ *</label>
              <input type="text" class="form-control" id="ville_depart" name="ville_depart" 
                     placeholder="Ex: Paris" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="ville_arrivee" class="form-label">Ville d'arrivée *</label>
              <input type="text" class="form-control" id="ville_arrivee" name="ville_arrivee" 
                     placeholder="Ex: Lyon" required>
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
                 value="maison" autocomplete="off" required>
          <input type="radio" class="btn-check" name="depart_type" id="depart_appartement" 
                 value="appartement" autocomplete="off" required>
          
          <div class="mb-3">
            <label class="form-label">Type de logement *</label>
            <div class="btn-group w-100" role="group">
              <label class="btn btn-outline-primary" for="depart_maison">Maison</label>
              <label class="btn btn-outline-primary" for="depart_appartement">Appartement</label>
            </div>
          </div>
          
          <div id="depart_details" class="logement-details-hidden">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="depart_etage" class="form-label">Étage</label>
                <input type="number" class="form-control" id="depart_etage" name="depart_etage" 
                       min="0" max="50" placeholder="0 pour rez-de-chaussée">
              </div>
              <div class="col-md-6 mb-3">
                <label for="depart_ascenseur" class="form-label">Ascenseur disponible ?</label>
                <select class="form-select" id="depart_ascenseur" name="depart_ascenseur">
                  <option value="0">Non</option>
                  <option value="1">Oui</option>
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
                 value="maison" autocomplete="off" required>
          <input type="radio" class="btn-check" name="arrivee_type" id="arrivee_appartement" 
                 value="appartement" autocomplete="off" required>
          
          <div class="mb-3">
            <label class="form-label">Type de logement *</label>
            <div class="btn-group w-100" role="group">
              <label class="btn btn-outline-primary" for="arrivee_maison">Maison</label>
              <label class="btn btn-outline-primary" for="arrivee_appartement">Appartement</label>
            </div>
          </div>
          
          <div id="arrivee_details" class="logement-details-hidden">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="arrivee_etage" class="form-label">Étage</label>
                <input type="number" class="form-control" id="arrivee_etage" name="arrivee_etage" 
                       min="0" max="50" placeholder="0 pour rez-de-chaussée">
              </div>
              <div class="col-md-6 mb-3">
                <label for="arrivee_ascenseur" class="form-label">Ascenseur disponible ?</label>
                <select class="form-select" id="arrivee_ascenseur" name="arrivee_ascenseur">
                  <option value="0">Non</option>
                  <option value="1">Oui</option>
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
                     placeholder="Ex: 30">
              <div class="form-text">Facultatif</div>
            </div>
            <div class="col-md-4 mb-3">
              <label for="poids" class="form-label">Poids approximatif (kg)</label>
              <input type="number" step="0.1" class="form-control" id="poids" name="poids" 
                     placeholder="Ex: 500">
              <div class="form-text">Facultatif</div>
            </div>
            <div class="col-md-4 mb-3">
              <label for="nombre_demenageurs" class="form-label">Nombre de déménageurs *</label>
              <input type="number" class="form-control" id="nombre_demenageurs" name="nombre_demenageurs" 
                     min="1" max="10" value="2" required>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Images -->
      <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
          <h5 class="mb-0">Photos (optionnel)</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label for="images" class="form-label">Ajouter des photos</label>
            <input type="file" class="form-control" id="images" name="images[]" 
                   accept="image/*" multiple>
            <div class="form-text">Vous pouvez ajouter plusieurs photos pour montrer la configuration du logement ou des objets encombrants.</div>
          </div>
        </div>
      </div>
      
      <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-4">
        <a href="mes_demenagements.php" class="btn btn-outline-secondary me-md-2">Annuler</a>
        <button type="submit" class="btn btn-primary">Publier l'annonce</button>
      </div>
    </form>
  </div>
</div>

<?php
  include('../includes/footer.inc.php');
?>
