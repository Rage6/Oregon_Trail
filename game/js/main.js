$(()=>{

  // console.log("game/js/main.js");

  const gameId = $("body").attr('data-game');
  const gameUrl = "json/game_" + gameId + "/game_" + gameId + ".json";

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
    let xhRequest = new XMLHttpRequest();
    xhRequest.open('GET', gameUrl, true);
    xhRequest.onload = () => {
      if (xhRequest.status == 200) {
        let gameData = JSON.parse(xhRequest.responseText);
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
    xhRequest.onerror = () => {
      console.log("An error occurred");
    };
    xhRequest.send();

    // For Player information
  }, 5000);

  $("#clickBox").submit((e)=>{
    e.preventDefault();
    nextTurn();
  });

  const nextTurn = () =>{
    console.log("Player " + currentPlayer + " clicked.");
  }

});
