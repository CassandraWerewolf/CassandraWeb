-- phpMyAdmin SQL Dump
-- version 4.0.10.20
-- https://www.phpmyadmin.net
--
-- Host: cassandra-db.cmkhy6b9hsrs.us-east-1.rds.amazonaws.com
-- Generation Time: Jul 11, 2019 at 01:56 PM
-- Server version: 5.6.40-log
-- PHP Version: 5.6.40

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `werewolf`
--

DELIMITER $$
--
-- Functions
--
CREATE DEFINER=`dbuser`@`%` FUNCTION `Get_name`(gid int(11), uid int(6)) RETURNS char(50) CHARSET latin1
    DETERMINISTIC
BEGIN
 DECLARE ret char(50);
 IF ((select vote_by_alias from Games where id=gid) = 'Yes') THEN
   select player_alias into ret from Players_r p where p.user_id=uid and p.game_id=gid;
 END IF;
 IF (ret is null) THEN
   select name into ret from Users u where u.id=uid;
 END IF; 
 return ret;
END$$

CREATE DEFINER=`dbuser`@`%` FUNCTION `Get_name_phys`(gid int(11), uid int(6)) RETURNS char(50) CHARSET latin1
    DETERMINISTIC
BEGIN
 DECLARE ret char(50);
 IF ((select phys_by_alias from Games where id=gid) = 'Yes') THEN
   select player_alias into ret from Players_r p where p.user_id=uid and p.game_id=gid;
 END IF;
 IF (ret is null) THEN
   select name into ret from Users u where u.id=uid;
 END IF; 
 return ret;
END$$

CREATE DEFINER=`dbuser`@`%` FUNCTION `get_non_voters`(game int, game_day int) RETURNS text CHARSET latin1
    READS SQL DATA
begin
declare nonvoters text;

select group_concat(Get_name(game,p.user_id) order by Get_name(game,p.user_id) separator ', ') into nonvoters from Players_r p where p.game_id = game and (p.death_day is null or p.death_day >= game_day) and p.user_id not in (select voter from Tally t where t.game_id = p.game_id and t.unvote=0 and t.day = game_day);
return nonvoters;
end$$

CREATE DEFINER=`dbuser`@`%` FUNCTION `get_non_voters_count`(game int, game_day int) RETURNS int(11)
    READS SQL DATA
begin
declare nonvoters integer;
select count(*) into nonvoters from Players_r p, Users u where p.game_id = game and (p.death_day is null or p.death_day >= game_day) and p.user_id = u.id and p.user_id not in (select voter from Tally t where t.game_id = p.game_id and t.unvote=0 and t.day = game_day);
return nonvoters;
end$$

CREATE DEFINER=`dbuser`@`%` FUNCTION `Get_tally`(gid int(11), nday int(3), tallytype char(5), tallysource char(4)) RETURNS text CHARSET latin1
    DETERMINISTIC
BEGIN
 DECLARE ret text;
 DECLARE strikeBegin char(8);
 DECLARE strikeEnd char(9);
 
 drop temporary table if exists T_Tally;              
 CREATE TEMPORARY TABLE T_Tally
   SELECT * from Tally where game_id=gid and day = nday;
   
 IF tallysource = 'bgg'
 THEN
   SET strikeBegin = '[-]';
   SET strikeEnd = '[/-]';
 ELSE
   SET strikeBegin = '<strike>';
   SET strikeEnd = '</strike>';  
 END IF;
 
 IF tallytype = 'lhv' THEN  
     SELECT CAST( GROUP_CONCAT( '[b]', votee,  '[/b] - ', total,  ' - ', vote_str,  '\n' SEPARATOR  '' ) AS CHAR ) into ret
       FROM (
         SELECT
           1 AS dummy,
           IF(votee, Get_name(game_id, votee), misc) AS votee,
           SUM( IF( unvote, 0, 1 ) ) AS total,        
           GROUP_CONCAT( IF( unvote,
             CONCAT(strikeBegin, CONCAT(Get_name(game_id, voter), '(',vote_count,')'), strikeEnd),
             CONCAT(Get_name(game_id, voter), IF(nightfall,'*',''), '(',vote_count, ')') )
             ORDER BY vote_count
             SEPARATOR  ', ') AS vote_str
         FROM T_Tally
         GROUP BY votee
         ORDER BY total DESC , MIN( IF( unvote, NULL , vote_count ) ) ASC
       ) AS tab
       GROUP BY dummy;  
 ELSEIF tallytype = 'lhlv' THEN  
     SELECT CAST( GROUP_CONCAT( '[b]', votee,  '[/b] - ', total,  ' - ', vote_str,  '\n' SEPARATOR  '' ) AS CHAR ) into ret
       FROM (
         SELECT
           1 AS dummy,
           IF(votee, Get_name(game_id, votee), misc) AS votee,
           SUM( IF( unvote, 0, 1 ) ) AS total,        
           GROUP_CONCAT( IF( unvote,
             CONCAT(strikeBegin, CONCAT(Get_name(game_id, voter), '(',vote_count,')'), strikeEnd),
             CONCAT(Get_name(game_id, voter), IF(nightfall,'*',''), '(',vote_count, ')') )
             ORDER BY vote_count
             SEPARATOR  ', ') AS vote_str
         FROM T_Tally
         GROUP BY votee
         ORDER BY total DESC , MAX( IF( unvote, NULL , vote_count ) ) ASC
       ) AS tab
       GROUP BY dummy;  
 ELSEIF tallytype = 'inv' THEN
     SELECT CAST( GROUP_CONCAT( '[b]', voter,  '[/b] - ', total,  ' - ', vote_str,  '\n' SEPARATOR  '' ) AS CHAR ) into ret
       FROM (
         SELECT
           1 AS dummy,
           Get_name(game_id, voter) AS voter,
           Count(1) AS total,        
           GROUP_CONCAT( IF( unvote,
             CONCAT(strikeBegin, CONCAT(IF(votee, Get_name(game_id, votee), misc), '(',vote_count,')'), strikeEnd),
             CONCAT(IF(votee, Get_name(game_id, votee), misc), IF(nightfall,'*',''), '(',vote_count, ')') )
             ORDER BY vote_count
             SEPARATOR  ', ') AS vote_str
         FROM T_Tally
         GROUP BY voter
         ORDER BY voter ASC
       ) AS tab
       GROUP BY dummy;        
 END IF;
   
 return ret;
END$$

CREATE DEFINER=`dbuser`@`%` FUNCTION `LEVENSHTEIN`(s1 VARCHAR(255), s2 VARCHAR(255)) RETURNS int(11)
    DETERMINISTIC
BEGIN
        DECLARE s1_len, s2_len, i, j, c, c_temp, cost INT;
        DECLARE s1_char CHAR;
        DECLARE cv0, cv1 VARBINARY(256);
        SET s1_len = CHAR_LENGTH(s1), s2_len = CHAR_LENGTH(s2), cv1 = 0x00, j = 1, i = 1, c = 0;
        IF s1 = s2 THEN
          RETURN 0;
        ELSEIF s1_len = 0 THEN
          RETURN s2_len;
        ELSEIF s2_len = 0 THEN
          RETURN s1_len;
        ELSE
          WHILE j <= s2_len DO
            SET cv1 = CONCAT(cv1, UNHEX(HEX(j))), j = j + 1;
          END WHILE;
          WHILE i <= s1_len DO
            SET s1_char = SUBSTRING(s1, i, 1), c = i, cv0 = UNHEX(HEX(i)), j = 1;
            WHILE j <= s2_len DO
                SET c = c + 1;
                IF s1_char = SUBSTRING(s2, j, 1) THEN SET cost = 0; ELSE SET cost = 1; END IF;
                SET c_temp = CONV(HEX(SUBSTRING(cv1, j, 1)), 16, 10) + cost;
                IF c > c_temp THEN SET c = c_temp; END IF;
                SET c_temp = CONV(HEX(SUBSTRING(cv1, j+1, 1)), 16, 10) + 1;
                IF c > c_temp THEN SET c = c_temp; END IF;
                SET cv0 = CONCAT(cv0, UNHEX(HEX(c))), j = j + 1;
            END WHILE;
            SET cv1 = cv0, i = i + 1;
          END WHILE;
        END IF;
        RETURN c;
      END$$

CREATE DEFINER=`dbuser`@`%` FUNCTION `split`(input TEXT,  delimiter VARCHAR(10), trim_type int, col int) RETURNS varchar(255) CHARSET latin1
    DETERMINISTIC
BEGIN
  
  
  
  
  
  DECLARE cur_position INT DEFAULT 1 ;
  DECLARE remainder TEXT;
  DECLARE cur_string VARCHAR(1000);
  DECLARE result_string VARCHAR(1000);
  DECLARE delimiter_length TINYINT UNSIGNED;
  DECLARE loop_count TINYINT UNSIGNED;
  SET loop_count=0;
  SET remainder = input;
  SET delimiter_length = CHAR_LENGTH(delimiter);
  WHILE CHAR_LENGTH(remainder) > 0 AND cur_position > 0 AND loop_count<col DO
    SET cur_position = INSTR(remainder, delimiter);
    IF cur_position = 0 THEN
      SET cur_string = remainder;
    ELSE
      SET cur_string = LEFT(remainder, cur_position - 1);
    END IF;
    SET result_string=cur_string;
    SET remainder = SUBSTRING(remainder, cur_position + delimiter_length);
    SET loop_count=loop_count+1;
  END WHILE;
  CASE trim_type
    WHEN 1 THEN RETURN TRIM(BOTH ' ' FROM result_string);
    WHEN 2 THEN RETURN TRIM(LEADING ' ' FROM result_string);
    WHEN 3 THEN RETURN TRIM(TRAILING ' ' FROM result_string);
    ELSE RETURN result_string;
  END CASE;
  RETURN result_string;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `AM_roles`
