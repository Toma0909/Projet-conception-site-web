<?php
  if(isset($_SESSION['message'])) {
    echo "<div class='container'>";
    echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>";
    echo $_SESSION["message"];
    echo "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
    echo "</div>";
    echo "</div>";
    unset($_SESSION['message']);
  }
  if(isset($_SESSION['erreur'])) {
    echo "<div class='container'>";
    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>";
    echo $_SESSION["erreur"];
    echo "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
    echo "</div>";
    echo "</div>";
    unset($_SESSION['erreur']);
  }
  if(isset($_SESSION['succes'])) {
    echo "<div class='container'>";
    echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>";
    echo $_SESSION["succes"];
    echo "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
    echo "</div>";
    echo "</div>";
    unset($_SESSION['succes']);
  }
  ?>