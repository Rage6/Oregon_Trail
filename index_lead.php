<?php

  // Detects whether this is the local host or not
  if ($_SERVER['HTTP_HOST'] == "localhost:8888") {
    $isLocal = true;
  } else {
    $isLocal = false;
  };

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

  // Lists all of the game modes
  $modeListStmt = $pdo->prepare("SELECT * FROM Mode");
  $modeListStmt->execute();

  // What takes place when a new game is started
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
        // Finds the Route's total length
        $getRouteLengthStmt = $pdo->prepare("SELECT route_length FROM RouteDesign JOIN Mode WHERE RouteDesign.route_id=Mode.route_id AND Mode.mode_id=:md");
        $getRouteLengthStmt->execute(array(
          ':md'=>htmlentities($_POST['modeId'])
        ));
        $routeLength = $getRouteLengthStmt->fetch(PDO::FETCH_ASSOC)['route_length'];
        // This creates the new game...
        $newToken = bin2hex(random_bytes(10));
        $startTime = time();
        $insertGameStmt = $pdo->prepare("INSERT INTO Game (token,start_time,party_name,mode_id,party_size,until_end) VALUES (:tk,:st,:pn,:mi,:ps,:ue)");
        $insertGameStmt->execute(array(
          ':tk'=>$newToken,
          ':st'=>$startTime,
          ':pn'=>htmlentities($_POST['partyName']),
          ':mi'=>htmlentities($_POST['modeId']),
          ':ps'=>htmlentities($_POST['playerTotal']),
          ':ue'=>$routeLength
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
        $updateGameHeadStmt = $pdo->prepare("UPDATE Game SET party_head=:ud, current_player=:cp WHERE game_id=:gd");
        $updateGameHeadStmt->execute(array(
          ':ud'=>$userId,
          ':cp'=>$userId,
          ':gd'=>$gameId
        ));
        // ... and creates the game's new folder...
        mkdir("game/json/game_".$gameId);
        // ... and creates the new game JSON file...
        $gameInfoStmt = $pdo->prepare("SELECT * FROM Game INNER JOIN Mode WHERE game_id=:gid AND Game.mode_id=Mode.mode_id");
        $gameInfoStmt->execute(array(
          ':gid'=>(int)$gameId
        ));
        $gameInfoArray = [];
        while ($oneGameInfo = $gameInfoStmt->fetch(PDO::FETCH_ASSOC)) {
          $gameInfoArray[] = $oneGameInfo;
        };
        $newGameFile = fopen("game/json/game_".$gameId."/game_".$gameId.".json","w");
        fwrite($newGameFile, json_encode($gameInfoArray));
        fclose($newGameFile);
        // ... and creates the player JSON file...
        $playerInfoStmt = $pdo->prepare("SELECT * FROM Player WHERE game_id=:gm");
        $playerInfoStmt->execute(array(
          ':gm'=>(int)$gameId
        ));
        $playerInfoArray = [];
        while ($onePlayerInfo = $playerInfoStmt->fetch(PDO::FETCH_ASSOC)) {
          $playerInfoArray[] = $onePlayerInfo;
        };
        $newPlayerFile = fopen("game/json/game_".$gameId."/player_".$gameId.".json","w");
        fwrite($newPlayerFile, json_encode($playerInfoArray));
        fclose($newPlayerFile);
        // // ... and creates the Trail card file...
        // $trailInfoStmt = $pdo->prepare("SELECT trail_id,picked_by,how_many,top_num,bottom_num,has_calamity,can_drown,lose_supplies,is_fort,is_town FROM Trail JOIN RouteDesign JOIN Mode WHERE Mode.mode_id=:mid AND RouteDesign.route_id=Mode.route_id AND Trail.route_id=RouteDesign.route_id");
        // $trailInfoStmt->execute(array(
        //   ':mid'=>(int)htmlentities($_POST['modeId'])
        // ));
        // $trailInfoArray = [];
        // while ($oneTrailInfo = $trailInfoStmt->fetch(PDO::FETCH_ASSOC)) {
        //   $trailInfoArray[] = $oneTrailInfo;
        // };
        // // This is where to expand the trail cards to the full deck before creating the JSON file
        // $countTrailStmt = $pdo->prepare("SELECT COUNT('trail_id') AS count FROM Trail JOIN RouteDesign JOIN Mode WHERE Mode.mode_id=:mo AND RouteDesign.route_id=Mode.route_id AND Trail.route_id=RouteDesign.route_id");
        // $countTrailStmt->execute(array(
        //   ':mo'=>(int)htmlentities($_POST['modeId'])
        // ));
        // $trailTotal = $countTrailStmt->fetch(PDO::FETCH_ASSOC)['count'];
        // for ($trailNum = 0; $trailNum < $trailTotal; $trailNum++) {
        //   $trailCard = $trailInfoArray[$trailNum];
        //   if ($trailCard['how_many'] > 1) {
        //     $moreTrailCards = $trailCard['how_many'] - 1;
        //     for ($addTrail = 0; $addTrail < $moreTrailCards; $addTrail++) {
        //       $trailInfoArray[] = $trailCard;
        //     };
        //   };
        // };
        copy("game/json/templates/original/trail_template.json","game/json/game_".$gameId."/trail_".$gameId.".json");
        // $newTrailFile = fopen("game/json/game_".$gameId."/trail_".$gameId.".json","w");
        // fwrite($newTrailFile, json_encode($trailInfoArray));
        // fclose($newTrailFile);
        // ... and finally goes to the game itself.
        $_SESSION['message'] = "<div style='color:green'>Your party was created!</div>";
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
  // echo("</pre><div>Server:</div>");
  // echo("<pre>");
  // var_dump($_SERVER);
  // echo("</pre>");
  // if ($isLocal == false) {
  //   echo("<pre>");
  //   var_dump($_ENV);
  //   echo("</pre>");
  // };

?>
