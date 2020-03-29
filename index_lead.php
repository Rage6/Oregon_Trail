<?php

  // echo("<div>Post:</div><pre>");
  // var_dump($_POST);
  // echo("</pre><div>Session:</div>");
  // echo("<pre>");
  // var_dump($_SESSION);
  // echo("</pre>");

  if (isset($_POST['newGame'])) {
    if ($_POST['partyName'] == '') {
      $_SESSION['message'] = "<div style='color:red'>Your party must have a name</div>";
      header("Location: index.php");
      exit;
    } else {
      $newToken = bin2hex(random_bytes(10));
      // $timestamp = time();
      $insertGameStmt = $pdo->prepare("INSERT INTO Game (token,party_name,party_size,until_end) VALUES (:tk,:pn,:ps,:ue)");
      $insertGameStmt->execute(array(
        ':tk'=>$newToken,
        ':pn'=>htmlentities($_POST['partyName']),
        ':ps'=>4,
        ':ue'=>40
      ));
      $_SESSION['message'] = "<div style='color:green'>Party created</div>";
      header("Location: index.php");
      exit;
    };
  };

?>
