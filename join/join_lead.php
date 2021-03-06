<?php

  // To retrieve a game_id based on the token in the URL
  if (isset($_GET['token'])) {
    $getGameInfoStmt = $pdo->prepare("SELECT * FROM Game WHERE token=:tk");
    $getGameInfoStmt->execute(array(
      ':tk'=>htmlentities($_GET['token'])
    ));
    $getGameInfo = $getGameInfoStmt->fetch(PDO::FETCH_ASSOC);
    $getGameId = $getGameInfo['game_id'];
    if ($getGameInfo == false) {
      $_SESSION['message'] = "<div style='color:red'>The game that you were looking for ended.</div>";
      header("Location: ../index.php");
      exit;
    };
  } else {
    $_SESSION['message'] = "<div style='color:red'>Your link did not include a required token. Talk to your party leader for a completed link.</div>";
    header("Location: ../index.php");
    exit;
  }

  // To add the invited player to the party
  if (isset($_POST['addPlayer'])) {
    // Makes sure that at least a username is included
    if ($_POST['username'] == '') {
      $_SESSION['message'] = "<div style='color:red'>Your character must have a name</div>";
      header("Location: join.php?token=".$_GET['token']);
      exit;
    } else {
      // Adds player to the database
      $addPlayerStmt = $pdo->prepare("INSERT INTO Player(username,first_name,last_name,alive,skips_left,is_shop,game_id) VALUES (:un,:fn,:ls,1,0,0,:gi)");
      $addPlayerStmt->execute(array(
        ':un'=>htmlentities($_POST['username']),
        ':fn'=>htmlentities($_POST['firstName']),
        ':ls'=>htmlentities($_POST['lastName']),
        ':gi'=>$getGameId
      ));
      $userId = $pdo->lastInsertId();
      // Adds the new player to the JSON file that lists the party members
      $fromOldFile = file_get_contents('../game/json/game_'.$getGameId.'/player_'.$getGameId.'.json');
      $oldFileList = json_decode($fromOldFile, true);
      $addedPlayer = new stdClass();
      $addedPlayer->player_id = $userId;
      $addedPlayer->username = htmlentities($_POST['username']);
      $addedPlayer->first_name = htmlentities($_POST['firstName']);
      $addedPlayer->last_name = htmlentities($_POST['lastName']);
      $addedPlayer->alive = "1";
      $addedPlayer->skips_left = "0";
      $addedPlayer->is_shop = "0";
      $addedPlayer->game_id = $getGameId;
      $oldFileList[] = $addedPlayer;
      $newFileList = json_encode($oldFileList);
      file_put_contents('../game/json/game_'.$getGameId.'/player_'.$getGameId.'.json', $newFileList);
      // Finally, sets up their player in their browser and sends them into the game
      $_SESSION['player_id'] = $userId;
      $_SESSION['message'] = "<div style='color:green'>Welcome to the party, ".htmlentities($_POST['username'])."</div>";
      header("Location: ../game/game.php?token=".$_GET['token']);
      exit;
    };
  };

?>
