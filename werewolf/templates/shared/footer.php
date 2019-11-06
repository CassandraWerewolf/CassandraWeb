    </div> <!-- end .content -->
  </div> <!-- end .content-wrap -->
   <!-- <img src='games_started_graph.php'> -->
    <footer>
      <div class="cassy-links">
        <div class="cassy-links-section">
          <h3>Cassandra</h3>
          <ul>
            <li><a href='signup.php'>Get a Password</a></li>
            <li><a href='password.php'>Change Password</a></li>
            <li><a href='show_active.php'>Active players and moderators</a></li>
            <li><a href='show_cassandra_files.php'>Current games</a></li>
            <li><a href='show_games_missing_info.php'>Games with missing data</a></li>
            <li><a href='/automod/'>Create your own Automod Template</a></li>
            <li><a href='/stats/'>Fun Statistics</a></li>
            <li><a href='change_log.html' title='Last Updated: <?php echo date("l, d-M-Y", filemtime('change_log.html'));?>'>Change Log</a></li>
          </ul>
        </div>
        <div class="cassy-links-section">
          <h3>Community</h3>
          <ul>
            <li><a href='secrecy_pledge.html'>Our Pledge</a> - Please Read</li>
            <li><a href='wotw.php'>Wolf of the Week List</a></li>
            <li><a href='wolfy_awards.php'>Wolfy Awards</a></li>
            <li><a href='ranks.php'>Player and moderator Ranks</a></li>
            <li><a href='http://boardgamegeek.com/thread/225928'>Player Picture Thread</a> (<a href='game/225928'>By Player</a>)</li>
            <li><a href='timezones.php'>Player Timezone Chart</a></li>
            <li><a href='/social/'>Find WW players Elsewhere</a></li>
            <?php if ( isset($username) ) { ?>
              <li>
                <a href="https://discord.gg/ftUvN3k" target="_blank"><img src="https://img.shields.io/discord/143256979564003328.svg?colorA=8888FF&colorB=d1d1d1" alt="Discord chat"></a>
              </li>
            <?php } ?>
          </ul>
        </div>
        <div class="cassy-links-section">
          <h3>Tools</h3>
          <ul>
            <!-- <li><a href='/tools/aes/'>AES Encryption App</a></li> -->
            <li><a href='/tools/rsa/'>RSA Encryption App</a></li>
            <li><a href='/tools/shamir/'>Shamir Secret Sharing App</a></li>
            <li><a href='/tools/bookmarklets/'>Bookmarklets</a></li>
            <li><a href='/admin/'>Admin Pages</a></li>
          </ul>
        </div>
      </div>
      <div class="cassy-load-time"><?php $timer->end_time(); echo number_format($timer->elapsed_time(), 3) . " seconds"; ?></div>
    </footer>
  </body>
</html>
