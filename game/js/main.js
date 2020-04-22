$(document).ready(()=>{

  const gameId = $("body").attr("data-game");
  const gameUrl = "json/game_" + gameId + "/game_" + gameId + ".json";
  const playerUrl = "json/game_" + gameId + "/player_" + gameId + ".json";
  const currentPlyUrl = "../game/game.php";

  let currentPlayer = $("body").attr("data-player");

  // Opens, closes the 'Party Leader' options
  const openOrClose = (box) => {
    if ($(box).css('display') == "none") {
      $(box).css('display','block')
    } else {
      $(box).css('display','none')
    };
  };

  // Opens, closes the 'Party Leader' options
  $("#ldrOptBttn").click(()=>{
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

  // Function for retrieving necessary Player and Game data
  const checkCurrentData = () => {
    // This gets current Game information...
    let gameRequest = new XMLHttpRequest();
    gameRequest.open('GET', gameUrl, true);
    gameRequest.onload = () => {
      if (gameRequest.status == 200) {
        let gameData = JSON.parse(gameRequest.responseText);
        // The below if/else determines whether to display the .clickBttn option  or not based on whether the current player's id (in JSON) is the same as their player id (in a data attribute in their HTML)
        if (gameData[0]["current_player"] == currentPlayer) {
          if ($(".clickBttn").val() == null) {
            $("#playerStatus").text("It is your turn");
            $(".clickBttn").css("display","block");
          };
        } else {
          $("#playerStatus").empty();
          $(".clickBttn").css("display","none");
        };
      };
    };
    gameRequest.onerror = () => {
      console.log("An error occurred in gameData");
    };
    gameRequest.send();
    // This gets current Player information
    let playerRequest = new XMLHttpRequest();
    playerRequest.open('GET', playerUrl, true);
    playerRequest.onload = () => {
      if (playerRequest.status == 200) {
        let playerData = JSON.parse(playerRequest.responseText);
        console.log(playerData);
      };
    };
    playerRequest.onerror = () => {
      console.log("An error occurred in playerData");
    };
    playerRequest.send();
  };

  $(".clickBttn").click((e)=>{
    console.log(gameId);
    // e.preventDefault();
    // let gameJsonUrl = "json/game_" + gameId + "/game_" + gameId + ".json";
    // let playerParam = "playerId=" + currentPlayer;
    // let turnRequest = new XMLHttpRequest();
    // turnRequest.open('POST',currentPlyUrl,true);
    // turnRequest.setRequestHeader('Content-type','application/x-www-form-urlencoded');
    // turnRequest.onload = () => {
    //   console.log("turnRequest worked");
    // };
    // turnRequest.send(playerParam);
    // // ...and the next player becomes the current player.
  });

  // Initial data check
  checkCurrentData();

  // Runs the data checks every 5 seconds
  runIntervalTool();

});
