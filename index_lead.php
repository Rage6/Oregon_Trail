<?php

  // Takes any players back to their current game, based on any existing player_id
  if (isset($_SESSION['player_id']) && !isset($_GET['invalid'])) {
    $findPlayerStmt = $pdo->prepare("SELECT token FROM Player INNER JOIN Game WHERE Player.game_id=Game.game_id AND player_id=:pl");
    $findPlayerStmt->execute(array(
      ':pl'=>htmlentities($_SESSION['player_id'])
    ));
    $allPlayerId = [];
    while ($onePlayerId = $findPlayerStmt->fetch(PDO::FETCH_ASSOC)) {
      $allPlayerId[] = $onePlayerId;
    };
    if (count($allPlayerId) == 1) {
      $currentToken = $allPlayerId[0]['token'];
      header("Location: game/game.php?token=".$currentToken);
      exit;
    } else {
      $_SESSION['message'] = "<div style='color:red'>Your prior game must have ended.</div>";
      unset($_SESSION);
      header("Location: index.php");
      exit;
    };
  };

  if (isset($_POST['newGame'])) {
    if ($_POST['partyName'] == '') {
      $_SESSION['message'] = "<div style='color:red'>Your party must have a name</div>";
      header("Location: index.php");
      exit;
    } else {
      if ($_POST['partyLeader'] == '') {
        header("Location: index.php");
        exit;
      } else {
        // This creates the new game...
        $newToken = bin2hex(random_bytes(10));
        $startTime = time();
        $insertGameStmt = $pdo->prepare("INSERT INTO Game (token,start_time,party_name,party_size,until_end) VALUES (:tk,:st,:pn,:ps,:ue)");
        $insertGameStmt->execute(array(
          ':tk'=>$newToken,
          ':st'=>$startTime,
          ':pn'=>htmlentities($_POST['partyName']),
          ':ps'=>htmlentities($_POST['playerTotal']),
          ':ue'=>40
        ));
        // ... and this makes the creator's player...
        $gameId = $pdo->lastInsertId();
        $insertLeadPlyrStmt = $pdo->prepare("INSERT INTO Player (username,first_name,last_name,game_id) VALUES (:us,:fn,:ls,:gi)");
        $insertLeadPlyrStmt->execute(array(
          ':us'=>htmlentities($_POST['partyLeader']),
          ':fn'=>htmlentities($_POST['firstName']),
          ':ls'=>htmlentities($_POST['lastName']),
          ':gi'=>$gameId
        ));
        // ... and this puts the creator's ID into the game's "party_head" column...
        $userId = $pdo->lastInsertId();
        $_SESSION['player_id'] = $userId;
        $updateGameHeadStmt = $pdo->prepare("UPDATE Game SET party_head=:ud WHERE game_id=:gd");
        $updateGameHeadStmt->execute(array(
          ':ud'=>$userId,
          ':gd'=>$gameId
        ));
        // ... and creates the game's new JSON folder w/ files.
        mkdir("game/json/game_".$gameId);
        $gameInfoStmt = $pdo->prepare("SELECT * FROM Game WHERE game_id=:gid");
        $gameInfoStmt->execute(array(
          ':gid'=>(int)$gameId
        ));
        $gameInfoArray = [];
        while ($oneGameInfo = $gameInfoStmt->fetch(PDO::FETCH_ASSOC)) {
          $gameInfoArray[] = $oneGameInfo;
        };
        $gameInfoArray[0]["current_player"] = $userId;
        $newFile = fopen("game/json/game_".$gameId."/game_".$gameId.".json","w");
        fwrite($newFile, json_encode($gameInfoArray));
        fclose($newFile);
        $_SESSION['message'] = "<div style='color:green'>Your party was created!</div>";
        // $_SESSION['message'] = $gameInfoArray;
        header("Location: game/game.php?token=".$newToken);
        exit;
      };
    };
  };

  if (isset($_POST['resetCharacter'])) {
    // This deletes a user's current character from another game
    if (isset($_SESSION['player_id'])) {
      $delCurrentPlyrStmt = $pdo->prepare("DELETE FROM Player WHERE player_id=:pid");
      $delCurrentPlyrStmt->execute(array(
        ':pid'=>htmlentities($_SESSION['player_id'])
      ));
      header("Location: join/join.php?token=".$_GET['token']);
      exit;
    };
  };

  // echo("<div>Post:</div><pre>");
  // var_dump($_POST);
  // echo("</pre><div>Session:</div>");
  // echo("<pre>");
  // var_dump($_SESSION);
  // echo("</pre>");

?>
