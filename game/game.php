<?php

  session_start();
  require_once("../pdo.php");
  require_once("game_lead.php");
  // require_once("json/game_".$getGameId."/game_".$getGameId.".json");

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
    <!-- <script src="js/main.js"></script> -->
  </head>
  <body data-game="<?php echo($getGameId) ?>" data-player="<?php echo($_SESSION['player_id']) ?>">
    <?php
      if ($partyHead == true) {
        if ($isLocal == true) {
          $localAttachment = "Oregon_Trail/";
        } else {
          $localAttachment = "";
        };
        echo("
          <div class='ldrOpt'>
          </div>
          <div id='ldrOptBox' class='ldrOptBox'>
            <div class='inviteBox'>
              <div id='inviteBttn'>COPY LINK</div>
              <div id='inviteLink' class='inviteLink'>
                ".$currentHost."/".$localAttachment."game/game.php?token=".htmlentities($_GET['token'])."
              </div>
            </div>
            <div class='startBttn'>START TRAIL?</div>");
          if ((int)$getGameInfo['active'] == 0) {
            echo("
            <div class='startBox'>
              Ready to go? No one else can join your party after hitting the trail.
              <form method='POST'>
                <input type='hidden' name='token' value='".$_GET['token']."'/>
                <input type='submit' name='startTrail' value='START' />
              </form>
            </div>");
          };
            echo("
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
        ");
      }
    ?>
    <div class="playerInfo">
      <div class="playerInfoBttn" id="playerInfoBttn">
        <?php echo("<div>".$thisPlayerInfo[0]["username"]."</div>") ?>
        <div id="healthStatus"></div>
      </div>
      <div class="playerInfoBox" id="playerInfoBox">
        <div class="playerInfoContent">
          Full Name: <?php echo($thisPlayerInfo[0]["first_name"]." ".$thisPlayerInfo[0]["last_name"]) ?>
          <?php
            if ($partyHead == true) {
              if ($isLocal == true) {
                $localAttachment = "Oregon_Trail/";
              } else {
                $localAttachment = "";
              };
              echo("<div id='ldrOptBttn' class='ldrOptBttn'>Party Leader Options</div>");
            };
          ?>
        </div>
      </div>
    </div>
    <?php
      if (isset($_SESSION['message'])) {
        echo($_SESSION['message']);
        // echo("<pre>");
        // var_dump($_SESSION['message']);
        // echo("</pre>");
        unset($_SESSION['message']);
      };
      // echo("<pre>".var_dump($thisPlayerInfo)."</pre>")
    ?>
    <div class='ifStarted'>
      <div>Current Player: <span id="currentName"></span></div>
      <div id="playerStatus"></div>
      <form id="nextTurn" class="nextTurn">
        <!-- button is displayed here when it is the player's turn -->
        <button type="submit" class='clickBttn'>DONE</button>
      </form>
    </div>
  </body>
  <script>
  $(()=>{
  // $(document).ready(()=>{

    const gameId = $("body").attr("data-game");
    const gameUrl = "json/game_" + gameId + "/game_" + gameId + ".json";
    const playerUrl = "json/game_" + gameId + "/player_" + gameId + ".json";
    const currentPlyUrl = "game.php?token=<?php echo($_GET['token']); ?>";

    let thisPlayer = $("body").attr("data-player");
    let currentGameData = null;

    // Opens, closes the 'Party Leader' options
    const openOrClose = (box) => {
      if ($(box).css('display') == "none") {
        $(box).css('display','block')
      } else {
        $(box).css('display','none')
      };
    };

    // Open, close the player's basic info
    $("#playerInfoBttn").click(()=>{
      openOrClose(".playerInfoBox");
    });

    // Opens, closes the 'Party Leader' options
    $("#ldrOptBttn").click(()=>{
      openOrClose(".ldrOpt");
      openOrClose(".ldrOptBox");
    });

    // Opens, closes the 'End Game' option
    $("#endBttn").click(()=>{
      openOrClose(".endBox");
    });

    // Checks data every 5 seconds
    let intervalTool;
    const runIntervalTool = ()=> {
      intervalTool = setInterval(checkCurrentData, 5000);
    };

    // Uses the updated Game data
    const gmUpdateScreen = (gmData) => {
      if (gmData[0]['active'] == "1") {
        $(".ifStarted").css("display","block");
        $(".startBox").css("display","none");
        $(".startBttn").css("display","none");
      } else {
        $(".ifStarted").css("display","none");
      };
      // The below if/else determines whether to display the .clickBttn option  or not based on whether the current player's id (in JSON) is the same as their player id (in a data attribute in their HTML)
      if (gmData[0]["current_player"] == thisPlayer) {
        if ($(".clickBttn").css('display') == "none") {
          $("#playerStatus").text("It is your turn");
          $(".clickBttn").css("display","block");
        };
      } else {
        $("#playerStatus").empty();
        $(".clickBttn").css("display","none");
      };
    };

    // Uses the updated Player data
    const plyUpdateScreen = (plyData,gmeData) => {
      for (userNum = 0; userNum < plyData.length; userNum++) {
        // Shows who the current player is
        if (plyData[userNum]["player_id"] == gmeData[0]["current_player"]) {
          $("#currentName").text(plyData[userNum]["username"]);
        };
        // Shows if the user's character is alive or not
        if (thisPlayer == plyData[userNum]["player_id"]) {
          if (plyData[userNum]["alive"] == "1") {
            $("#healthStatus").text("ALIVE");
          } else {
            $("#healthStatus").text("DEAD");
          };
        };
      };
      gmUpdateScreen(gmeData);
    };

    // Requests Player update from JSON
    const playerRequest = (gmData) => {
      let playerRequest = new XMLHttpRequest();
      let playerUrlWithTime = playerUrl + "?time=" + Date.now();
      playerRequest.open('GET', playerUrlWithTime, true);
      playerRequest.onload = () => {
        if (playerRequest.status == 200) {
          let playerData = JSON.parse(playerRequest.responseText);
          console.log(playerData);
          plyUpdateScreen(playerData,gmData);
        };
      };
      playerRequest.onerror = () => {
        console.log("An error occurred in playerData");
      };
      playerRequest.send();
    };

    // Function for retrieving necessary Player and Game data
    const checkCurrentData = () => {
      // This gets current Game information...
      let gameRequest = new XMLHttpRequest();
      let gameUrlWithTime = gameUrl + "?time=" + Date.now();
      gameRequest.open('GET', gameUrlWithTime, true);
      gameRequest.onload = () => {
        if (gameRequest.status == 200) {
          gameData = JSON.parse(gameRequest.responseText);
          currentGameData = gameData;
          console.log(gameData);
          playerRequest(gameData);
        };
      };
      gameRequest.onerror = () => {
        console.log("An error occurred in gameData");
      };
      gameRequest.send();
    };

    // Completes a player's turn and switches to the next player
    const switchPlayer = (e)=>{
      e.preventDefault();
      let playerParam = "player=" + gameData[0]["current_player"];
      console.log(playerParam);
      let turnRequest = new XMLHttpRequest();
      turnRequest.onload = () => {
        // console.log("onload on switchPlayer");
      };
      turnRequest.open('POST',currentPlyUrl,true);
      turnRequest.setRequestHeader('Content-type','application/x-www-form-urlencoded');
      turnRequest.send(playerParam);
      // ...and the next player becomes the current player.
    };

    // $(".clickBttn").click(switchPlayer);
    document.getElementById('nextTurn').addEventListener('submit',switchPlayer);

    // Initial data check
    checkCurrentData();

    // Runs the data checks every 5 seconds
    runIntervalTool();

  });
  </script>
</html>
