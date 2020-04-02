<?php
session_start();
require_once("../pdo.php");
require_once("join_lead.php");
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Oregon Trail | Join "<?php echo($getGameInfo['party_name']); ?>"</title>
  </head>
  <body>
    <form method="POST">
      <div>
        Your character name:
        <input type="text" name="username" placeholder="required"/>
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
        <input type="submit" name="addPlayer" value="JOIN" />
      </div>
    </form>
  </body>
</html>
