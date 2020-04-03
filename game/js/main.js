$(()=>{

  // console.log("game/js/main.js");

  const gameId = $("body").attr('data-game');
  const gameUrl = "json/game_" + gameId + "/game_" + gameId + ".json";

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
    let xhRequest = new XMLHttpRequest();
    xhRequest.open('GET', gameUrl, true);
    xhRequest.onload = () => {
      if (xhRequest.status == 200) {
        let gameData = JSON.parse(xhRequest.responseText);
        let currentPlayer = $("body").attr('data-player');
        if (gameData[0]["current_player"] == currentPlayer) {
          $("#playerStatus").text("It is your turn");
        } else {
          $("#playerStatus").text("");
        };
      }
    };
    xhRequest.onerror = () => {
      console.log();
    };
    xhRequest.send();
  }, 5000);

});
