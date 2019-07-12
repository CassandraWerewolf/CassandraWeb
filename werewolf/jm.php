<?php
            require_once dirname(__FILE__)."/pfc/src/pfcinfo.class.php";
            $info  = new pfcInfo( md5("cassy1") );
            // NULL is used to get all the connected users, but you can specify
            // a channel name to get only the connected user on a specific channel
            $users = $info->getOnlineNick(NULL);
            echo '<div align="center">';
            $info = "";
            $nb_users = count($users);
            if ($nb_users <= 0)
              $info = "<center>%d users in the Chat Room</center>";
            echo "</div>";
            echo "<div>";
            echo "<p>".sprintf($info, $nb_users)."</p>";
            echo "<ol>";
            foreach($users as $u)
            {
              echo "<li>".$u."</li>";
            }
            echo "</ol>";
            echo "</div>";
            ?>
