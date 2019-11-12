<h1><?php echo $games_list['title'] ?></h1>

<table class="games-list forum_table">
    <tr>
        <th><?php echo $games_list['title'] ?> (<?php echo count($games_list['games']) ?>)</th>
        <th>Winner</th>
    </tr>
    <?php foreach ( $games_list['games'] as $game ) { ?>
        <tr>
            <td><?php echo $game['info'] ?></td>
            <td><?php echo $game['winner'] ?></td>
        </tr>
    <?php } ?>
</table>