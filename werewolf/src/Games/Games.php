<?php
include_once 'php/common.php';

class Games 
{
    // -------------------------------------------------------------------------
    // Setup
    // -------------------------------------------------------------------------

    const STATUSES = ['all', 'evil', 'good', 'other', 'in_progress'];

    private $cache;
    private $games_notifications;

    public function __construct() {
        $this->cache = init_cache();
    }

    // -------------------------------------------------------------------------
    // Public functions
    // -------------------------------------------------------------------------

    public function get_stats() {
        if($counts = $this->cache->get('game-counts', 'front')) {
            return $counts;
        }

        $sql = "select sum(case when status = 'finished' and number !=0 then 1 else 0 end) finished_games, sum(case when status = 'finished' and winner='good' then 1 else 0 end) good_wins,sum(case when status = 'finished' and winner='evil' then 1 else 0 end) evil_wins,sum(case when status = 'finished' and winner='other' then 1 else 0 end) other_wins,sum(case when status = 'in progress' and number != 0 then 1 else 0 end) inprogress_games from Games;";
        $result = mysql_query($sql);

        $games_completed = mysql_result($result,0,0);
        $games_won_by_evil = mysql_result($result,0,1);
        $games_won_by_good = mysql_result($result,0,2);
        $games_won_by_other = mysql_result($result,0,3);
        $games_in_progress = mysql_result($result,0,4);
        $games_all = $games_completed + $games_in_progress;

        $counts = [
            'all' => $games_all,
            'evil' => $games_won_by_evil,
            'good' => $games_won_by_good,
            'other' => $games_won_by_other,
            'in_progress' => $games_in_progress,
        ];

        $this->cache->save($counts, 'game-counts', 'front');

        return $counts;
    }

    public function get_games_in_fast_progress() {
        $sql = "Select id, number, title, TIME_FORMAT(day_length, '%H:%i') day_length, TIME_FORMAT(night_length, '%H:%i') night_length, thread_id from Games where status='In Progress' and deadline_speed='Fast' and number is not null order by start_date, number";
        $result = mysql_query($sql);

        return $this->process_game_data($result);
    }

    public function get_games_in_standard_progress() {
        $sql = "Select id, number, title, TIME_FORMAT(lynch_time, '%l:%i %p') lynch_time, TIME_FORMAT(na_deadline, '%l:%i %p') na_deadline, thread_id from Games where status='In Progress' and deadline_speed='Standard' and number is not null order by start_date, number";
        $result = mysql_query($sql);
    
        return $this->process_game_data($result);
    }

    public function get_games_recently_ended() {
        $sql = "Select id, title, number, thread_id from Games where status = 'Finished' and number is not null order by end_date desc Limit 0, 10";
        $result = mysql_query($sql);

        return $this->process_game_data($result);
    }

    // TODO: Eliminate N+1 sql query inside this function
    // See the get_game function called from common
    public function filter_games_by_type($type) {
        $sql = "SELECT Games.id, winner FROM Games WHERE status='Finished' ";

        if($type == 'all') {
            $sql .= " OR status='In Progress'"; 
            $title = "All Games";
        } else if($type == 'evil') {
            $sql .= " AND winner = 'evil'"; 
            $title = "All Games Won by Evil";
        } else if($type == 'good') {
            $sql .= " AND winner = 'good'"; 
            $title = "All Games Won by Good";
        } else if($type == 'other') {
            $sql .= " AND winner = 'other'"; 
            $title = "All Other Type Games";
        } else if ($type == 'in_progress') {
            $sql = "SELECT Games.id, winner FROM Games WHERE status='In Progress'";
            $title = "Games In Progress";
        }
        $sql .= " ORDER BY Games.number";

        $result = mysql_query($sql);
        $games = [];
        while ( $game_data = mysql_fetch_array($result) ) {
            $games[] = [
                'info' => get_game($game_data['id'],"num, complex, title, mod"),
                'winner' => $game_data['winner']
            ];
        }

        return [
            'title' => $title,
            'games' => $games
        ];
    }

    public function filter_games_by_missing_info() {
        $sql = "SELECT u.name as mod_name, CONCAT(g.number, ') ', g.title) as title, g.thread_id FROM Games g, Players p, Moderators m, Users u where p.game_id = g.id and (p.role_id = 1 or p.side is null) and m.game_id = g.id and m.user_id = u.id and g.status = 'Finished' and g.winner != 'Other' group by g.number ORDER BY number;";
        $title = "Finished Games With Missing Role Info";

        $result = mysql_query($sql);
        $games = [];
        while ( $game_data = mysql_fetch_array($result) ) {
            $games[] = [
                'info' => "<a href='/games/".$game_data['thread_id']."'>".$game_data['title']."</a>",
                'moderator' => $game_data['mod_name']
            ];
        }

        return [
            'title' => $title,
            'games' => $games
        ];
    }



    public function games_in_fast_signup() {
        $sql = "Select Games.id, Games.thread_id, Games.complex, Games.title, DATE_FORMAT(start_date, '%b-%d-%y %l:%i %p') as start, swf, TIME_FORMAT(day_length, '%H:%i') day_length, TIME_FORMAT(night_length, '%H:%i') night_length, GROUP_CONCAT(Users.name SEPARATOR ',') mods, (select count(*)from Players where Players.game_id = Games.id) num_players, Games.max_players from Games join Moderators on Games.id = Moderators.game_id join Users on Moderators.user_id = Users.id where status='Sign-up' and deadline_speed='Fast' and ( (swf='No' and (datediff(start_date, now()) <=500) and (datediff(now(), start_date) <=3)) or swf='Yes' or automod_id is not null ) group by Games.id order by swf, start_date asc";
        $result = mysql_query($sql);

        return $this->process_game_data($result);
    }

