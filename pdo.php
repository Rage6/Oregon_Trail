<?php
  $currentHost = $_SERVER['HTTP_HOST'];

  // Will determine where to retrieve the data from
  if ($currentHost == "localhost:8888") {
    $isLocal = true;
    $pdo = new PDO('mysql:host=localhost;port=8888;dbname=Oregon_Trail','Nick','Ike');
  } else {
    $isLocal = false;
    $pdo = new PDO('mysql:us-cdbr-iron-east-01.cleardb.net;port=3306;dbname=heroku_a1c8419498096ac','b69578920b8ccd','');
  };

?>
