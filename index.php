<?php
  session_start();
  require_once("pdo.php");
  require_once('vendor/autoload.php');
  require_once("index_lead.php");
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Oregon Trail Card Game</title>
    <link rel="stylesheet" type="text/css" href="index_css/css_360px.css"></link>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"
    integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
    crossorigin="anonymous"></script>
    <script src="index_js/main.js"></script>
  </head>
  <body>
    <div class='mainBody'>
      <div class="headTitle">
        Oregon Trail
        <div class="headSubtitle">
          <div>The Video Game...</div>
          <div>Based On The Card Game...</div>
          <div>Based On The Video Game</div>
        </div>
      </div>
      <?php
        if (isset($_SESSION['message'])) {
          echo($_SESSION['message']);
          unset($_SESSION['message']);
        };
      ?>
      <div>
        <div id="newGameBttn" class="newGameBttn">
          START A NEW GAME
        </div>
        <div id="newGameBox" class="newGameBox">
          <form method="POST">
            <div>
              Travel party name:
              <input type="text" name="partyName" placeholder="required"/>
            </div>
            <div>
              Number of members:
              <select name="playerTotal">
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
              </select>
            </div>
            <div>
              Your character name:
              <input type="text" name="partyLeader" placeholder="required"/>
            </div>
            <div>
              First name:
              <input type="text" name="firstName" placeholder="optional"/>
            </div>
            <div>
              Last name:
              <input type="text" name="lastName" placeholder="optional"/>
            </div>
            <div>
              <input type="submit" name="newGame" value="GO!" />
            </div>
          </form>
        </div>
        <div class="instructionBox">
          <div>Instructions</div>
          <div>(Don't worry, you can find them during the game too)</div>
        </div>
      </div>
    </div>
  </body>
</html>
