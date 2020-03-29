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
  </head>
  <body>
    The game starts now.
    <?php
      if (isset($_SESSION['message'])) {
        echo($_SESSION['message']);
        unset($_SESSION['message']);
      };
    ?>
  </body>
</html>
