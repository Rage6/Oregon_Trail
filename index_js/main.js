$(document).ready(()=>{

  // Show or hide the form for starting a new game
  $("#newGameBttn").click(()=>{
    if ($("#newGameBox").css('display') == 'none') {
      $("#newGameBox").css('display','block');
    } else {
      $("#newGameBox").css('display','none');
    };
  });



});
