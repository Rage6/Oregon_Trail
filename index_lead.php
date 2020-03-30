<?php

  // Takes any players back to their current game, based on any existing player_id
  if (isset($_SESSION['playerId'])) {
    $findPlayerStmt = $pdo->prepare("SELECT token FROM Player INNER JOIN Game WHERE Player.game_id=Game.game_id AND player_id=:pl");
    $findPlayerStmt->execute(array(
      ':pl'=>htmlentities($_SESSION['playerId'])
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
        $_SESSION['message'] = "<div style='color:red'>Your character must have a name</div>";
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
        // ... and this makes the creator's player.
        $gameId = $pdo->lastInsertId();
        $insertLeadPlyrStmt = $pdo->prepare("INSERT INTO Player (username,first_name,last_name,game_id) VALUES (:us,:fn,:ls,:gi)");
        $insertLeadPlyrStmt->execute(array(
          ':us'=>htmlentities($_POST['partyLeader']),
          ':fn'=>htmlentities($_POST['firstName']),
          ':ls'=>htmlentities($_POST['lastName']),
          ':gi'=>$gameId
        ));
        $_SESSION['message'] = "<div style='color:green'>Your party was created!</div>";
        $userId = $pdo->lastInsertId();
        $_SESSION['playerId'] = $userId;
        header("Location: game/game.php?token=".$newToken);
        exit;
      };
    };
  };

  // echo("<div>Post:</div><pre>");
  // var_dump($_POST);
  // echo("</pre><div>Session:</div>");
  // echo("<pre>");
  // var_dump($_SESSION);
  // echo("</pre>");

  echo("<pre>");
  var_dump($allPlayerId);
  echo("</pre>");

?>
