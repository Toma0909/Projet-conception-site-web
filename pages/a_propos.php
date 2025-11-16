<?php
  session_start();
  $titre = "À propos de LiftUp";
  require_once('../config/param.inc.php');
  include('../includes/header.inc.php');
  include('../includes/menu.inc.php');
?>

<div class="row mb-5">
  <div class="col-lg-10 mx-auto">
    <h1 class="mb-4">À propos de LiftUp</h1>
    <p class="lead">Votre partenaire de confiance pour tous vos déménagements</p>
  </div>
</div>

<!-- Notre mission -->
<div class="row mb-5">
  <div class="col-lg-10 mx-auto">
    <div class="card border-0 shadow-sm">
      <div class="card-body p-4">
        <h2 class="card-title mb-4">Notre Mission</h2>
        <p class="card-text">
          LiftUp est née d'une volonté simple : faciliter la rencontre entre les particuliers ayant besoin d'un déménagement 
          et les professionnels qualifiés du secteur. Nous croyons qu'un déménagement ne devrait pas être une source de stress, 
          mais une expérience fluide et bien organisée.
        </p>
        <p class="card-text">
          Notre plateforme met en relation des clients avec des déménageurs vérifiés et expérimentés, permettant à chacun 
          de trouver la solution la plus adaptée à ses besoins et à son budget.
        </p>
      </div>
    </div>
  </div>
</div>

<!-- Nos engagements -->
<div class="row mb-5">
  <div class="col-lg-10 mx-auto">
    <h2 class="mb-4">Nos Engagements</h2>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card h-100 border-primary">
          <div class="card-body text-center">
            <div class="display-4 text-primary mb-3">🔒</div>
            <h5 class="card-title">Sécurité</h5>
            <p class="card-text">
              Tous nos déménageurs sont vérifiés et possèdent les assurances nécessaires pour exercer leur activité en toute légalité.
            </p>
          </div>
        </div>
      </div>
      
      <div class="col-md-4">
        <div class="card h-100 border-success">
          <div class="card-body text-center">
            <div class="display-4 text-success mb-3">⭐</div>
            <h5 class="card-title">Qualité</h5>
            <p class="card-text">
              Un système d'évaluation transparent permet à nos clients de noter leurs expériences et de choisir les meilleurs professionnels.
            </p>
          </div>
        </div>
      </div>
      
      <div class="col-md-4">
        <div class="card h-100 border-info">
          <div class="card-body text-center">
            <div class="display-4 text-info mb-3">💰</div>
            <h5 class="card-title">Transparence</h5>
            <p class="card-text">
              Des devis clairs et détaillés, sans frais cachés. Vous savez exactement ce que vous payez avant de vous engager.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Comment ça marche -->
<div class="row mb-5">
  <div class="col-lg-10 mx-auto">
    <h2 class="mb-4">Comment ça marche ?</h2>
    
    <div class="card mb-3">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Pour les Clients</h5>
      </div>
      <div class="card-body">
        <ol class="list-group list-group-numbered list-group-flush">
          <li class="list-group-item">
            <strong>Créez votre compte</strong> - Inscrivez-vous gratuitement en quelques clics
          </li>
          <li class="list-group-item">
            <strong>Publiez votre annonce</strong> - Décrivez votre déménagement avec tous les détails nécessaires
          </li>
          <li class="list-group-item">
            <strong>Recevez des propositions</strong> - Les déménageurs intéressés vous envoient leurs devis
          </li>
          <li class="list-group-item">
            <strong>Comparez et choisissez</strong> - Sélectionnez l'offre qui correspond le mieux à vos attentes
          </li>
          <li class="list-group-item">
            <strong>Déménagez sereinement</strong> - Le jour J, tout est organisé pour que tout se passe bien
          </li>
        </ol>
      </div>
    </div>
    
    <div class="card">
      <div class="card-header bg-success text-white">
        <h5 class="mb-0">Pour les Déménageurs</h5>
      </div>
      <div class="card-body">
        <ol class="list-group list-group-numbered list-group-flush">
          <li class="list-group-item">
            <strong>Inscrivez-vous</strong> - Créez votre profil professionnel avec vos informations et certifications
          </li>
          <li class="list-group-item">
            <strong>Parcourez les annonces</strong> - Consultez les demandes de déménagement disponibles dans votre secteur
          </li>
          <li class="list-group-item">
            <strong>Faites vos propositions</strong> - Envoyez vos devis personnalisés aux clients
          </li>
          <li class="list-group-item">
            <strong>Développez votre activité</strong> - Gagnez de nouveaux clients et fidélisez-les grâce à vos bons services
          </li>
          <li class="list-group-item">
            <strong>Recevez des avis</strong> - Construisez votre réputation grâce aux évaluations de vos clients
          </li>
        </ol>
      </div>
    </div>
  </div>