--

CREATE TABLE IF NOT EXISTS `AM_roles` (
  `id` int(3) unsigned NOT NULL AUTO_INCREMENT,
  `template_id` int(2) NOT NULL,
  `role_id` int(2) unsigned NOT NULL,
  `side` enum('Good','Evil','Other') NOT NULL,
  `game_action` enum('none','alive','dead','all') NOT NULL DEFAULT 'none',
  `action_desc` text,
  `group_name` varchar(100) DEFAULT NULL,
  `n0_knows` varchar(50) NOT NULL DEFAULT 'none',
  `n0_view` varchar(100) NOT NULL DEFAULT 'none',
  `view_result` text,
  `reveal_as` varchar(50) NOT NULL DEFAULT 'role',
  `attribute` enum('','Brutal','Tough','Tinker','White Hat') NOT NULL,
  `a_hidden` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=false, 1=true',
  `parity` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=false,1=true',
  `promotion` varchar(50) NOT NULL DEFAULT 'none',
  `promotion_parity` enum('yes','no') NOT NULL DEFAULT 'no' COMMENT 'Yes means keeps current parity status after promotion, does not gain promoted roles parity status.',
  `require_role` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2283 ;

-- --------------------------------------------------------

--
-- Table structure for table `AM_template`
--

CREATE TABLE IF NOT EXISTS `AM_template` (
  `id` int(2) unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` smallint(4) unsigned NOT NULL,
  `name` varchar(80) NOT NULL,
  `description` text NOT NULL,
  `num_players` int(2) NOT NULL,
  `num_player_sets` varchar(50) NOT NULL DEFAULT '0',
  `role_reveal` enum('yes','no') NOT NULL DEFAULT 'yes' COMMENT 'If changed edit "expand_role_reveal" in edit_functions.php',
  `random_n0` enum('yes','no') NOT NULL DEFAULT 'yes',
  `priest_type` enum('none','choose','lynch','all','passive') NOT NULL DEFAULT 'none' COMMENT 'if changed edit expand_priest_type in edit_functions.php',
  `random_tinker` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=false, 1=true',
  `random_whitehat` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=false, 1=true',
  `mode` enum('Edit','Test','Active') NOT NULL DEFAULT 'Edit',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=244 ;

-- --------------------------------------------------------

--
-- Table structure for table `Auto_dusk`
--

CREATE TABLE IF NOT EXISTS `Auto_dusk` (
  `game_id` smallint(4) unsigned NOT NULL,
  `mon` tinyint(1) NOT NULL DEFAULT '0',
  `tue` tinyint(1) NOT NULL DEFAULT '0',
  `wed` tinyint(1) NOT NULL DEFAULT '0',
  `thu` tinyint(1) NOT NULL DEFAULT '0',
  `fri` tinyint(1) NOT NULL DEFAULT '0',
  `sat` tinyint(1) NOT NULL DEFAULT '0',
  `sun` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`game_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Bio`
--

CREATE TABLE IF NOT EXISTS `Bio` (
  `user_id` int(3) unsigned NOT NULL,
  `avatar` varchar(60) DEFAULT NULL COMMENT 'BGG Avatar',
  `rl_name` varchar(50) DEFAULT NULL COMMENT 'Real Life Name',
  `email_addr` varchar(50) DEFAULT NULL COMMENT 'E-mail Address',
  `twitter_name` varchar(50) DEFAULT NULL COMMENT 'Twitter Name',
  `name_origin` text COMMENT 'Origin of Username',
  `b_date` date DEFAULT NULL COMMENT 'Birthday',
  `gender` enum('M','F') DEFAULT NULL COMMENT 'Gender',
  `location` varchar(100) DEFAULT NULL COMMENT 'Location',
  `time_zone` varchar(1) DEFAULT NULL COMMENT 'Time Zone',
  `mbti` text COMMENT '<a href=''http://en.wikipedia.org/wiki/MBTI''>MBTI</a>',
  `free_hours` text COMMENT 'Typical Hours to Play WW',
  `job` text COMMENT 'Job',
  `family` text COMMENT 'Family',
  `religion` text COMMENT 'Religion',
  `max_messages` int(11) NOT NULL DEFAULT '50' COMMENT 'Max New Chat Posts (0=All)',
  `comments` text COMMENT 'Anything Else',
  `chat_color` varchar(7) DEFAULT '#000000' COMMENT 'Chat Color Preference',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `CC_info`
--

CREATE TABLE IF NOT EXISTS `CC_info` (
  `game_id` smallint(4) unsigned NOT NULL,
  `user_id` smallint(3) unsigned NOT NULL,
  `claim_time` datetime NOT NULL,
  `challenger_id` smallint(4) DEFAULT NULL,
  `type_error` enum('game','player') DEFAULT NULL,
  `desc_error` text,
  PRIMARY KEY (`game_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `CC_players`
--

CREATE TABLE IF NOT EXISTS `CC_players` (
  `user_id` smallint(4) unsigned NOT NULL,
  `team` varchar(50) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Chat_messages`
--

CREATE TABLE IF NOT EXISTS `Chat_messages` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `room_id` int(5) NOT NULL,
  `user_id` int(3) NOT NULL,
  `message` text NOT NULL,
  `post_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `Chat_messages_room_id_idx` (`room_id`,`post_time`),
  KEY `Chat_messages_user_id_idx` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1650495 ;

-- --------------------------------------------------------

--
-- Table structure for table `Chat_message_actions`
--

CREATE TABLE IF NOT EXISTS `Chat_message_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `target_id` int(11) NOT NULL,
  `misc` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Chat_rooms`
--

CREATE TABLE IF NOT EXISTS `Chat_rooms` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `game_id` int(4) unsigned NOT NULL,
  `name` varchar(60) NOT NULL,
  `lock` enum('Off','On','Secure') NOT NULL DEFAULT 'Off',
  `max_post` int(11) DEFAULT NULL,
  `remaining_post` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  `monitor` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `Chat_room_game_id_idx` (`game_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=57320 ;

--
-- Triggers `Chat_rooms`
--
DROP TRIGGER IF EXISTS `Chat_rooms_bd`;
DELIMITER //
CREATE TRIGGER `Chat_rooms_bd` BEFORE DELETE ON `Chat_rooms`
 FOR EACH ROW begin
       delete from Chat_users where room_id = old.id;
       delete from Chat_messages where room_id = old.id;
end
//
DELIMITER ;
DROP TRIGGER IF EXISTS `Chat_rooms_bu`;
DELIMITER //
CREATE TRIGGER `Chat_rooms_bu` BEFORE UPDATE ON `Chat_rooms`
 FOR EACH ROW begin
if ( !(new.max_post <=> old.max_post) ) then
	if((old.max_post is NULL) or (new.max_post is NULL)) then
		set new.remaining_post = new.max_post;
	else
		set new.remaining_post = new.max_post - old.max_post +	old.remaining_post;
	end if;
end if;	
if ( new.`lock` <> 'Secure' and !(old.remaining_post <=> new.remaining_post) ) then
    if(new.remaining_post <= 0) then
        set new.`lock` = 'On';
    else
        set new.`lock` = 'Off';
    end if;
end if;
end
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `Chat_users`
--

CREATE TABLE IF NOT EXISTS `Chat_users` (
  `room_id` int(5) unsigned NOT NULL,
  `user_id` int(3) unsigned NOT NULL,
  `alias` varchar(50) DEFAULT NULL,
  `secret` enum('on','') DEFAULT NULL,
  `last_view` datetime NOT NULL,
  `color` varchar(7) NOT NULL DEFAULT '#000000',
  `lock` enum('Off','On','Secure') NOT NULL DEFAULT 'Off',
  `max_post` int(11) DEFAULT NULL,
  `remaining_post` int(11) DEFAULT NULL,
  `open` datetime NOT NULL,
  `close` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`,`room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Triggers `Chat_users`
--
DROP TRIGGER IF EXISTS `Chat_users_bi`;
DELIMITER //
CREATE TRIGGER `Chat_users_bi` BEFORE INSERT ON `Chat_users`
 FOR EACH ROW begin
if (new.user_id = 0) then
	set new.color = '#0000CC';
end if;
end
//
DELIMITER ;
DROP TRIGGER IF EXISTS `Chat_users_bu`;
DELIMITER //
CREATE TRIGGER `Chat_users_bu` BEFORE UPDATE ON `Chat_users`
 FOR EACH ROW begin
if ( !(new.max_post <=> old.max_post) ) then
	if((old.max_post is NULL) or (new.max_post is NULL)) then
		set new.remaining_post = new.max_post;
	else
		set new.remaining_post = new.max_post - old.max_post +	old.remaining_post;
	end if;
end if;	
if ( new.`lock` <> 'Secure' and !(old.remaining_post <=> new.remaining_post) ) then
    if(new.remaining_post <= 0) then
        set new.`lock` = 'On';
    else
        set new.`lock` = 'Off';
    end if;
end if;
if (new.user_id = 0) then
	set new.color = '#0000CC';
end if;
end
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `Exits`
--

CREATE TABLE IF NOT EXISTS `Exits` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `game_id` int(4) unsigned NOT NULL,
  `name` varchar(60) NOT NULL,
  `travel_text` text,
  `template_id` int(5) unsigned DEFAULT NULL,
  `comment` text,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4379 ;

-- --------------------------------------------------------

--
-- Table structure for table `Games`
--

CREATE TABLE IF NOT EXISTS `Games` (
  `id` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `number` int(4) DEFAULT NULL,
  `start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` datetime DEFAULT NULL,
  `title` varchar(200) NOT NULL DEFAULT '',
  `status` enum('Finished','In Progress','Sign-up','Scheduled','Sub-Thread','Unknown') NOT NULL DEFAULT 'In Progress',
  `winner` enum('','Evil','Good','Other') NOT NULL DEFAULT '',
  `thread_id` int(7) DEFAULT NULL,
  `parent_game_id` int(4) DEFAULT NULL,
  `description` text,
  `swf` enum('Yes','No') NOT NULL DEFAULT 'No' COMMENT 'Used to specify that the game starts when full',
  `aprox_length` int(11) DEFAULT NULL,
  `max_players` int(11) DEFAULT NULL,
  `player_list_id` int(11) DEFAULT NULL,
  `complex` enum('','Newbie','Low','Medium','High','Extreme','Kima') NOT NULL,
  `phase` enum('day','night') NOT NULL DEFAULT 'night',
  `day` int(11) NOT NULL DEFAULT '0',
  `game_order` enum('off','on') NOT NULL DEFAULT 'off',
  `auto_vt` enum('No','lhv','lhlv') NOT NULL DEFAULT 'No',
  `allow_nightfall` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `allow_nolynch` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `vote_by_alias` enum('No','Yes') NOT NULL DEFAULT 'No',
  `phys_by_alias` enum('No','Yes') NOT NULL DEFAULT 'No',
  `alias_display` enum('None','Private','Public') NOT NULL DEFAULT 'None',
  `updated_tally` tinyint(1) NOT NULL DEFAULT '0',
  `dawn_chat_reset` enum('No','Yes') NOT NULL DEFAULT 'No',
  `missing_hr` int(3) DEFAULT NULL COMMENT 'moderator will be notified if a player hasn''t posted in x amount of hours',
  `deadline_speed` enum('Standard','Fast') NOT NULL DEFAULT 'Standard' COMMENT 'If Standard then lynch_time and na_deadline should be set, if Fast then day_lenght and night_length should be set',
  `lynch_time` time DEFAULT NULL,
  `na_deadline` time DEFAULT NULL,
  `day_length` time DEFAULT NULL,
  `night_length` time DEFAULT NULL,
  `auto_deadline` time DEFAULT NULL COMMENT 'if this is set dawn and dusk will be automatically posted by cassy.  This time is the mimimum length that day should last on day1 and if there is a delay posting a deadline.',
  `automod_id` int(11) DEFAULT NULL,
  `automod_state` varchar(50) DEFAULT NULL,
  `automod_phase_change` datetime NOT NULL,
  `automod_nextdeadline` datetime DEFAULT NULL COMMENT 'used by any game with auto dawn/dusk selected to indicate when the next one should be posted.',
  `automod_weekend` tinyint(1) DEFAULT NULL,
  `automod_running` datetime DEFAULT NULL,
  `automod_timestamp` datetime DEFAULT NULL,
  `calendar_id` text,
  `expired` enum('0','1') NOT NULL DEFAULT '0',
  `phys_move_limit` tinyint(3) unsigned DEFAULT NULL,
  `phys_item_limit` tinyint(3) unsigned DEFAULT NULL,
  `phys_reset_moves` enum('none','dawn','dusk') NOT NULL DEFAULT 'none',
  `series_id` int(5) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `thread_id` (`thread_id`),
  KEY `status_idx` (`status`),
  KEY `winner_idx` (`winner`),
  KEY `end_date_idx` (`end_date`),
  KEY `start_date_idx` (`start_date`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4554 ;

--
-- Triggers `Games`
--
DROP TRIGGER IF EXISTS `Games_ad`;
DELIMITER //
CREATE TRIGGER `Games_ad` AFTER DELETE ON `Games`
 FOR EACH ROW begin
if old.status <> 'Sub-Thread'  and old.status <> 'Unknown' then
	INSERT INTO Update_calendar(action, calendar_id) VALUES('delete', old.calendar_id);
end if;
end
//
DELIMITER ;
DROP TRIGGER IF EXISTS `Games_ai`;
DELIMITER //
CREATE TRIGGER `Games_ai` AFTER INSERT ON `Games`
 FOR EACH ROW begin
declare slot_id tinyint;
declare parent_status enum('Sign-up', 'In Progress', 'Finished', 'Sub-Thread');
if new.status = 'In Progress' Then
	set slot_id = (select id from Post_collect_slots where id = (select min(id) from Post_collect_slots where game_id is null));
	update Post_collect_slots set game_id = new.id where id=slot_id;
elseif new.status = 'Sub-Thread' then
	set parent_status = (select status from Games where id = new.parent_game_id);
	if parent_status = 'In Progress' then
		set slot_id = (select id from Post_collect_slots where id = (select min(id) from Post_collect_slots where game_id is null));
		update Post_collect_slots set game_id = new.id where id=slot_id;
	end if;
end if;
if(new.status <> 'Sub-Thread' and new.status <> 'Unknown') then
	INSERT INTO Update_calendar(action, game_id) VALUES('add', new.id);
end if;
end
//
DELIMITER ;
DROP TRIGGER IF EXISTS `Games_au`;
DELIMITER //
CREATE TRIGGER `Games_au` AFTER UPDATE ON `Games`
 FOR EACH ROW begin
declare done bool default false;
declare slot_id tinyint;
declare game int(4);
declare cur cursor for select id from Games where parent_game_id = new.id and status = 'Sub-Thread';
declare continue handler for sqlstate '02000' set done = true;
if (new.status = 'In Progress' and old.status='Sign-up') Then
	if new.start_date <> old.start_date then
		INSERT INTO Update_calendar(action, game_id, calendar_id) VALUES('update', new.id, new.calendar_id);
	end if;
	set slot_id = (select id from Post_collect_slots where id = (select min(id) from Post_collect_slots where game_id is null));
	update Post_collect_slots set game_id = new.id where id=slot_id;
	open cur;
	subthread_loop_add: loop
		fetch cur into game;
		if done then leave subthread_loop_add; end if;
		set slot_id = (select id from Post_collect_slots where id = (select min(id) from Post_collect_slots where game_id is null));
		update Post_collect_slots set game_id = game where id=slot_id;
	end loop subthread_loop_add;
	close cur;
elseif (new.status = 'Finished' and old.status = 'In Progress') then
	set slot_id = (select id from Post_collect_slots where game_id = new.id);
	update Post_collect_slots set game_id = NULL, last_dumped = NULL where id = slot_id;
	open cur;
	subthread_loop_remove: loop
		fetch cur into game;
		if done then leave subthread_loop_remove; end if;
		set slot_id = (select id from Post_collect_slots where game_id = game);
		update Post_collect_slots set game_id = NULL, last_dumped = NULL where id = slot_id;
	end loop subthread_loop_remove;
	close cur;
end if;
if (new.status = 'Finished' and old.status = 'In Progress') Then
	INSERT INTO Update_calendar(action, game_id, calendar_id) VALUES('update', new.id, new.calendar_id);
end if;
if (new.status ='Sign-up' or new.status ='Scheduled' or new.status ='Finished') and (new.title <> old.title or new.thread_id <> old.thread_id or new.start_date <> old.start_date or new.end_date <> old.end_date or new.description <> old.description) then
	INSERT INTO Update_calendar(action, game_id, calendar_id) VALUES('update', new.id, new.calendar_id);
end if;
end
//
DELIMITER ;
DROP TRIGGER IF EXISTS `Games_bd`;
DELIMITER //
CREATE TRIGGER `Games_bd` BEFORE DELETE ON `Games`
 FOR EACH ROW begin
       delete from Moderators where game_id = old.id;
       delete from Players where game_id = old.id;
       delete from Replacements where game_id = old.id;
       update Post_collect_slots set game_id = NULL, last_dumped = NULL where game_id = old.id;
       delete from Posts where game_id = old.id;
       delete from Votes where game_id = old.id;
       delete from Tally where game_id = old.id;
       delete from Chat_rooms where game_id = old.id;
end
//
DELIMITER ;
DROP TRIGGER IF EXISTS `Games_bu`;
DELIMITER //
CREATE TRIGGER `Games_bu` BEFORE UPDATE ON `Games`
 FOR EACH ROW begin
declare done bool default false;
declare room int(4);
declare cur cursor for select id from Chat_rooms where game_id = new.id;
declare continue handler for sqlstate '02000' set done = true;
if(new.status = 'In Progress' and old.status = 'Sign-up') then
    if(old.number is null) then
        set new.number = (select max(number)+1 from Games);
	end if;
    set new.start_date = (select CURRENT_TIMESTAMP);
elseif(new.status = 'Finished' and old.status = 'In Progress') then
    set new.end_date = (select CURRENT_TIMESTAMP);
end if;
							        
if(new.phase = 'day' and old.phase = 'night' and new.dawn_chat_reset = 'Yes') then
    open cur;
    room_loop: loop
        fetch cur into room;
        if done then leave room_loop; end if;
        Update Chat_rooms set remaining_post = max_post, `lock`='Off' WHERE id = room and `lock` <> 'Secure';
		Update Chat_users set remaining_post = max_post, `lock`='Off' WHERE room_id = room and `lock` <> 'Secure';
	end loop room_loop;
	close cur;
end if;
if(new.phase = 'day' and old.phase = 'night') then
        Update Players set ga_lock = NULL WHERE game_id = new.id;
end if;
if (new.status <> old.status and new.status = 'Unknown') Then
    INSERT INTO Update_calendar(action, game_id, calendar_id) VALUES('delete', new.id, new.calendar_id);
    set new.calendar_id = NULL;
end if;
end
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `Game_day_log`
--

CREATE TABLE IF NOT EXISTS `Game_day_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `game_id` int(11) NOT NULL,
  `phase` enum('day','night') NOT NULL DEFAULT 'night',
  `day` int(11) NOT NULL DEFAULT '0',
  `change_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `Game_orders`
--

CREATE TABLE IF NOT EXISTS `Game_orders` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(3) NOT NULL,
  `game_id` int(4) NOT NULL,
  `desc` varchar(50) NOT NULL,
  `target_id` int(3) DEFAULT NULL,
  `user_text` varchar(255) NOT NULL,
  `cancel` smallint(1) DEFAULT NULL,
  `day` int(2) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `game_id` (`game_id`,`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=95556 ;

-- --------------------------------------------------------

--
-- Table structure for table `Game_series`
--

CREATE TABLE IF NOT EXISTS `Game_series` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `Items`
--

CREATE TABLE IF NOT EXISTS `Items` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `template_id` int(5) unsigned NOT NULL,
  `game_id` int(4) unsigned NOT NULL,
  `name` varchar(60) NOT NULL,
  `owner_ref_id` int(5) unsigned DEFAULT NULL,
  `owner_type` enum('user','loc') NOT NULL DEFAULT 'user',
  `description` text,
  `visibility` enum('obvious','hide','conceal','invis') NOT NULL DEFAULT 'conceal',
  `mobility` enum('fixed','heavy','mobile','nonphys') NOT NULL DEFAULT 'nonphys',
  `room_id` int(6) DEFAULT NULL,
  `room_alias` varchar(50) DEFAULT NULL,
  `room_color` varchar(7) DEFAULT '#000000',
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12656 ;

-- --------------------------------------------------------

--
-- Table structure for table `Item_orders`
--

CREATE TABLE IF NOT EXISTS `Item_orders` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(5) unsigned NOT NULL,
  `game_id` int(5) unsigned NOT NULL,
  `item_id` int(5) unsigned NOT NULL,
  `target_id` int(5) unsigned NOT NULL,
  `target_type` enum('user','loc') NOT NULL,
  `status` enum('active','canceled','processed') NOT NULL DEFAULT 'active',
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `game_id` (`game_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2344 ;

-- --------------------------------------------------------

--
-- Table structure for table `Item_templates`
--

CREATE TABLE IF NOT EXISTS `Item_templates` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `game_id` int(4) unsigned NOT NULL,
  `name` varchar(60) NOT NULL,
  `description` text CHARACTER SET latin1 COLLATE latin1_spanish_ci,
  `visibility` enum('obvious','hide','conceal','invis') NOT NULL DEFAULT 'conceal',
  `mobility` enum('fixed','heavy','mobile','nonphys') NOT NULL DEFAULT 'nonphys',
  `room_id` int(6) DEFAULT NULL,
  `room_alias` varchar(50) DEFAULT NULL,
  `room_color` varchar(7) DEFAULT '#000000',
  PRIMARY KEY (`id`),
  KEY `game_id` (`game_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7498 ;

-- --------------------------------------------------------

--
-- Table structure for table `Locations`
--

CREATE TABLE IF NOT EXISTS `Locations` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `game_id` int(4) unsigned NOT NULL,
  `name` varchar(60) NOT NULL,
  `description` text,
  `comment` text,
  `subgame_id` int(4) unsigned DEFAULT NULL,
  `room_id` int(5) unsigned DEFAULT NULL,
  `created` datetime NOT NULL,
  `lock` enum('Off','On','Secure') NOT NULL DEFAULT 'Off',
  `visibility` enum('None','Search','Full') NOT NULL DEFAULT 'Full',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6527 ;

-- --------------------------------------------------------

--
-- Table structure for table `Loc_exits`
--

CREATE TABLE IF NOT EXISTS `Loc_exits` (
  `loc_from_id` int(5) unsigned NOT NULL,
  `loc_to_id` int(5) unsigned NOT NULL,
  `exit_id` int(5) unsigned NOT NULL,
  PRIMARY KEY (`loc_from_id`,`exit_id`),
  KEY `exit_id` (`exit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Misc_users`
--

CREATE TABLE IF NOT EXISTS `Misc_users` (
  `user_id` int(3) unsigned NOT NULL,
  `google_calendar` text,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Moderators`
--

CREATE TABLE IF NOT EXISTS `Moderators` (
  `user_id` int(3) unsigned NOT NULL DEFAULT '000',
  `game_id` int(4) unsigned NOT NULL DEFAULT '0000',
  `comment` text,
  PRIMARY KEY (`user_id`,`game_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Triggers `Moderators`
--
DROP TRIGGER IF EXISTS `Moderators_ai`;
DELIMITER //
CREATE TRIGGER `Moderators_ai` AFTER INSERT ON `Moderators`
 FOR EACH ROW Begin	
	DECLARE l_room_id INT(5);
	DECLARE done BOOL DEFAULT FALSE;
	DECLARE cur_1 CURSOR FOR SELECT r.id from Chat_rooms r where r.game_id = new.game_id AND r.id NOT IN (SELECT cr.id FROM Chat_rooms cr, Chat_users cu WHERE cr.game_id = new.game_id AND cu.room_id = cr.id AND cu.user_id = new.user_id);
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
	open cur_1;
	my_loop: LOOP
		FETCH cur_1 INTO l_room_id;
		IF done THEN
			CLOSE cur_1;
			LEAVE my_loop;
		END IF;
		INSERT INTO Chat_users(user_id,room_id) VALUES(new.user_id,l_room_id);
	END LOOP;	
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `Moderators_active`
--
CREATE TABLE IF NOT EXISTS `Moderators_active` (
`name` varchar(50)
);
-- --------------------------------------------------------

--
-- Table structure for table `Move_orders`
--

CREATE TABLE IF NOT EXISTS `Move_orders` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(4) unsigned NOT NULL,
  `game_id` int(4) unsigned NOT NULL,
  `exit_id` int(5) unsigned NOT NULL,
  `status` enum('active','canceled','processed') NOT NULL DEFAULT 'active',
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `game_id` (`game_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10775 ;

-- --------------------------------------------------------

--
-- Table structure for table `Physics_processing`
--

CREATE TABLE IF NOT EXISTS `Physics_processing` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `game_id` int(4) unsigned NOT NULL,
  `type` enum('item','movement') NOT NULL,
  `frequency` enum('7daily','5daily','hourly','2hourly','30mins','15mins','immediate') NOT NULL,
  `minute` tinyint(2) unsigned NOT NULL,
  `hour` tinyint(2) unsigned DEFAULT NULL,
  `last_run` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=429 ;

-- --------------------------------------------------------

--
-- Table structure for table `Players`
--

CREATE TABLE IF NOT EXISTS `Players` (
  `user_id` int(3) unsigned NOT NULL DEFAULT '000',
  `game_id` int(4) unsigned NOT NULL DEFAULT '0000',
  `role_name` varchar(50) DEFAULT NULL,
  `role_id` int(2) unsigned NOT NULL DEFAULT '01',
  `side` enum('Good','Evil','Other') DEFAULT NULL,
  `game_action` enum('none','alive','dead','all') NOT NULL DEFAULT 'none',
  `ga_desc` text,
  `ga_text` enum('','on') NOT NULL DEFAULT '',
  `ga_group` varchar(50) DEFAULT NULL,
  `ga_lock` smallint(1) DEFAULT NULL,
  `mod_comment` text,
  `user_comment` text,
  `update_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `death_phase` enum('','Alive','Day','Night') DEFAULT NULL,
  `death_day` smallint(2) DEFAULT NULL,
  `need_replace` tinyint(1) DEFAULT NULL,
  `automod_role_id` smallint(4) DEFAULT NULL,
  `automod_promoted_id` smallint(4) DEFAULT NULL,
  `need_to_confirm` tinyint(4) DEFAULT NULL,
  `tough_lives` tinyint(1) NOT NULL DEFAULT '0',
  `player_alias` varchar(50) DEFAULT NULL,
  `alias_color` varchar(7) NOT NULL DEFAULT '#000000',
  `loc_id` int(5) unsigned DEFAULT NULL,
  `modchat_id` int(5) unsigned DEFAULT NULL,
  `phys_move_limit` smallint(4) unsigned DEFAULT NULL,
  `phys_item_limit` int(4) unsigned DEFAULT NULL,
  `phys_moves` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`game_id`),
  KEY `game_id_idx` (`game_id`),
  KEY `side` (`side`),
  KEY `player_alias` (`player_alias`),
  KEY `loc_id` (`loc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Triggers `Players`
--
DROP TRIGGER IF EXISTS `Players_ad`;
DELIMITER //
CREATE TRIGGER `Players_ad` AFTER DELETE ON `Players`
 FOR EACH ROW Begin	
	DELETE from Replacements WHERE game_id = old.game_id AND user_id = old.user_id;
end
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `Players_active`
--
CREATE TABLE IF NOT EXISTS `Players_active` (
`name` varchar(50)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `Players_all`
--
CREATE TABLE IF NOT EXISTS `Players_all` (
`game_id` int(11) unsigned
,`user_id` int(11) unsigned
,`type` varchar(11)
,`original_id` int(11) unsigned
,`player_alias` varchar(50)
,`alias_color` varchar(7)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `Players_month`
--
CREATE TABLE IF NOT EXISTS `Players_month` (
`month` varchar(5)
,`user_id` int(11) unsigned
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `Players_r`
--
CREATE TABLE IF NOT EXISTS `Players_r` (
`game_id` int(11) unsigned
,`user_id` int(11) unsigned
,`death_phase` varchar(5)
,`death_day` smallint(6)
,`player_alias` varchar(50)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `Players_result`
--
CREATE TABLE IF NOT EXISTS `Players_result` (
`game_id` int(4) unsigned
,`user_id` int(11) unsigned
,`original_id` int(11) unsigned
,`result` varchar(7)
);
-- --------------------------------------------------------

--
-- Table structure for table `Posts`
--

CREATE TABLE IF NOT EXISTS `Posts` (
  `article_id` int(10) NOT NULL DEFAULT '0',
  `game_id` int(4) unsigned NOT NULL DEFAULT '0000',
  `user_id` int(3) unsigned NOT NULL DEFAULT '000',
  `time_stamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `text` text NOT NULL,
  `page` int(3) NOT NULL DEFAULT '0',
  `num_edits` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`article_id`),
  KEY `group_user_idx` (`game_id`,`user_id`),
  KEY `page_idx` (`page`),
  FULLTEXT KEY `text` (`text`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Post_collect_slots`
--

CREATE TABLE IF NOT EXISTS `Post_collect_slots` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `game_id` int(10) unsigned DEFAULT NULL,
  `minute` tinyint(3) unsigned NOT NULL,
  `run_order` tinyint(4) NOT NULL,
  `last_dumped` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=41 ;

-- --------------------------------------------------------

--
-- Table structure for table `Replacements`
--

CREATE TABLE IF NOT EXISTS `Replacements` (
  `user_id` int(3) unsigned NOT NULL DEFAULT '000',
  `game_id` int(4) unsigned NOT NULL DEFAULT '0000',
  `replace_id` int(3) unsigned NOT NULL DEFAULT '000',
  `period` enum('Day','Night') NOT NULL DEFAULT 'Day',
  `number` int(11) NOT NULL DEFAULT '0',
  `rep_comment` text,
  PRIMARY KEY (`user_id`,`game_id`,`replace_id`),
  KEY `replacements_replace_id_idx` (`replace_id`),
  KEY `replace_game_idx` (`game_id`,`replace_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Triggers `Replacements`
--
DROP TRIGGER IF EXISTS `Replacements_ai`;
DELIMITER //
CREATE TRIGGER `Replacements_ai` AFTER INSERT ON `Replacements`
 FOR EACH ROW Begin	
	DECLARE l_room_id INT(5);
	DECLARE l_alias VARCHAR(50);
	DECLARE l_secret ENUM('on', '');
	DECLARE l_last_view DATETIME;
	DECLARE l_color VARCHAR(7);
	DECLARE l_lock ENUM('Off', 'On', 'Secure');
	DECLARE l_max_post INT(11);
	DECLARE l_remaining_post INT(11);
	DECLARE l_open DATETIME;
	DECLARE l_close DATETIME;
	DECLARE done BOOL DEFAULT FALSE;
	DECLARE cur_1 CURSOR FOR SELECT u.room_id,u.alias,u.secret,u.last_view,u.color,u.lock,u.max_post,u.remaining_post,u.open,u.close from Chat_users u, Chat_rooms r where r.game_id = new.game_id and u.room_id = r.id and u.user_id = new.user_id AND r.id NOT IN (SELECT cr.id FROM Chat_rooms cr, Chat_users cu WHERE cr.game_id = new.game_id AND cu.room_id = cr.id AND cu.user_id = new.replace_id);
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
	UPDATE Tally SET votee = new.replace_id WHERE game_id=new.game_id AND votee = new.user_id;
	UPDATE Tally SET voter = new.replace_id WHERE game_id=new.game_id AND voter = new.user_id;
	UPDATE Votes SET votee = new.replace_id WHERE game_id=new.game_id AND votee = new.user_id;
	UPDATE Votes SET voter = new.replace_id WHERE game_id=new.game_id AND voter = new.user_id;
	open cur_1;
	my_loop: LOOP
		FETCH cur_1 INTO l_room_id,l_alias,l_secret,l_last_view,l_color,l_lock,l_max_post,l_remaining_post,l_open,l_close;
		IF done THEN
			CLOSE cur_1;
			LEAVE my_loop;
		END IF;
		IF l_secret = 'on' THEN
			UPDATE Chat_users u SET u.user_id = new.replace_id WHERE u.room_id= l_room_id and u.user_id = new.user_id;
			UPDATE Chat_messages m SET m.user_id = new.replace_id WHERE m.room_id = l_room_id and m.user_id = new.user_id;
		ELSE
			INSERT INTO Chat_users(user_id,room_id, alias, secret,last_view,color,`lock`,max_post,remaining_post,open,close) VALUES(new.replace_id,l_room_id,l_alias,l_secret,l_last_view,l_color,l_lock,l_max_post,l_remaining_post,l_open,l_close);
		END IF;
	END LOOP;	
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `Roles`
--

CREATE TABLE IF NOT EXISTS `Roles` (
  `id` int(2) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=30 ;

-- --------------------------------------------------------

--
-- Table structure for table `Social_sites`
--

CREATE TABLE IF NOT EXISTS `Social_sites` (
  `id` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
  `site_name` varchar(200) NOT NULL,
  `url` varchar(200) DEFAULT NULL,
  `category` enum('Chatting','Gaming','Social Media','Personal','Other') NOT NULL,
  `link` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `site_name` (`site_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=55 ;

-- --------------------------------------------------------

--
-- Table structure for table `Social_users`
--

CREATE TABLE IF NOT EXISTS `Social_users` (
  `site_id` smallint(4) unsigned NOT NULL,
  `user_id` smallint(4) unsigned NOT NULL,
  `user_info` varchar(200) NOT NULL,
  PRIMARY KEY (`site_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Stats`
--

CREATE TABLE IF NOT EXISTS `Stats` (
  `id` int(3) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL DEFAULT 'Untitled',
  `sql` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=42 ;

-- --------------------------------------------------------

--
-- Table structure for table `Tally`
--

CREATE TABLE IF NOT EXISTS `Tally` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `game_id` int(11) NOT NULL,
  `day` int(11) NOT NULL,
  `votee` int(11) DEFAULT NULL,
  `voter` int(11) NOT NULL,
  `vote_article` int(11) NOT NULL,
  `vote_count` int(11) NOT NULL,
  `unvote` tinyint(1) NOT NULL DEFAULT '0',
  `unvote_article` int(11) DEFAULT NULL,
  `nightfall` tinyint(1) NOT NULL DEFAULT '0',
  `nightfall_article` int(11) DEFAULT NULL,
  `misc` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `game_id` (`game_id`,`day`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=59423 ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `Tally_display_inverted`
--
CREATE TABLE IF NOT EXISTS `Tally_display_inverted` (
`game_id` int(11)
,`day` int(11)
,`voter` varchar(50)
,`total` bigint(21)
,`votes_bgg` text
,`votes_html` text
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `Tally_display_lhlv`
--
CREATE TABLE IF NOT EXISTS `Tally_display_lhlv` (
`game_id` int(11)
,`day` int(11)
,`votee` varchar(50)
,`total` decimal(23,0)
,`votes_bgg` text
,`votes_html` text
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `Tally_display_lhv`
--
CREATE TABLE IF NOT EXISTS `Tally_display_lhv` (
`game_id` int(11)
,`day` int(11)
,`votee` varchar(50)
,`total` decimal(23,0)
,`votes_bgg` text
,`votes_html` text
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `Tally_votes`
--
CREATE TABLE IF NOT EXISTS `Tally_votes` (
`game_id` int(11)
,`day` int(11)
,`voter` varchar(50)
,`votee` varchar(50)
,`vote_count` int(11)
,`unvoted` varchar(3)
,`nightfall` varchar(3)
,`vote_article` int(11)
,`unvote_article` int(11)
,`nightfall_article` int(11)
);
-- --------------------------------------------------------

--
-- Table structure for table `Timezones`
--

CREATE TABLE IF NOT EXISTS `Timezones` (
  `zone` varchar(1) NOT NULL,
  `GMT` int(11) NOT NULL,
  `description` varchar(75) NOT NULL DEFAULT '',
  PRIMARY KEY (`zone`),
  UNIQUE KEY `GMT` (`GMT`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Update_calendar`
--

CREATE TABLE IF NOT EXISTS `Update_calendar` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `action` enum('add','delete','update') NOT NULL,
  `calendar_id` text,
  `game_id` int(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11829 ;

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE IF NOT EXISTS `Users` (
  `id` int(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT 'none',
  `level` enum('0','1','2','3') NOT NULL DEFAULT '3',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3238 ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `Users_game_all`
--
CREATE TABLE IF NOT EXISTS `Users_game_all` (
`game_id` int(11) unsigned
,`user_id` int(11) unsigned
,`type` varchar(11)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `Users_game_ranks`
--
CREATE TABLE IF NOT EXISTS `Users_game_ranks` (
`user_id` int(11) unsigned
,`name` varchar(50)
,`games_played` bigint(21)
,`rank` bigint(21)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `Users_game_totals`
--
CREATE TABLE IF NOT EXISTS `Users_game_totals` (
`user_id` int(11) unsigned
,`name` varchar(50)
,`games_played` bigint(21)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `Users_modded_ranks`
--
CREATE TABLE IF NOT EXISTS `Users_modded_ranks` (
`user_id` int(3) unsigned
,`name` varchar(50)
,`games_moderated` bigint(21)
,`rank` bigint(21)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `Users_modded_totals`
--
CREATE TABLE IF NOT EXISTS `Users_modded_totals` (
`user_id` int(3) unsigned
,`name` varchar(50)
,`games_moderated` bigint(21)
);
-- --------------------------------------------------------

--
-- Table structure for table `Users_ngrams`
--

CREATE TABLE IF NOT EXISTS `Users_ngrams` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `n` smallint(5) unsigned NOT NULL,
  `position` smallint(5) unsigned NOT NULL,
  `gram` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=40268 ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `Users_result_count`
--
CREATE TABLE IF NOT EXISTS `Users_result_count` (
`user_id` int(11) unsigned
,`result` varchar(7)
,`count` bigint(21)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `Users_result_side_count`
--
CREATE TABLE IF NOT EXISTS `Users_result_side_count` (
`user_id` int(11) unsigned
,`result` varchar(7)
,`side` varchar(5)
,`count` bigint(21)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `Users_series_result`
--
CREATE TABLE IF NOT EXISTS `Users_series_result` (
`series_id` int(5)
,`user_id` int(11) unsigned
,`side` enum('Good','Evil','Other')
,`winner` enum('','Evil','Good','Other')
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `Users_start_month`
--
CREATE TABLE IF NOT EXISTS `Users_start_month` (
`name` varchar(50)
,`start_month` varchar(5)
);
-- --------------------------------------------------------

--
-- Table structure for table `Votes`
--

CREATE TABLE IF NOT EXISTS `Votes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `game_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `day` int(11) NOT NULL,
  `voter` int(11) NOT NULL,
  `votee` int(11) DEFAULT NULL,
  `type` varchar(20) NOT NULL,
  `misc` text,
  `valid` tinyint(1) NOT NULL DEFAULT '1',
  `edited` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `valid` (`valid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=68957 ;

--
-- Triggers `Votes`
--
DROP TRIGGER IF EXISTS `Votes_bi`;
DELIMITER //
CREATE TRIGGER `Votes_bi` BEFORE INSERT ON `Votes`
 FOR EACH ROW vote_check:begin
declare tally_id int(11);
declare tally_nightfall int(11);
declare new_count int(11);
declare new_tally int(1);
if new.valid != 1 then
leave vote_check;
end if;
set new_tally := 0;
set new_count := (select max(vote_count) from Tally where game_id = new.game_id and day = new.day);
if(new_count is NULL) then
set new_count := 1;
else
set new_count := new_count + 1;
end if;
if(new.type = 'vote') then
select id, nightfall INTO tally_id, tally_nightfall from Tally where Tally.game_id=new.game_id and Tally.day=new.day and Tally.voter=new.voter and Tally.unvote=0;
if(tally_id is null) then
insert into Tally(game_id, day, votee, voter, vote_article, vote_count,misc) Values(new.game_id, new.day, new.votee, new.voter,new.article_id, new_count,new.misc);
set new_tally := 1;
elseif(new.misc = 'nightfall') then
update Tally set nightfall=1, nightfall_article=new.article_id where id=tally_id;
set new_tally = 1;
elseif(tally_nightfall = 0) then
update Tally set Tally.unvote=1, Tally.unvote_article=new.article_id where Tally.game_id=new.game_id and Tally.day=new.day and Tally.voter=new.voter and Tally.unvote=0 and Tally.nightfall=0;
insert into Tally(game_id, day, votee, voter, vote_article, vote_count,misc) Values(new.game_id, new.day, new.votee, new.voter,new.article_id, new_count,new.misc);
set new_tally := 1;
end if;
elseif(new.type = 'unvote') then
update Tally set Tally.unvote=1, Tally.unvote_article=new.article_id where Tally.game_id=new.game_id and Tally.day=new.day and Tally.voter=new.voter and Tally.unvote=0 and Tally.nightfall=0;
set new_tally = 1;
end if;
if(new_tally = 1) then
update Games set updated_tally = 1 where Games.id = new.game_id;
end if;
end
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `Votes_log`
--
CREATE TABLE IF NOT EXISTS `Votes_log` (
`game_id` int(11)
,`article_id` int(11)
,`day` int(11)
,`voter` varchar(50)
,`type` varchar(20)
,`votee` varchar(50)
,`misc` text
,`time_stamp` datetime
,`valid` varchar(3)
,`edited` varchar(3)
);
-- --------------------------------------------------------

--
-- Table structure for table `Wolfy_awards`
--

CREATE TABLE IF NOT EXISTS `Wolfy_awards` (
  `id` int(2) unsigned NOT NULL AUTO_INCREMENT,
  `award` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=40 ;

-- --------------------------------------------------------

--
-- Table structure for table `Wolfy_games`
--

CREATE TABLE IF NOT EXISTS `Wolfy_games` (
  `award_id` int(2) unsigned NOT NULL,
  `game_id` int(3) unsigned NOT NULL,
  `year` int(4) NOT NULL,
  `award_post` int(10) NOT NULL,
  PRIMARY KEY (`award_id`,`game_id`,`year`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Wolfy_players`
--

CREATE TABLE IF NOT EXISTS `Wolfy_players` (
  `award_id` int(2) unsigned NOT NULL,
  `user_id` int(3) unsigned NOT NULL,
  `year` int(4) NOT NULL,
  `award_post` int(10) NOT NULL,
  PRIMARY KEY (`award_id`,`user_id`,`year`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Wotw`
--

CREATE TABLE IF NOT EXISTS `Wotw` (
  `id` int(3) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(3) unsigned NOT NULL,
  `num` int(3) NOT NULL,
  `start_date` date NOT NULL,
  `thread_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=279 ;

-- --------------------------------------------------------

--
-- Structure for view `Moderators_active`
--
DROP TABLE IF EXISTS `Moderators_active`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dbuser`@`%` SQL SECURITY DEFINER VIEW `Moderators_active` AS select `Users`.`name` AS `name` from `Users` where exists(select NULL AS `NULL` from `Moderators` where ((`Moderators`.`user_id` = `Users`.`id`) and exists(select NULL AS `NULL` from `Games` where ((`Moderators`.`game_id` = `Games`.`id`) and (`Games`.`status` = _latin1'In Progress'))))) order by `Users`.`name`;

-- --------------------------------------------------------

--
-- Structure for view `Players_active`
--
DROP TABLE IF EXISTS `Players_active`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dbuser`@`%` SQL SECURITY DEFINER VIEW `Players_active` AS select `Users`.`name` AS `name` from `Users` where exists(select NULL AS `NULL` from `Players_all` where ((`Players_all`.`user_id` = `Users`.`id`) and exists(select NULL AS `NULL` from `Games` where ((`Players_all`.`game_id` = `Games`.`id`) and (`Games`.`status` = _latin1'In Progress'))))) order by `Users`.`name`;

-- --------------------------------------------------------

--
-- Structure for view `Players_all`
--
DROP TABLE IF EXISTS `Players_all`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dbuser`@`%` SQL SECURITY DEFINER VIEW `Players_all` AS select `p`.`game_id` AS `game_id`,`p`.`user_id` AS `user_id`,_utf8'player' AS `type`,`p`.`user_id` AS `original_id`,`p`.`player_alias` AS `player_alias`,`p`.`alias_color` AS `alias_color` from `Players` `p` where (not(exists(select NULL AS `NULL` from `Replacements` where ((`Replacements`.`game_id` = `p`.`game_id`) and (`Replacements`.`user_id` = `p`.`user_id`))))) union select `r`.`game_id` AS `game_id`,`r`.`replace_id` AS `user_id`,_utf8'replacement' AS `type`,`r`.`user_id` AS `user_id`,`p`.`player_alias` AS `player_alias`,`p`.`alias_color` AS `alias_color` from (`Replacements` `r` join `Players` `p`) where ((`r`.`game_id` = `p`.`game_id`) and (`p`.`user_id` = `r`.`user_id`)) union select `r`.`game_id` AS `game_id`,`r`.`user_id` AS `user_id`,_utf8'replaced' AS `type`,`r`.`user_id` AS `original_id`,`p`.`player_alias` AS `player_alias`,`p`.`alias_color` AS `alias_color` from (`Replacements` `r` join `Players` `p`) where ((`r`.`game_id` = `p`.`game_id`) and (`p`.`user_id` = `r`.`user_id`)) order by `game_id`,`user_id`;

-- --------------------------------------------------------

--
-- Structure for view `Players_month`
--
DROP TABLE IF EXISTS `Players_month`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dbuser`@`%` SQL SECURITY DEFINER VIEW `Players_month` AS select distinct date_format(`g`.`start_date`,_utf8'%y-%m') AS `month`,`p`.`user_id` AS `user_id` from (`Players_all` `p` join `Games` `g`) where ((`g`.`id` = `p`.`game_id`) and (`g`.`number` is not null)) order by date_format(`g`.`start_date`,_utf8'%y-%m');

-- --------------------------------------------------------

--
-- Structure for view `Players_r`
--
DROP TABLE IF EXISTS `Players_r`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dbuser`@`%` SQL SECURITY DEFINER VIEW `Players_r` AS (select `p`.`game_id` AS `game_id`,`p`.`user_id` AS `user_id`,`p`.`death_phase` AS `death_phase`,`p`.`death_day` AS `death_day`,`p`.`player_alias` AS `player_alias` from `Players` `p` where (not(exists(select NULL AS `NULL` from `Replacements` `r` where ((`r`.`game_id` = `p`.`game_id`) and (`p`.`user_id` = `r`.`user_id`)))))) union (select `r`.`game_id` AS `game_id`,`r`.`replace_id` AS `replace_id`,`p`.`death_phase` AS `death_phase`,`p`.`death_day` AS `death_day`,`p`.`player_alias` AS `player_alias` from (`Replacements` `r` join `Players` `p`) where ((`p`.`game_id` = `r`.`game_id`) and (`p`.`user_id` = `r`.`user_id`))) order by `game_id`,`user_id`;

-- --------------------------------------------------------

--
-- Structure for view `Players_result`
--
DROP TABLE IF EXISTS `Players_result`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dbuser`@`%` SQL SECURITY DEFINER VIEW `Players_result` AS select `g`.`id` AS `game_id`,`pa`.`user_id` AS `user_id`,`pa`.`original_id` AS `original_id`,if(((`g`.`winner` = _latin1'') or (`p`.`side` = _latin1'') or isnull(`p`.`side`)),_latin1'Unknown',if((`g`.`winner` = _latin1'Other'),_latin1'Other',if((`g`.`winner` = `p`.`side`),_latin1'Won',_latin1'Lost'))) AS `result` from (((`Games` `g` join `Players_all` `pa`) join `Players` `p`) join `Users` `u`) where ((`g`.`id` = `pa`.`game_id`) and (`pa`.`game_id` = `p`.`game_id`) and (`pa`.`original_id` = `p`.`user_id`) and (`g`.`number` is not null) and (`g`.`number` <> 0) and (`u`.`id` = `pa`.`user_id`)) order by `g`.`id`,`pa`.`user_id`;

-- --------------------------------------------------------

--
-- Structure for view `Tally_display_inverted`
--
DROP TABLE IF EXISTS `Tally_display_inverted`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dbuser`@`%` SQL SECURITY DEFINER VIEW `Tally_display_inverted` AS select `Tally_votes`.`game_id` AS `game_id`,`Tally_votes`.`day` AS `day`,`Tally_votes`.`voter` AS `voter`,count(0) AS `total`,group_concat(if((`Tally_votes`.`unvoted` = _latin1'Yes'),concat(_latin1'[-]',concat(`Tally_votes`.`votee`,_latin1'(',`Tally_votes`.`vote_count`,_latin1')'),_latin1'[/-]'),concat(`Tally_votes`.`votee`,if((`Tally_votes`.`nightfall` = _latin1'Yes'),_latin1'*',_latin1''),_latin1'(',`Tally_votes`.`vote_count`,_latin1')')) order by `Tally_votes`.`vote_count` ASC separator ', ') AS `votes_bgg`,group_concat(if((`Tally_votes`.`unvoted` = _latin1'Yes'),concat(_latin1'<strike>',concat(`Tally_votes`.`votee`,_latin1'(',`Tally_votes`.`vote_count`,_latin1')'),_latin1'</strike>'),concat(`Tally_votes`.`votee`,if((`Tally_votes`.`nightfall` = _latin1'Yes'),_latin1'*',_latin1''),_latin1'(',`Tally_votes`.`vote_count`,_latin1')')) order by `Tally_votes`.`vote_count` ASC separator ', ') AS `votes_html` from `Tally_votes` group by `Tally_votes`.`game_id`,`Tally_votes`.`day`,`Tally_votes`.`voter` order by `Tally_votes`.`game_id`,`Tally_votes`.`day`,`Tally_votes`.`voter`;

-- --------------------------------------------------------

--
-- Structure for view `Tally_display_lhlv`
--
DROP TABLE IF EXISTS `Tally_display_lhlv`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dbuser`@`%` SQL SECURITY DEFINER VIEW `Tally_display_lhlv` AS select `Tally_votes`.`game_id` AS `game_id`,`Tally_votes`.`day` AS `day`,`Tally_votes`.`votee` AS `votee`,sum(if((`Tally_votes`.`unvoted` = _latin1'Yes'),0,1)) AS `total`,group_concat(if((`Tally_votes`.`unvoted` = _latin1'Yes'),concat(_latin1'[-]',concat(`Tally_votes`.`voter`,_latin1'(',`Tally_votes`.`vote_count`,_latin1')'),_latin1'[/-]'),concat(`Tally_votes`.`voter`,if((`Tally_votes`.`nightfall` = _latin1'Yes'),_latin1'*',_latin1''),_latin1'(',`Tally_votes`.`vote_count`,_latin1')')) order by `Tally_votes`.`vote_count` ASC separator ', ') AS `votes_bgg`,group_concat(if((`Tally_votes`.`unvoted` = _latin1'Yes'),concat(_latin1'<strike>',concat(`Tally_votes`.`voter`,_latin1'(',`Tally_votes`.`vote_count`,_latin1')'),_latin1'</strike>'),concat(`Tally_votes`.`voter`,if((`Tally_votes`.`nightfall` = _latin1'Yes'),_latin1'*',_latin1''),_latin1'(',`Tally_votes`.`vote_count`,_latin1')')) order by `Tally_votes`.`vote_count` ASC separator ', ') AS `votes_html` from `Tally_votes` group by `Tally_votes`.`game_id`,`Tally_votes`.`day`,`Tally_votes`.`votee` order by `Tally_votes`.`game_id`,`Tally_votes`.`day`,if((`Tally_votes`.`votee` = _latin1'nightfall'),NULL,1) desc,sum(if((`Tally_votes`.`unvoted` = _latin1'Yes'),0,1)) desc,max(if((`Tally_votes`.`unvoted` = _latin1'Yes'),NULL,`Tally_votes`.`vote_count`));

-- --------------------------------------------------------

--
-- Structure for view `Tally_display_lhv`
--
DROP TABLE IF EXISTS `Tally_display_lhv`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dbuser`@`%` SQL SECURITY DEFINER VIEW `Tally_display_lhv` AS select `Tally_votes`.`game_id` AS `game_id`,`Tally_votes`.`day` AS `day`,`Tally_votes`.`votee` AS `votee`,sum(if((`Tally_votes`.`unvoted` = _latin1'Yes'),0,1)) AS `total`,group_concat(if((`Tally_votes`.`unvoted` = _latin1'Yes'),concat('[-]',concat(`Tally_votes`.`voter`,'(',`Tally_votes`.`vote_count`,')'),'[/-]'),concat(`Tally_votes`.`voter`,convert(if((`Tally_votes`.`nightfall` = _latin1'Yes'),_utf8'*',_utf8'') using latin1),'(',`Tally_votes`.`vote_count`,')')) order by `Tally_votes`.`vote_count` ASC separator ', ') AS `votes_bgg`,group_concat(if((`Tally_votes`.`unvoted` = _latin1'Yes'),concat('<strike>',concat(`Tally_votes`.`voter`,'(',`Tally_votes`.`vote_count`,')'),'</strike>'),concat(`Tally_votes`.`voter`,convert(if((`Tally_votes`.`nightfall` = _latin1'Yes'),_utf8'*',_utf8'') using latin1),'(',`Tally_votes`.`vote_count`,')')) order by `Tally_votes`.`vote_count` ASC separator ', ') AS `votes_html` from `Tally_votes` group by `Tally_votes`.`game_id`,`Tally_votes`.`day`,`Tally_votes`.`votee` order by `Tally_votes`.`game_id`,`Tally_votes`.`day`,if((`Tally_votes`.`votee` = _latin1'nightfall'),NULL,1) desc,sum(if((`Tally_votes`.`unvoted` = _latin1'Yes'),0,1)) desc,min(if((`Tally_votes`.`unvoted` = _latin1'Yes'),NULL,`Tally_votes`.`vote_count`));

-- --------------------------------------------------------

--
-- Structure for view `Tally_votes`
--
DROP TABLE IF EXISTS `Tally_votes`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dbuser`@`%` SQL SECURITY DEFINER VIEW `Tally_votes` AS (select `Tally`.`game_id` AS `game_id`,`Tally`.`day` AS `day`,`u1`.`name` AS `voter`,`u2`.`name` AS `votee`,`Tally`.`vote_count` AS `vote_count`,if((`Tally`.`unvote` = 0),_latin1'No',_latin1'Yes') AS `unvoted`,if((`Tally`.`nightfall` = 0),_latin1'No',_latin1'Yes') AS `nightfall`,`Tally`.`vote_article` AS `vote_article`,`Tally`.`unvote_article` AS `unvote_article`,`Tally`.`nightfall_article` AS `nightfall_article` from ((`Tally` join `Users` `u1`) join `Users` `u2`) where ((`Tally`.`voter` = `u1`.`id`) and (`Tally`.`votee` = `u2`.`id`))) union (select `Tally`.`game_id` AS `game_id`,`Tally`.`day` AS `day`,`u1`.`name` AS `voter`,`Tally`.`misc` AS `votee`,`Tally`.`vote_count` AS `vote_count`,if((`Tally`.`unvote` = 0),_latin1'No',_latin1'Yes') AS `unvoted`,if((`Tally`.`nightfall` = 0),_latin1'No',_latin1'Yes') AS `nightfall`,`Tally`.`vote_article` AS `vote_article`,`Tally`.`unvote_article` AS `unvote_article`,`Tally`.`nightfall_article` AS `nightfall_article` from (`Tally` join `Users` `u1`) where ((`Tally`.`voter` = `u1`.`id`) and (`Tally`.`votee` = 0))) order by `game_id`,`day`,`vote_count`;

-- --------------------------------------------------------

--
-- Structure for view `Users_game_all`
--
DROP TABLE IF EXISTS `Users_game_all`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dbuser`@`%` SQL SECURITY DEFINER VIEW `Users_game_all` AS select `Players`.`game_id` AS `game_id`,`Players`.`user_id` AS `user_id`,_utf8'player' AS `type` from `Players` union select `Moderators`.`game_id` AS `game_id`,`Moderators`.`user_id` AS `user_id`,_utf8'moderator' AS `type` from `Moderators` union select `Replacements`.`game_id` AS `game_id`,`Replacements`.`replace_id` AS `user_id`,_utf8'replacement' AS `type` from `Replacements` order by `game_id`,`user_id`;

-- --------------------------------------------------------

--
-- Structure for view `Users_game_ranks`
--
DROP TABLE IF EXISTS `Users_game_ranks`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dbuser`@`%` SQL SECURITY DEFINER VIEW `Users_game_ranks` AS select `a`.`user_id` AS `user_id`,`a`.`name` AS `name`,`a`.`games_played` AS `games_played`,count(`b`.`games_played`) AS `rank` from (`Users_game_totals` `a` join `Users_game_totals` `b`) where ((`a`.`games_played` < `b`.`games_played`) or ((`a`.`games_played` = `b`.`games_played`) and (`a`.`user_id` = `b`.`user_id`))) group by `a`.`user_id`,`a`.`name`,`a`.`games_played` order by `a`.`games_played` desc,`a`.`name`;

-- --------------------------------------------------------

--
-- Structure for view `Users_game_totals`
--
DROP TABLE IF EXISTS `Users_game_totals`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dbuser`@`%` SQL SECURITY DEFINER VIEW `Users_game_totals` AS select `Players_all`.`user_id` AS `user_id`,`Users`.`name` AS `name`,count(0) AS `games_played` from ((`Users` join `Players_all`) join `Games`) where ((`Games`.`number` <> 0) and (`Users`.`id` = `Players_all`.`user_id`) and (`Players_all`.`game_id` = `Games`.`id`) and (`Games`.`status` in (_latin1'In Progress',_latin1'Finished'))) group by `Users`.`name` order by count(0) desc;

-- --------------------------------------------------------

--
-- Structure for view `Users_modded_ranks`
--
DROP TABLE IF EXISTS `Users_modded_ranks`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dbuser`@`%` SQL SECURITY DEFINER VIEW `Users_modded_ranks` AS select `a`.`user_id` AS `user_id`,`a`.`name` AS `name`,`a`.`games_moderated` AS `games_moderated`,count(`b`.`games_moderated`) AS `rank` from (`Users_modded_totals` `a` join `Users_modded_totals` `b`) where ((`a`.`games_moderated` < `b`.`games_moderated`) or ((`a`.`games_moderated` = `b`.`games_moderated`) and (`a`.`user_id` = `b`.`user_id`))) group by `a`.`user_id`,`a`.`name`,`a`.`games_moderated` order by `a`.`games_moderated` desc,`a`.`name`;

-- --------------------------------------------------------

--
-- Structure for view `Users_modded_totals`
--
DROP TABLE IF EXISTS `Users_modded_totals`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dbuser`@`%` SQL SECURITY DEFINER VIEW `Users_modded_totals` AS select `Moderators`.`user_id` AS `user_id`,`Users`.`name` AS `name`,count(0) AS `games_moderated` from ((`Users` join `Moderators`) join `Games`) where ((`Moderators`.`user_id` <> 306) and (`Games`.`number` <> 0) and (`Users`.`id` = `Moderators`.`user_id`) and (`Moderators`.`game_id` = `Games`.`id`) and (`Games`.`status` in (_latin1'In Progress',_latin1'Finished'))) group by `Users`.`name` order by count(0) desc;

-- --------------------------------------------------------

--
-- Structure for view `Users_result_count`
--
DROP TABLE IF EXISTS `Users_result_count`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dbuser`@`%` SQL SECURITY DEFINER VIEW `Users_result_count` AS (select `Players_result`.`user_id` AS `user_id`,`Players_result`.`result` AS `result`,count(0) AS `count` from `Players_result` group by `Players_result`.`user_id`,`Players_result`.`result`) union (select `Players_result`.`user_id` AS `user_id`,_utf8'Total' AS `result`,count(0) AS `count` from `Players_result` where ((`Players_result`.`result` = _latin1'Won') or (`Players_result`.`result` = _latin1'Lost')) group by `Players_result`.`user_id`) order by `user_id`,(`result` = _utf8'Unknown'),(`result` = _utf8'Other'),(`result` = _utf8'Lost'),(`result` = _utf8'Won');

-- --------------------------------------------------------

--
-- Structure for view `Users_result_side_count`
--
DROP TABLE IF EXISTS `Users_result_side_count`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dbuser`@`%` SQL SECURITY DEFINER VIEW `Users_result_side_count` AS (select `Players_result`.`user_id` AS `user_id`,`Players_result`.`result` AS `result`,`Players`.`side` AS `side`,count(0) AS `count` from (`Players_result` join `Players`) where ((`Players_result`.`original_id` = `Players`.`user_id`) and (`Players_result`.`game_id` = `Players`.`game_id`) and (`Players_result`.`result` <> _latin1'Unknown')) group by `Players_result`.`result`,`Players`.`side`,`Players_result`.`user_id`) union (select `Players_result`.`user_id` AS `user_id`,_latin1'Total' AS `result`,`Players`.`side` AS `side`,count(0) AS `count( * )` from (`Players_result` join `Players`) where ((`Players_result`.`original_id` = `Players`.`user_id`) and (`Players_result`.`game_id` = `Players`.`game_id`) and (`Players_result`.`result` <> _latin1'Unknown') and ((`Players_result`.`result` = _latin1'Won') or (`Players_result`.`result` = _latin1'Lost'))) group by `Players`.`side`,`Players_result`.`user_id`) order by `user_id`,`side`,(`result` = _latin1'Unknown'),(`result` = _latin1'Other'),(`result` = _latin1'Lost'),(`result` = _latin1'Won');

-- --------------------------------------------------------

--
-- Structure for view `Users_series_result`
--
DROP TABLE IF EXISTS `Users_series_result`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dbuser`@`%` SQL SECURITY DEFINER VIEW `Users_series_result` AS select `g`.`series_id` AS `series_id`,`p`.`user_id` AS `user_id`,`po`.`side` AS `side`,`g`.`winner` AS `winner` from ((`Players_all` `p` join `Players` `po`) join `Games` `g`) where ((`g`.`series_id` is not null) and (`g`.`status` = _latin1'Finished') and (`p`.`game_id` = `g`.`id`) and (`po`.`user_id` = `p`.`original_id`) and (`po`.`game_id` = `p`.`game_id`));

-- --------------------------------------------------------

--
-- Structure for view `Users_start_month`
--
DROP TABLE IF EXISTS `Users_start_month`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dbuser`@`%` SQL SECURITY DEFINER VIEW `Users_start_month` AS select `Users`.`name` AS `name`,date_format(min(`Games`.`start_date`),_utf8'%y-%m') AS `start_month` from ((`Users` join `Players`) join `Games`) where ((`Users`.`id` = `Players`.`user_id`) and (`Games`.`id` = `Players`.`game_id`) and (`Games`.`start_date` <> _utf8'0000-00-00')) group by `Users`.`name` order by date_format(min(`Games`.`start_date`),_utf8'%y-%m');

-- --------------------------------------------------------

--
-- Structure for view `Votes_log`
--
DROP TABLE IF EXISTS `Votes_log`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dbuser`@`%` SQL SECURITY DEFINER VIEW `Votes_log` AS select `Votes`.`game_id` AS `game_id`,`Votes`.`article_id` AS `article_id`,`Votes`.`day` AS `day`,`u1`.`name` AS `voter`,`Votes`.`type` AS `type`,`u2`.`name` AS `votee`,`Votes`.`misc` AS `misc`,`Posts`.`time_stamp` AS `time_stamp`,if((`Votes`.`valid` = 1),_utf8'Yes',_utf8'No') AS `valid`,if((`Votes`.`edited` = 1),_utf8'Yes',_utf8'No') AS `edited` from ((`Posts` join `Users` `u1`) join (`Votes` left join `Users` `u2` on((`Votes`.`votee` = `u2`.`id`)))) where ((`Votes`.`voter` = `u1`.`id`) and (`Votes`.`article_id` = `Posts`.`article_id`)) order by `Votes`.`day`,`Votes`.`article_id`,`Votes`.`type`;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