    public function games_in_standard_signup_as_date() {
        $sql = "SELECT Games.id, Games.thread_id, Games.complex, Games.title, DATE_FORMAT(start_date, '%b-%d-%y') as start, swf, TIME_FORMAT(lynch_time, '%l:%i %p') lynch_time, TIME_FORMAT(na_deadline, '%l:%i %p') na_deadline, GROUP_CONCAT(Users.name SEPARATOR ',') mods, (select count(*)from Players where Players.game_id = Games.id) num_players, Games.max_players from Games join Moderators on Games.id = Moderators.game_id join Users on Moderators.user_id = Users.id where status='Sign-up' and deadline_speed='Standard' and ( ((datediff(start_date, now()) <=500) and (datediff(now(), start_date) <=3) and swf='No') or automod_id is not null ) and swf = 'No' group by Games.id order by start_date asc";
        $result = mysql_query($sql);

        return $this->process_game_data($result);
    }

    public function games_in_standard_signup_as_swf() {
        $sql = "Select id,  Games.thread_id, title, complex, (max_players - count(Players.user_id)) as players_needed,  (max_players - count(Players.user_id))=0 as players_needed_bin,  cast(format(((count(Players.user_id)/max_players)*100),0) as unsigned) as percent,  TIME_FORMAT(lynch_time, '%l:%i %p') lynch_time,  TIME_FORMAT(na_deadline, '%l:%i %p') na_deadline, (select GROUP_CONCAT(Users.name SEPARATOR ',') mods from Moderators join Users on Moderators.user_id = Users.id where Moderators.game_id = Games.id) mods, (select count(*)from Players where Players.game_id = Games.id) num_players, max_players from Games  LEFT JOIN Players on Games.id=Players.game_id  where `status`='Sign-up' and deadline_speed='Standard' and swf='Yes'  group by Games.id  order by players_needed_bin asc, players_needed asc, percent desc";
        $result = mysql_query($sql);

        return $this->process_game_data($result);
    }

    // -------------------------------------------------------------------------
    // Protected functions
    // -------------------------------------------------------------------------

    private function process_game_data($games_data) {
        $games = [];
        $games_notifications = $this->get_game_notifications();

        while ( $game_data = mysql_fetch_array($games_data) ) {
            $games[] = [
                'id' => $game_data['id'],
                'number' => $game_data['number'],
                'thread_id' => $game_data['thread_id'],
                'title' => $game_data['title'],
                'lynch_time' => $game_data['lynch_time'],
                'na_deadline' => $game_data['na_deadline'],
                'day_length' => $game_data['day_length'],
                'night_length' => $game_data['night_length'],
                'start' => $game_data['start'],
                'swf' => $game_data['swf'],
                'notifications' => [
                    'needs_replacement' => in_array($game_data['id'], $games_notifications['needs_replacement']),
                    'signed_up' => in_array($game_data['id'], $games_notifications['signed_up']),
                    'new_chat' => in_array($game_data['id'], $games_notifications['new_chat']),
                ],
                'players_needed' => $game_data['players_needed'],
                'complex' => $game_data['complex'],
                'num_players' => $game_data['num_players'],
                'max_players' => $game_data['max_players'],
                'mods' => explode(',', $game_data['mods']),
            ];
        }

        return $games;
    }

    private function get_game_notifications() {
        if (isset($this->games_notifications)) {
            return $this->games_notifications;
        }
        
        $this->games_notifications = [
            'signed_up' => [],
            'new_chat' => [],
            'needs_replacement' => [],
        ];

        if (isset($_SESSION['uid'])) {
            #Get list of games this user is in
            $sql = sprintf("select game_id from Users_game_all join Games on Users_game_all.game_id = Games.id where user_id=%s and Games.status != 'Finished' union (select game_id from Users_game_all join Games on Users_game_all.game_id = Games.id where user_id=%s and Games.status = 'Finished' and number is not null order by Games.end_date desc limit 10)", $_SESSION['uid'], $_SESSION['uid']);
            $result = mysql_query($sql);
            while ($game = mysql_fetch_array($result)) {
                $this->games_notifications['signed_up'][] = $game['game_id'];
            }

            #Get list of games this user has new chat messages
            $sql = sprintf("select Games.id from Games, Chat_users, Chat_rooms, Chat_messages where Chat_users.room_id=Chat_rooms.id and Chat_rooms.id=Chat_messages.room_id and Chat_messages.post_time >= Chat_users.last_view and Chat_messages.post_time > Chat_users.open and Chat_messages.post_time < if(Chat_users.close is null, now(), Chat_users.close) and Games.id=Chat_rooms.game_id and Games.status ='In Progress' and Chat_users.user_id=%s",quote_smart($_SESSION['uid']));
            $result = mysql_query($sql);
            while ($game = mysql_fetch_array($result)) {
                $this->games_notifications['new_chat'][] = $game['id'];
            }

            #Get list of games that need a replacement
            $sql = "select distinct game_id from Games join Players on Players.game_id = Games.id where need_replace is not null and Games.status != 'Finished'";
            $result = mysql_query($sql);
            while ($game = mysql_fetch_array($result)) {
                $this->games_notifications['needs_replacement'][] = $game['game_id'];
            }
        }
        
        return $this->games_notifications;
    }

}
?>