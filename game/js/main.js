$(()=>{

  // console.log("game/js/main.js");

  const gameId = $("body").attr('data-game');
  const gameUrl = "json/game_" + gameId + "/game_" + gameId + ".json";
  const playerUrl = "json/game_" + gameId + "/player_" + gameId + ".json";

  let currentPlayer = $("body").attr('data-player');

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

  window.setInterval(()=>{
    // For Game information...
    let gameRequest = new XMLHttpRequest();
    gameRequest.open('GET', gameUrl, true);
    gameRequest.onload = () => {
      if (gameRequest.status == 200) {
        let gameData = JSON.parse(gameRequest.responseText);
        console.log(gameData);
        // currentPlayer = $("body").attr('data-player');
        if (gameData[0]["current_player"] == currentPlayer) {
          $("#playerStatus").text("It is your turn");
          if (document.getElementById("clickBttn") == null) {
            $("#clickBox").append("<input id='clickBttn' type='submit' value='DONE'>");
          };
        } else {
          $("#playerStatus").empty();
          $("#clickBttn").remove();
        };
      }
    };
    gameRequest.onerror = () => {
      console.log("An error occurred in gameData");
    };
    gameRequest.send();
    // For Player information
    let playerRequest = new XMLHttpRequest();
    playerRequest.open('GET', playerUrl, true);
    playerRequest.onload = () => {
      if (playerRequest.status == 200) {
        let playerData = JSON.parse(playerRequest.responseText);
        // console.log(playerData);

      };
    };
    playerRequest.onerror = () => {
      console.log("An error occurred in playerData");
    };
    playerRequest.send();

  }, 5000);

  $("#clickBox").submit((e)=>{
    e.preventDefault();
    nextTurn();
  });

  const nextTurn = (e) =>{
    e.preventDefault();
    let playerParam = "playerId=" + currentPlayer;
    let turnRequest = new XMLHttpRequest();
    turnRequest.open('POST','../game_lead.php', true);
    turnRequest.setRequestHeader('Content-type','application/x-www-form-urlencoded');
    turnRequest.onload = () => {
      console.log("turnRequest worked");
    };
    turnRequest.send(playerParam);
    // ...and the next player becomes the current player.
    console.log("Player " + currentPlayer + " clicked.");
  }

});
