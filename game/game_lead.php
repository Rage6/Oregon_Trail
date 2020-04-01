<?php

  // To retrieve a game_id based on the token in the URL
  if (isset($_GET['token'])) {
    $getGameInfoStmt = $pdo->prepare("SELECT * FROM Game WHERE token=:tk");
    $getGameInfoStmt->execute(array(
      ':tk'=>htmlentities($_GET['token'])
    ));
    $getGameInfo = $getGameInfoStmt->fetch(PDO::FETCH_ASSOC);
    $getGameId = $getGameInfo['game_id'];
    echo("<pre>");
    var_dump($getGameInfo);
    echo("</pre>");
  } else {
    $_SESSION['message'] = "<div style='color:red'>Your link did not include a required token. Talk to your party leader for a completed link.</div>";
    header("Location: ../index.php");
    exit;
  }

  // Checks if you are the "party_head" or not
  if ($getGameInfo['party_head'] == $_SESSION['player_id']) {
    $partyHead = true;
  } else {
    $partyHead = false;
  };

  echo("<pre>");
  var_dump($_GET);
  echo("</pre>");

  echo("<pre>");
  var_dump($_POST);
  echo("</pre>");

  echo("<pre>");
  var_dump($_SESSION);
  echo("</pre>");

?>
