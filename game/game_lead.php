<?php

  // Function for showing tests on the console
  function console_log($output, $with_script_tags = true) {
    $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) .
  ');';
    if ($with_script_tags) {
        $js_code = '<script>' . $js_code . '</script>';
    }
    echo $js_code;
  };
  // console_log("testing WITHOUT script",false);
  // console_log("testing WITH script",true);

  $getGameInfoStmt = $pdo->prepare("SELECT * FROM Game WHERE token=:tk");
  $getGameInfoStmt->execute(array(
    ':tk'=>htmlentities($_GET['token'])
  ));
  $getGameInfo = $getGameInfoStmt->fetch(PDO::FETCH_ASSOC);
  $getGameId = (int)$getGameInfo['game_id'];
  $getGameMode = (int)$getGameInfo['mode_id'];

  // To retrieve a game_id based on the token in the URL
  if (isset($_GET['token'])) {
    if ($getGameInfo == false) {
      $_SESSION['message'] = "<div class='message'>The link that you used was either invalid or no longer in use. Please contact your party leader for the current party's link.</div>";
      header("Location: ../index.php?invalid=true");
      exit;
    };
    // echo("<pre>");
    // var_dump($getGameInfo);
    // var_dump($getGameId);
    // echo("</pre>");
  } else {
    $_SESSION['message'] = "<div class='message'>Your link did not include a required token. Talk to your party leader for a completed link.</div>";
    header("Location: ../index.php");
    exit;
  };

  // Checks if you are already in a party
  if (isset($_SESSION['player_id'])) {
    // Checks if you are a member of THIS party arleady
    $matchGameIdStmt = $pdo->prepare("SELECT game_id FROM Player WHERE Player.player_id=:pid");
    $matchGameIdStmt->execute(array(
      ':pid'=>htmlentities($_SESSION['player_id'])
    ));
    $matchGameId = $matchGameIdStmt->fetch(PDO::FETCH_ASSOC)['game_id'];
    if ($matchGameId == $getGameInfo['game_id']) {
      // Checks if you are the "party_head" or not
      if ($getGameInfo['party_head'] == $_SESSION['player_id']) {
        $partyHead = true;
      } else {
        $partyHead = false;
      };
    } else {
      // Checks to see if they are the leader of their current party
      $getPartyHeadStmt = $pdo->prepare("SELECT party_head FROM Game INNER JOIN Player WHERE Player.player_id=:pg AND Game.game_id=Player.game_id");
      $getPartyHeadStmt->execute(array(
        ':pg'=>htmlentities($_SESSION['player_id'])
      ));
      $getPartyHead = $getPartyHeadStmt->fetch(PDO::FETCH_ASSOC)['party_head'];
      if ($_SESSION['player_id'] != $getPartyHead) {
        $_SESSION['message'] = "
          <div class='message'>
            According to our records, you are not a member of ".$getGameInfo['party_name'].". To join it, click
            <form method='POST'>
              <input type='submit' name='resetCharacter' value='HERE' />
            </form>
            and enter a new character</br>
            NOTE: Your current player will be deleted!
          </div>";
        header("Location: ../index.php?token=".$_GET['token']."&invalid=true");
        exit;
      } else {
        $_SESSION['message'] = "
          <div class='message'>
            According to our records, you are currently the Party Leader of another game. Therefore, you cannot join another party until your current game ends.
          </div>";
        header("Location: ../index.php");
        exit;
      };
    };
  } else {
    header("Location: ../join/join.php?token=".$_GET['token']);
    exit;
  };

  $thisPlayerInfoStmt = $pdo->prepare("SELECT * FROM Player WHERE Player.player_id=:pid");
  $thisPlayerInfoStmt->execute(array(
    ':pid'=>htmlentities($_SESSION['player_id'])
  ));
  $thisPlayerInfo = [];
  while ($onePlayerInfo = $thisPlayerInfoStmt->fetch(PDO::FETCH_ASSOC)) {
    $thisPlayerInfo[] = $onePlayerInfo;
  };

  // Starts the game after all members joined
  if (isset($_POST['startTrail'])) {
    $currentCountStmt = $pdo->prepare("SELECT COUNT(player_id) FROM Player WHERE game_id=:gd");
    $currentCountStmt->execute(array(
      ':gd'=>$getGameId
    ));
    $currentCount = (int)$currentCountStmt->fetch(PDO::FETCH_ASSOC)["COUNT(player_id)"];
    if ((int)$getGameInfo['party_size'] == $currentCount) {
      // Mark the game 'active' in the dB
      $activateStmt = $pdo->prepare("UPDATE Game SET active=1 WHERE game_id=:gid");
      $activateStmt->execute(array(
        ':gid'=>$getGameId
      ));
      // Use the updated dB to "activate" in the JSON file
      $inactiveJsonFile = file_get_contents("json/game_".$getGameId."/game_".$getGameId.".json");
      $decodedInactiveJson = json_decode($inactiveJsonFile, true);
      $decodedInactiveJson[0]["active"] = "1";
      $activateGameJson = json_encode($decodedInactiveJson);
      file_put_contents("json/game_".$getGameId."/game_".$getGameId.".json",$activateGameJson);
      // Assign each user their initial trail cards by...
      // ...getting all of the player id's...
      $playerListJson = file_get_contents("json/game_".$getGameId."/player_".$getGameId.".json");
      $decodePlayerList = json_decode($playerListJson,true);
      $playerList = [];
      for ($plyNum = 0; $plyNum < count($decodePlayerList); $plyNum++) {
        $playerList[] = $decodePlayerList[$plyNum]["player_id"];
      };
      // ...then get all of the trail cards...
      $trailListJson = file_get_contents("json/game_".$getGameId."/trail_".$getGameId.".json");
      $decodeTrailList = json_decode($trailListJson,true);
      // ...then assign trail cards to each player.
      $takenTrailCards = [];
      $playerTotal = count($playerList);
      $trailTotal = count($decodeTrailList);
      for ($plNum = 0; $plNum < $playerTotal; $plNum++) {
        for ($pickedTrailNum = 0; $pickedTrailNum < 5; $pickedTrailNum++) {
          $lastTrailNum = $trailTotal - 1;
          $trailNum = rand(0,$lastTrailNum);
          if ($decodeTrailList[$trailNum]["picked_by"] == "0") {
            $decodeTrailList[$trailNum]["picked_by"] = $playerList[$plNum];
          } else {
            $pickedTrailNum--;
          };
        };
      };
      $assignInitTrailCards = json_encode($decodeTrailList);
      file_put_contents("json/game_".$getGameId."/trail_".$getGameId.".json",$assignInitTrailCards);
      // End the start-up process and refresh the game file
      $_SESSION['message'] = "<div style='color:white;background-color:green'>Your travel has begun!</div>";
      header("Location: game.php?token=".$_GET['token']);
      exit;
    } else {
      $_SESSION['message'] = "<div style='color:red;background-color:white'>You planned to have ".(int)$getGameInfo['party_size']." party members, but only ".$currentCount." is/are present. You can wait for your remaining member(s), or end this game to start a new one with the desired party size.</div>";
      header("Location: game.php?token=".$_GET['token']);
      exit;
    };
  };

  // This function is a) carried out within the 'player' POST (see below) and b) only happens if a trail card was used.
  function trailCardUse($pdoParam,$thisGameId) {
    $oneLessStmt = $pdoParam->prepare("UPDATE Game SET until_end = until_end - 1 WHERE game_id=:gm");
    $oneLessStmt->execute(array(
      ':gm'=>$thisGameId
    ));
    $trailLeftStmt = $pdoParam->prepare("SELECT until_end FROM Game WHERE game_id=:ga");
    $trailLeftStmt->execute(array(
      ':ga'=>$thisGameId
    ));
    $trailLeft = $trailLeftStmt->fetch(PDO::FETCH_ASSOC)['until_end'];
    return $trailLeft;
  };

  // // The function to set update the 'current_trail'
  function newCurrentTrail($pdoParam,$thisGameId) {
    $updateCardStmt = $pdoParam->prepare("UPDATE Game SET current_trail = :ct WHERE game_id = :ge");
    $updateCardStmt->execute(array(
      ':ct'=>(int)htmlentities($_POST['cardId']),
      ':ge'=>$thisGameId
    ));
    $getNewTrail = $pdoParam->prepare("SELECT current_trail FROM Game WHERE game_id=:ga");
    $getNewTrail->execute(array(
      ':ga'=>$thisGameId
    ));
    $newTrail = $getNewTrail->fetch(PDO::FETCH_ASSOC)['current_trail'];
    return $newTrail;
  };

  // Turn the next player into the current player
  if (isset($_POST['player'])) {
    $lessTrail = null;
    $currentTrail = $decodedGameJson[0]["current_trail"];
    if ($_POST['action'] == "trail") {
      $lessTrail = trailCardUse($pdo,$getGameId);
      $currentTrail = newCurrentTrail($pdo,$getGameId);
    };
    // First, identify the next player's id number
    $currentPlayer = (int)htmlentities($_POST['player']);
    $allAliveStmt = $pdo->prepare("SELECT player_id FROM Player WHERE game_id=:gg AND alive=1");
    $allAliveStmt->execute(array(
      ':gg'=>$getGameId
    ));
    $allAliveList = [];
    while ($onePlayer = $allAliveStmt->fetch(PDO::FETCH_ASSOC)) {
      $allAliveList[] = $onePlayer;
    };
    $lastPlayerNum = count($allAliveList) - 1;
    $lastPlayerId = (int)$allAliveList[$lastPlayerNum]['player_id'];
    $nextPlayerId;
    $foundCurrent = false;
    for ($playerNum = 0; $playerNum < count($allAliveList); $playerNum++) {
      if ($foundCurrent == false) {
        if ((int)$allAliveList[$playerNum]['player_id'] == $currentPlayer) {
          if ($currentPlayer == $lastPlayerId) {
            $nextPlayerId = (int)$allAliveList[0]['player_id'];
          } else {
            $nextNum = $playerNum + 1;
            $nextPlayerId = (int)$allAliveList[$nextNum]['player_id'];
          };
          $foundCurrent = true;
        };
      };
    };
    // Second, change the current_player in the dB
    $switchPlayerStmt = $pdo->prepare("UPDATE Game SET current_player=:np WHERE game_id=:gid");
    $switchPlayerStmt->execute(array(
      ':np'=>$nextPlayerId,
      ':gid'=>$getGameId
    ));
    // Third, use the updated dB to update the JSON file
    $gameJsonFile = file_get_contents("json/game_".$getGameId."/game_".$getGameId.".json");
    $decodedGameJson = json_decode($gameJsonFile, true);
    $decodedGameJson[0]["current_player"] = strval($nextPlayerId);
    $decodedGameJson[0]["until_end"] = $lessTrail;
    $decodedGameJson[0]["current_trail"] = $currentTrail;
    $updatedGameJson = json_encode($decodedGameJson);
    file_put_contents("json/game_".$getGameId."/game_".$getGameId.".json",$updatedGameJson);
    header("Location: game.php?token=".$_GET['token']);
    exit;
  };

  // To end a party and delete a game, the party leader can use this
  if (isset($_POST['deleteGame'])) {
    $deletePlayersStmt = $pdo->prepare("DELETE FROM Player WHERE Player.game_id=:plg");
    $deletePlayersStmt->execute(array(
      ':plg'=>$getGameId
    ));
    $deleteGameStmt = $pdo->prepare("DELETE FROM Game WHERE game_id=:gi");
    $deleteGameStmt->execute(array(
      ':gi'=>$getGameId
    ));
    unset($_SESSION['player_id']);
    $folderPath = "json/game_".$getGameId;
    $folderContent = glob($folderPath."/*");
    foreach($folderContent as $oneFile) {
      unlink($oneFile);
    };
    rmdir($folderPath);
    $_SESSION['message'] = "<div class='message'>Your game is now deleted.</div>";
    header("Location: ../index.php");
    exit;
  };

  // To 'kill off' a member (in case they stop playing)



  // echo("<pre>");
  // echo("GET:</br>");
  // var_dump($_GET);
  // echo("</pre>");
  //
  // echo("<pre>");
  // echo("POST:</br>");
  // var_dump($_POST);
  // echo("</pre>");
  //
  // echo("<pre>");
  // echo("SESSION:</br>");
  // var_dump($_SESSION);
  // echo("</pre>");
?>
