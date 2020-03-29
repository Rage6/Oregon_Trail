<?php

  // echo("<div>Post:</div><pre>");
  // var_dump($_POST);
  // echo("</pre><div>Session:</div>");
  // echo("<pre>");
  // var_dump($_SESSION);
  // echo("</pre>");

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
        header("Location: game/game.php?token=".$newToken);
        exit;
      };
    };
  };

?>
