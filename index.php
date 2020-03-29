<?php
  session_start();
  require_once("pdo.php");
  require_once("index_lead.php");
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Oregon Trail Card Game</title>
    <link rel="stylesheet" type="text/css" href="index_css/css_360px.css"></link>
    <script src="index_js/main.js"></script>
  </head>
  <body>
    <div class="headTitle">Oregon Trail</div>
    <?php
      if (isset($_SESSION['message'])) {
        echo($_SESSION['message']);
        unset($_SESSION['message']);
      };
    ?>
    <div>
      <div>
        START A NEW GAME
      </div>
      <form method="POST">
        <input type="text" name="partyName" placeholder="Enter your new party's name"/></br>
        <input type="submit" name="newGame" value="GO!" /></br>
      </form>
      <div>Instructions</div>
      <div>(Don't worry, you can find them during the game too)</div>
    </div>
  </body>
</html>
