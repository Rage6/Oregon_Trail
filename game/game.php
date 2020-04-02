<?php

  session_start();
  require_once("../pdo.php");
  require_once("game_lead.php");

?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Oregon Trail | <?php echo($getGameInfo['party_name']); ?></title>
    <link rel="stylesheet" type="text/css" href="style/game_360px.css"></link>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"
    integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
    crossorigin="anonymous"></script>
    <script src="json/game_<?php echo($getGameId) ?>/game_<?php echo($getGameId) ?>.json"></script>
    <script src="js/main.js"></script>
  </head>
  <body>
    <?php
      if ($partyHead == true) {
        echo("
          <div>
            Invitation Link: ".$currentHost."/Oregon_Trail/game/game.php?token=".$_GET['token']."
          </div>
        ");
      };
    ?>
    <div>
      The game starts now.
    </div>
    <?php
      if (isset($_SESSION['message'])) {
        echo($_SESSION['message']);
        // echo("<pre>");
        // var_dump($_SESSION['message']);
        // echo("</pre>");
        unset($_SESSION['message']);
      };
      if ($partyHead == true) {
        echo("
          <div>
            <div id='ldrOptBttn' class='ldrOptBttn'>Party Leader Options</div>
            <div id='ldrOptBox' class='ldrOptBox'>
              <div id='endBttn' class='endBttn'>END GAME</div>
              <div id='endBox' class='endBox'>
                Are you sure that you want to end your trail now? ALL of your party members and your party's progress will end!
                <div>
                  <form method='POST'>
                    <input type='submit' name='deleteGame' value='YES, END OUR TRAIL' />
                  </form>
                  <div>NO, CONTINUE OUR JOURNEY</div>
                </div>
              </div>
            </div>
          </div>
        ");
      }
    ?>

  </body>
</html>
