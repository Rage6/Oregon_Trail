$(()=>{

  // console.log("game/js/main.js");

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

});
