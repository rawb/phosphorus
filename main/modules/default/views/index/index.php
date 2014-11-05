<?php
echo '<h1>USERS:</h1>';
foreach($users as $user){
    echo '<p> id: '.$user["id"].' name: '.$user["name"].'</p>';
}
?>
