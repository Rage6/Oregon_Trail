<?php

  session_start();
  require_once("../pdo.php");
  require_once("game_lead.php");

?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Oregon Trail | <?php echo($getGameInfo['party_name']); ?></title>
    <script src="json/game_<?php echo($getGameId) ?>.json"></script>
  </head>
  <body>
    <div>
      Invitation Link: <?php echo($currentHost."/Oregon_Trail?token=".$_GET['token']); ?>
    </div>
    <div>
      The game starts now.
    </div>
    <?php
      if (isset($_SESSION['message'])) {
        echo($_SESSION['message']);
        unset($_SESSION['message']);
      };
      if ($partyHead == true) {
        echo("<div>You are the leader of the party!</div>");
      }
    ?>
  </body>
</html>
