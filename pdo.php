<?php
  $currentHost = $_SERVER['HTTP_HOST'];

  // Will determine where to retrieve the data from
  if ($currentHost == "localhost:8888") {
    $isLocal = true;
    $pdo = new PDO('mysql:host=localhost;port=8888;dbname=Oregon_Trail','Nick','Ike');
  } else {
    $isLocal = false;
    $pdo = new PDO('mysql:host=us-cdbr-iron-east-02.cleardb.net;port=3306;dbname=heroku_9f89bb0196fa398','bb859affb4aa30','*passwrd_goes_here*');
  };

?>
