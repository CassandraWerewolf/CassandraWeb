<div class='page-title'>
    <h1>BGG Werewolf Stats</h1>
</div>

<table class='games-counts forum_table' border='0'>
    <tr>
        <th>Total</th>
        <th>Won by Evil</th>
        <th>Won by Good</th>
        <th>Other type of game</th>
        <th>In Progress</th>
    </tr>
    <tr>
        <?php foreach ( $games->get_stats() as $type => $count ) { ?>
            <td><a href='show_games.php?type=<?php echo $type ?>'><?php echo $count ?></a></td>
        <?php } ?>
    </tr>
</table>


<div class='games-index'>
    <div class='games-index-column games-index-column--progress'>
        
        <?php if ( count($games_in_fast_progress) > 0 ) {?>
            <table width='100%' class='games-index--section forum_table' cellpadding='2'>
                <tr>
                    <th colspan=2>In Progress (Fast)</th>
                    <th>Day</th>
                    <th>Night</th>
                </tr>
                <?php foreach ( $games_in_fast_progress as $game ) { ?>
                    <tr>
                        <td><?php echo $game['number'] . ")" ?></td>
                        <td>
                            <?php if ($game['notifications']['needs_replacement']) { ?>
                                <img src='/images/i_replace.png' border='0'/>
                            <?php } ?>
                            <?php if ($game['notifications']['signed_up']) { ?>
                                <img src='/images/calendar.png' />
                            <?php } ?>
                            <?php if ($game['notifications']['new_chat']) { ?>
                                <a href='/game/<?php echo $game['thread_id'] ?>/chat'><img src='/images/new_message.png' border='0'/></a>
                            <?php } ?>
                            <a href='/game/<?php echo $game['thread_id'] ?>'><?php echo $game['title'] ?></a>
                        </td>
                        <td><?php echo $game['day_length'] ?></td>
                        <td><?php echo $game['night_length'] ?></td>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>

        <table width='100%' class='games-index--section forum_table' cellpadding='2'>
            <tr>
                <th colspan=2>In Progress (Standard)</th>
                <th>Day</th>
                <th>Night</th>
            </tr>
            <?php foreach ( $games_in_standard_progress as $game ) { ?>
                <tr>
                    <td><?php echo $game['number'] . ")" ?></td>
                    <td>
                        <?php if ($game['notifications']['needs_replacement']) { ?>
                            <img src='/images/i_replace.png' border='0'/>
                        <?php } ?>
                        <?php if ($game['notifications']['signed_up']) { ?>
                            <img src='/images/calendar.png' />
                        <?php } ?>
                        <?php if ($game['notifications']['new_chat']) { ?>
                            <a href='/game/<?php echo $game['thread_id'] ?>/chat'><img src='/images/new_message.png' border='0'/></a>
                        <?php } ?>
                        <a href='/game/<?php echo $game['thread_id'] ?>'><?php echo $game['title'] ?></a>
                    </td>
                    <td><?php echo $game['lynch_time'] ?></td>
                    <td><?php echo $game['na_deadline'] ?></td>
                </tr>
            <?php } ?>
        </table>

        <table width='100%' class='forum_table' cellpadding='2'>
            <tr><th colspan=2>10 Most Recently Ended</th></tr>
            <?php foreach ( $games_recently_ended as $game ) { ?>
                <tr>
                    <td><?php echo $game['number'] . ")" ?></td>
                    <td>
                        <?php if ($game['notifications']['signed_up']) { ?>
                            <img src='/images/calendar.png' />
                        <?php } ?>
                        <?php if ($game['notifications']['new_chat']) { ?>
                            <a href='/game/<?php echo $game['thread_id'] ?>/chat'><img src='/images/new_message.png' border='0'/></a>
                        <?php } ?>
                        <a href='/game/<?php echo $game['thread_id'] ?>'><?php echo $game['title'] ?></a>
                    </td>
                </tr>
            <?php } ?>
        </table>

    </div>

    <div class='games-index-column games-index-column--signup'>

        <table width='100%' class='games-index--section forum_table' cellpadding='2'>
            <tr>
                <th>In Signup (Fast)</th>
                <th>Moderator</th>
                <th>Start Date</th>
                <th>Dusk</th>
                <th>Dawn</th>
            </tr>
            <?php foreach ( $games_in_fast_signup as $game ) { ?>
                <tr>
                    <td>
                        <img src='/images/<?php echo $game['complex'] ?>_small.png' alt='<?php echo $game['complex'] ?>' />
                        <?php if ($game['notifications']['signed_up']) { ?>
                            <img src='/images/calendar.png' />
                        <?php } ?>
                        <?php if ($game['notifications']['new_chat']) { ?>
                            <a href='/game/<?php echo $game['thread_id'] ?>/chat'><img src='/images/new_message.png' border='0'/></a>
                        <?php } ?>
                        <a href='/game/<?php echo $game['thread_id'] ?>'><?php echo $game['title'] ?></a>
                        <span>
                            (<?php echo ($game['num_players'] == $game['max_players'] ? "Full" : $game['num_players']) ?>/<?php echo $game['max_players'] ?>)
                        </span>
                    </td>
                    <td><?php echo ($game['swf'] == 'Yes' ? "When Full" : $game['start']) ?></td>
                    <td>
                        <?php 
                            echo implode(
                                array_map(
                                    function($mod) { return "<a href='/player/$mod'>$mod</a>"; },
                                    array_values($game['mods']
                                )
                            ), ', ');
                        ?>
                    </td>
                    <td><?php echo $game['day_length'] ?></td>
                    <td><?php echo $game['night_length'] ?></td>
                </tr>
            <?php } ?>
        </table>

        <table width='100%' class='games-index--section forum_table' cellpadding='2'>
            <tr>
                <th>In Signup (Standard)</th>
                <th>Moderator</th>
                <th>Start Date</th>
                <th>Dusk</th>
                <th>Dawn</th>
            </tr>
            <?php foreach ( $games_in_standard_signup_as_date as $game ) { ?>
                <tr>
                    <td>
                        <img src='/images/<?php echo $game['complex'] ?>_small.png' alt='<?php echo $game['complex'] ?>' />
                        <?php if ($game['notifications']['signed_up']) { ?>
                            <img src='/images/calendar.png' />
                        <?php } ?>
                        <?php if ($game['notifications']['new_chat']) { ?>
                            <a href='/game/<?php echo $game['thread_id'] ?>/chat'><img src='/images/new_message.png' border='0'/></a>
                        <?php } ?>
                        <a href='/game/<?php echo $game['thread_id'] ?>'><?php echo $game['title'] ?></a>
                        <span>
                            (<?php echo ($game['num_players'] == $game['max_players'] ? "Full" : $game['num_players']) ?>/<?php echo $game['max_players'] ?>)
                        </span>
                    </td>
                    <td><?php echo $game['start'] ?></td>
                    <td>
                        <?php 
                            echo implode(
                                array_map(
                                    function($mod) { return "<a href='/player/$mod'>$mod</a>"; },
                                    array_values($game['mods']
                                )
                            ), ', ');
                        ?>
                    </td>
                    <td><?php echo $game['lynch_time'] ?></td>
                    <td><?php echo $game['na_deadline'] ?></td>
                </tr>
            <?php } ?>
        </table>

        <table width='100%' class='games-index--section forum_table' cellpadding='2'>
            <tr>
                <th>In Signup (Standard - Starts When Full)</th>
                <th>Moderator</th>
                <th>Needs</th>
                <th>Dusk</th>
                <th>Dawn</th>
            </tr>
            <?php foreach ( $games_in_standard_signup_as_swf as $game ) { ?>
                <tr>
                    <td>
                        <img src='/images/<?php echo $game['complex'] ?>_small.png' alt='<?php echo $game['complex'] ?>' />
                        <?php if ($game['notifications']['signed_up']) { ?>
                            <img src='/images/calendar.png' />
                        <?php } ?>
                        <?php if ($game['notifications']['new_chat']) { ?>
                            <a href='/game/<?php echo $game['thread_id'] ?>/chat'><img src='/images/new_message.png' border='0'/></a>
                        <?php } ?>
                        <a href='/game/<?php echo $game['thread_id'] ?>'><?php echo $game['title'] ?></a>
                        <span>
                            (<?php echo ($game['num_players'] == $game['max_players'] ? "Full" : $game['num_players']) ?>/<?php echo $game['max_players'] ?>)
                        </span>
                    </td>
                    <td>
                        <?php 
                            echo implode(
                                array_map(
                                    function($mod) { return "<a href='/player/$mod'>$mod</a>"; },
                                    array_values($game['mods']
                                )
                            ), ', ');
                        ?>
                    </td>
                    <td><?php echo $game['players_needed'] ?></td>
                    <td><?php echo $game['lynch_time'] ?></td>
                    <td><?php echo $game['na_deadline'] ?></td>
                </tr>
            <?php } ?>
        </table>

        <span><b>Complexity Ratings: <img src='images/Newbie_large.png'><img src='images/Low_large.png'><img src='images/Medium_large.png'><img src='images/High_large.png'><img src='images/Extreme_large.png'></b></span>
        <br />
        <a href='create_a_game.php'>Add a Game in Signup</a>
        <br />
        <a href='automod/new.php'>Add an Auto-Mod Game</a>

    </div>
</div>
