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
          <div id='ldrOptBox' class='ldrOptBox'>
            <div class='inviteBox'>
              <div id='inviteBttn' class='inviteBttn'>
                COPY LINK
              </div>
              <div id='inviteLink' class='inviteLink'>
                ".$currentHost."/".$localAttachment."game/game.php?token=".htmlentities($_GET['token'])."
              </div>
            </div>
            <div>
              <u>Party Members</u>
            </div>
            <div class='playerList'>
            </div>");
          if ((int)$getGameInfo['active'] == "0") {
            echo("
            <div class='startBox'>
              <div class='startBttn'>
                START TRAIL
              </div>
              <div class='startContent'>
                Once the chosen number of members have joined your party, click 'START' and hit the trail.
                <form method='POST' class='startForm'>
                  <input type='hidden' name='token' value='".$_GET['token']."'/>
                  <input type='submit' name='startTrail' value='START' />
                </form>
              </div>
            </div>");
          };
            echo("

            <div id='endBttn' class='endBttn'>
              END GAME
            </div>
            <div id='endBox' class='endBox'>
              Are you sure that you want to end your trail now? ALL of your party members and your party's progress will end!
              <div>
                <form method='POST'>
                  <input type='submit' name='deleteGame' value='YES, END OUR TRAIL' />
                </form>
                <div>
                  NO, CONTINUE OUR JOURNEY
                </div>
              </div>
            </div>
          </div>
        ");
      } else {
        echo("
          <div class='followBox'>
            <div class='startFollowBox'>
              <div>
                <u>Party Members</u>
              </div>
              <div class='playerList'>
              </div>
              <div>Your travel will begin shortly</div>
            </div>
          </div>
        ");
      };
    ?>
    <div class="playerInfo">
      <div class="playerInfoBttn" id="playerInfoBttn">
        <?php echo("<div>".$thisPlayerInfo[0]["username"]."</div>") ?>
        <div id="healthStatus">
        </div>
      </div>
      <div class="playerInfoBox" id="playerInfoBox">
        <div class="playerInfoContent">
          <div>
            <u>Party Members</u>
          </div>
          <div class="playerList">
          </div>
          <?php
            if ($partyHead == true) {
              if ($isLocal == true) {
                $localAttachment = "Oregon_Trail/";
              } else {
                $localAttachment = "";
              };
              echo("
          <div id='ldrOptBttn' class='ldrOptBttn'>
            Party Leader Options
          </div>");
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
      <div>
        Current Player: <span id="currentName"></span>
      </div>
      <div>
        To Destination: <span id="currentTrail"></span>
      </div>
      <div id="playerStatus">
      </div>
      <div class="yourTurnBox">
        <div style="display:flex;justify-content:space-between">
          <div id="trailCard">TRAIL CARD</div>
          <div>SUPPLY CARD</div>
        </div>
        <form id="nextTurn" class="nextTurn">
          <!-- button is displayed here when it is the player's turn -->
          <button type="submit" class='clickBttn'>
            DONE
          </button>
        </form>
      </div>
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

    let turnOver = false;

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

    // To mark this as the first run on the interval cycle
    let firstRun = true;

    // Uses the updated Game data
    const gmUpdateScreen = (gmData) => {
      if (gmData[0]['active'] == "1") {
        $(".ifStarted").css("display","block");
        $(".startBox").css("display","none");
        $(".followBox").css("display","none");
        if (firstRun == true) {
          $(".ldrOptBox").css("display","none");
          firstRun = false;
        };
        turnOver = false;
        $(".clickBttn")
          .css("background-color","green");
      } else {
        $(".ifStarted").css("display","none");
      };
      $("#currentTrail").text(gmData[0]["until_end"]);
      // The below if/else determines whether to display the .yourTurnBox element or not based on whether the current player's id (in JSON) is the same as their player id (in a data attribute in their HTML)
      if (gmData[0]["current_player"] == thisPlayer) {
        if ($(".yourTurnBox").css('display') == "none") {
          // $("#playerStatus").text("It is your turn");
          $(".yourTurnBox").css("display","block");
        };
      } else {
        // $("#playerStatus").empty();
        $(".yourTurnBox").css("display","none");
      };
    };

    // Uses the updated Player data
    const plyUpdateScreen = (plyData,gmeData) => {
      $(".playerList").empty();
      $(".playerList").append("\
        <div class='playerRow topRow'>\
          <div>Username</div>\
          <div>Full Name</div>\
          <div>Health</div>\
        </div>");
      for (userNum = 0; userNum < plyData.length; userNum++) {
        $(".playerList").append("\
          <div class='playerRow'>\
            <div>"+plyData[userNum]["username"]+"</div>\ <div>"+plyData[userNum]["first_name"]+" "+plyData[userNum]["last_name"]+"</div>\
            <div>"+plyData[userNum]["alive"]+"</div>\
          </div>");
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

    // The cardAction shows whether a trail or supply card is being used
    cardAction = null;
    $("#trailCard").click(()=>{
      cardAction = "trail";
      console.log("Trail Card selected: " + cardAction);
    });

    // Completes a player's turn and switches to the next player
    const switchPlayer = (e)=>{
      e.preventDefault();
      if (turnOver == false) {
        let playerParam = "player=" + window.encodeURIComponent(gameData[0]["current_player"]);
        let actionParam = "&action=" + window.encodeURIComponent(cardAction);
        let fullParam = playerParam + actionParam;
        console.log(fullParam);
        let turnRequest = new XMLHttpRequest();
        turnRequest.onload = () => {
          // console.log("onload on switchPlayer");
        };
        turnRequest.open('POST',currentPlyUrl,true);
        turnRequest.setRequestHeader('Content-type','application/x-www-form-urlencoded');
        turnRequest.send(fullParam);
        // ...and the next player becomes the current player.
        // To make sure this function is carried out only once...
        turnOver = true;
        cardAction = null;
        $(".clickBttn")
          .css("background-color","lightgrey");
      } else {
        console.log("It already happened");
      };
    };

    // $(".clickBttn").click(switchPlayer);
    document.getElementById('nextTurn').addEventListener('submit',switchPlayer);

    // Initial data check
    checkCurrentData();

    // // Closes the Leader Options after starting the game
    // if (currentGameData[0]['active'] == "1") {
    //   $(".ldrOptBox").css("display","none");
    // };

    // Runs the data checks every 5 seconds
    runIntervalTool();

  });
  </script>
</html>