</div>

<!-- Nos chiffres -->
<div class="row mb-5">
  <div class="col-lg-10 mx-auto">
    <h2 class="mb-4">LiftUp en Chiffres</h2>
    <div class="row text-center g-4">
      <div class="col-md-3">
        <div class="card border-0 bg-light">
          <div class="card-body">
            <h3 class="display-4 text-primary">500+</h3>
            <p class="text-muted">Déménagements réalisés</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 bg-light">
          <div class="card-body">
            <h3 class="display-4 text-success">150+</h3>
            <p class="text-muted">Déménageurs certifiés</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 bg-light">
          <div class="card-body">
            <h3 class="display-4 text-warning">4.8/5</h3>
            <p class="text-muted">Note moyenne</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 bg-light">
          <div class="card-body">
            <h3 class="display-4 text-info">98%</h3>
            <p class="text-muted">Clients satisfaits</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- FAQ -->
<div class="row mb-5">
  <div class="col-lg-10 mx-auto">
    <h2 class="mb-4">Questions Fréquentes</h2>
    <div class="accordion" id="faqAccordion">
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
            LiftUp est-il gratuit ?
          </button>
        </h2>
        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            Oui, l'inscription et la publication d'annonces sont entièrement gratuites pour les clients. 
            Les déménageurs paient une commission uniquement sur les contrats conclus via la plateforme.
          </div>
        </div>
      </div>
      
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
            Comment sont vérifiés les déménageurs ?
          </button>
        </h2>
        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            Nous vérifions systématiquement les assurances professionnelles, les certifications et l'identité 
            de chaque déménageur avant de valider leur inscription. De plus, le système d'avis clients permet 
            de maintenir un haut niveau de qualité.
          </div>
        </div>
      </div>
      
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
            Puis-je annuler un déménagement ?
          </button>
        </h2>
        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            Oui, vous pouvez annuler votre déménagement depuis votre espace client. Les conditions d'annulation 
            dépendent du contrat établi avec le déménageur. Nous vous recommandons de bien lire les conditions 
            avant d'accepter une proposition.
          </div>
        </div>
      </div>
      
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
            Comment sont calculés les tarifs ?
          </button>
        </h2>
        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            Les tarifs sont fixés librement par chaque déménageur en fonction de nombreux critères : 
            distance, volume, nombre de déménageurs nécessaires, présence d'un ascenseur, etc. 
            C'est pourquoi nous vous recommandons de comparer plusieurs devis.
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Call to action -->
<div class="row mb-5">
  <div class="col-lg-10 mx-auto">
    <div class="card bg-primary text-white">
      <div class="card-body text-center p-5">
        <h2 class="card-title mb-3">Prêt à déménager avec LiftUp ?</h2>
        <p class="card-text mb-4">Rejoignez des centaines de clients satisfaits et trouvez le déménageur idéal pour votre projet</p>
        <?php if (isset($_SESSION['connecte']) && $_SESSION['connecte'] === true): ?>
          <?php if ($_SESSION['role'] == 1): ?>
            <a href="../pages/creer_demenagement.php" class="btn btn-light btn-lg">Créer ma demande</a>
          <?php else: ?>
            <a href="../pages/annonces.php" class="btn btn-light btn-lg">Voir les annonces</a>
          <?php endif; ?>
        <?php else: ?>
          <a href="../auth/inscription.php" class="btn btn-light btn-lg">S'inscrire gratuitement</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php
  include('../includes/footer.inc.php');
?>
