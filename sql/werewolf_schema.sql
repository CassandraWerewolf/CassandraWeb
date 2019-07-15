-- MySQL dump 10.13  Distrib 5.5.62, for Linux (x86_64)
--
-- Host: cassandra-db.cmkhy6b9hsrs.us-east-1.rds.amazonaws.com    Database: werewolf
-- ------------------------------------------------------
-- Server version	5.6.40-log
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `AM_roles`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AM_roles` (
  `id` int(3) unsigned zerofill NOT NULL,
  `template_id` int(2) NOT NULL,
  `role_id` int(2) unsigned zerofill NOT NULL,
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
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `AM_template`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AM_template` (
  `id` int(2) unsigned zerofill NOT NULL,
  `owner_id` smallint(4) unsigned zerofill NOT NULL,
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
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Auto_dusk`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Auto_dusk` (
  `game_id` smallint(4) unsigned zerofill NOT NULL,
  `mon` tinyint(1) NOT NULL DEFAULT '0',
  `tue` tinyint(1) NOT NULL DEFAULT '0',
  `wed` tinyint(1) NOT NULL DEFAULT '0',
  `thu` tinyint(1) NOT NULL DEFAULT '0',
  `fri` tinyint(1) NOT NULL DEFAULT '0',
  `sat` tinyint(1) NOT NULL DEFAULT '0',
  `sun` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`game_id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Bio`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Bio` (
  `user_id` int(3) unsigned zerofill NOT NULL,
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
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `CC_info`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CC_info` (
  `game_id` smallint(4) unsigned zerofill NOT NULL,
  `user_id` smallint(3) unsigned zerofill NOT NULL,
  `claim_time` datetime NOT NULL,
  `challenger_id` smallint(4) DEFAULT NULL,
  `type_error` enum('game','player') DEFAULT NULL,
  `desc_error` text,
  PRIMARY KEY (`game_id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `CC_players`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CC_players` (
  `user_id` smallint(4) unsigned zerofill NOT NULL,
  `team` varchar(50) NOT NULL,
  PRIMARY KEY (`user_id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Chat_message_actions`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Chat_message_actions` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `target_id` int(11) NOT NULL,
  `misc` text NOT NULL,
  PRIMARY KEY (`id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Chat_messages`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Chat_messages` (
  `id` int(11) unsigned NOT NULL,
  `room_id` int(5) NOT NULL,
  `user_id` int(3) NOT NULL,
  `message` text NOT NULL,
  `post_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `Chat_messages_room_id_idx` (`room_id`,`post_time`),
  KEY `Chat_messages_user_id_idx` (`user_id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Chat_rooms`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Chat_rooms` (
  `id` int(5) unsigned zerofill NOT NULL,
  `game_id` int(4) unsigned zerofill NOT NULL,
  `name` varchar(60) NOT NULL,
  `lock` enum('Off','On','Secure') NOT NULL DEFAULT 'Off',
  `max_post` int(11) DEFAULT NULL,
  `remaining_post` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  `monitor` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `Chat_room_game_id_idx` (`game_id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Chat_users`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Chat_users` (
  `room_id` int(5) unsigned zerofill NOT NULL,
  `user_id` int(3) unsigned zerofill NOT NULL,
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
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Exits`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Exits` (
  `id` int(5) unsigned zerofill NOT NULL,
  `game_id` int(4) unsigned zerofill NOT NULL,
  `name` varchar(60) NOT NULL,
  `travel_text` text,
  `template_id` int(5) unsigned zerofill DEFAULT NULL,
  `comment` text,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Game_day_log`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Game_day_log` (
  `id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `phase` enum('day','night') NOT NULL DEFAULT 'night',
  `day` int(11) NOT NULL DEFAULT '0',
  `change_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Game_orders`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Game_orders` (
  `id` int(5) unsigned zerofill NOT NULL,
  `user_id` int(3) NOT NULL,
  `game_id` int(4) NOT NULL,
  `desc` varchar(50) NOT NULL,
  `target_id` int(3) DEFAULT NULL,
  `user_text` varchar(255) NOT NULL,
  `cancel` smallint(1) DEFAULT NULL,
  `day` int(2) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `game_id` (`game_id`,`user_id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Game_series`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Game_series` (
  `id` int(5) unsigned zerofill NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Games`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Games` (
  `id` int(4) unsigned zerofill NOT NULL,
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
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Item_orders`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Item_orders` (
  `id` int(5) unsigned zerofill NOT NULL,
  `user_id` int(5) unsigned zerofill NOT NULL,
  `game_id` int(5) unsigned zerofill NOT NULL,
  `item_id` int(5) unsigned zerofill NOT NULL,
  `target_id` int(5) unsigned zerofill NOT NULL,
  `target_type` enum('user','loc') NOT NULL,
  `status` enum('active','canceled','processed') NOT NULL DEFAULT 'active',
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `game_id` (`game_id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Item_templates`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Item_templates` (
  `id` int(5) unsigned zerofill NOT NULL,
  `game_id` int(4) unsigned zerofill NOT NULL,
  `name` varchar(60) NOT NULL,
  `description` text CHARACTER SET latin1 COLLATE latin1_spanish_ci,
  `visibility` enum('obvious','hide','conceal','invis') NOT NULL DEFAULT 'conceal',
  `mobility` enum('fixed','heavy','mobile','nonphys') NOT NULL DEFAULT 'nonphys',
  `room_id` int(6) DEFAULT NULL,
  `room_alias` varchar(50) DEFAULT NULL,
  `room_color` varchar(7) DEFAULT '#000000',
  PRIMARY KEY (`id`),
  KEY `game_id` (`game_id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Items`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Items` (
  `id` int(5) unsigned zerofill NOT NULL,
  `template_id` int(5) unsigned zerofill NOT NULL,
  `game_id` int(4) unsigned zerofill NOT NULL,
  `name` varchar(60) NOT NULL,
  `owner_ref_id` int(5) unsigned zerofill DEFAULT NULL,
  `owner_type` enum('user','loc') NOT NULL DEFAULT 'user',
  `description` text,
  `visibility` enum('obvious','hide','conceal','invis') NOT NULL DEFAULT 'conceal',
  `mobility` enum('fixed','heavy','mobile','nonphys') NOT NULL DEFAULT 'nonphys',
  `room_id` int(6) DEFAULT NULL,
  `room_alias` varchar(50) DEFAULT NULL,
  `room_color` varchar(7) DEFAULT '#000000',
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Loc_exits`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Loc_exits` (
  `loc_from_id` int(5) unsigned zerofill NOT NULL,
  `loc_to_id` int(5) unsigned zerofill NOT NULL,
  `exit_id` int(5) unsigned zerofill NOT NULL,
  PRIMARY KEY (`loc_from_id`,`exit_id`),
  KEY `exit_id` (`exit_id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Locations`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Locations` (
  `id` int(5) unsigned zerofill NOT NULL,
  `game_id` int(4) unsigned zerofill NOT NULL,
  `name` varchar(60) NOT NULL,
  `description` text,
  `comment` text,
  `subgame_id` int(4) unsigned zerofill DEFAULT NULL,
  `room_id` int(5) unsigned zerofill DEFAULT NULL,
  `created` datetime NOT NULL,
  `lock` enum('Off','On','Secure') NOT NULL DEFAULT 'Off',
  `visibility` enum('None','Search','Full') NOT NULL DEFAULT 'Full',
  PRIMARY KEY (`id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Misc_users`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Misc_users` (
  `user_id` int(3) unsigned zerofill NOT NULL,
  `google_calendar` text,
  PRIMARY KEY (`user_id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Moderators`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Moderators` (
  `user_id` int(3) unsigned zerofill NOT NULL DEFAULT '000',
  `game_id` int(4) unsigned zerofill NOT NULL DEFAULT '0000',
  `comment` text,
  PRIMARY KEY (`user_id`,`game_id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `Moderators_active`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `Moderators_active` (
  `name` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `Move_orders`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Move_orders` (
  `id` int(5) unsigned zerofill NOT NULL,
  `user_id` int(4) unsigned zerofill NOT NULL,
  `game_id` int(4) unsigned zerofill NOT NULL,
  `exit_id` int(5) unsigned zerofill NOT NULL,
  `status` enum('active','canceled','processed') NOT NULL DEFAULT 'active',
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `game_id` (`game_id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Physics_processing`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Physics_processing` (
  `id` int(5) unsigned zerofill NOT NULL,
  `game_id` int(4) unsigned zerofill NOT NULL,
  `type` enum('item','movement') NOT NULL,
  `frequency` enum('7daily','5daily','hourly','2hourly','30mins','15mins','immediate') NOT NULL,
  `minute` tinyint(2) unsigned NOT NULL,
  `hour` tinyint(2) unsigned DEFAULT NULL,
  `last_run` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Players`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Players` (
  `user_id` int(3) unsigned zerofill NOT NULL DEFAULT '000',
  `game_id` int(4) unsigned zerofill NOT NULL DEFAULT '0000',
  `role_name` varchar(50) DEFAULT NULL,
  `role_id` int(2) unsigned zerofill NOT NULL DEFAULT '01',
  `side` enum('Good','Evil','Other') DEFAULT NULL,
  `game_action` enum('none','alive','dead','all') NOT NULL DEFAULT 'none',
  `ga_desc` text,
  `ga_text` enum('','on') NOT NULL DEFAULT '',
  `ga_group` varchar(50) DEFAULT NULL,
  `ga_lock` smallint(1) DEFAULT NULL,
  `mod_comment` text,
  `user_comment` text,
  `update_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `death_phase` enum('','Alive','Day','Night') DEFAULT NULL,
  `death_day` smallint(2) DEFAULT NULL,
  `need_replace` tinyint(1) DEFAULT NULL,
  `automod_role_id` smallint(4) DEFAULT NULL,
  `automod_promoted_id` smallint(4) DEFAULT NULL,
  `need_to_confirm` tinyint(4) DEFAULT NULL,
  `tough_lives` tinyint(1) NOT NULL DEFAULT '0',
  `player_alias` varchar(50) DEFAULT NULL,
  `alias_color` varchar(7) NOT NULL DEFAULT '#000000',
  `loc_id` int(5) unsigned zerofill DEFAULT NULL,
  `modchat_id` int(5) unsigned zerofill DEFAULT NULL,
  `phys_move_limit` smallint(4) unsigned DEFAULT NULL,
  `phys_item_limit` int(4) unsigned DEFAULT NULL,
  `phys_moves` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`game_id`),
  KEY `game_id_idx` (`game_id`),
  KEY `side` (`side`),
  KEY `player_alias` (`player_alias`),
  KEY `loc_id` (`loc_id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `Players_active`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `Players_active` (
  `name` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `Players_all`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `Players_all` (
  `game_id` tinyint NOT NULL,
  `user_id` tinyint NOT NULL,
  `type` tinyint NOT NULL,
  `original_id` tinyint NOT NULL,
  `player_alias` tinyint NOT NULL,
  `alias_color` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `Players_month`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `Players_month` (
  `month` tinyint NOT NULL,
  `user_id` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `Players_r`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `Players_r` (
  `game_id` tinyint NOT NULL,
  `user_id` tinyint NOT NULL,
  `death_phase` tinyint NOT NULL,
  `death_day` tinyint NOT NULL,
  `player_alias` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `Players_result`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `Players_result` (
  `game_id` tinyint NOT NULL,
  `user_id` tinyint NOT NULL,
  `original_id` tinyint NOT NULL,
  `result` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `Post_collect_slots`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Post_collect_slots` (
  `id` bigint(20) NOT NULL,
  `game_id` int(10) unsigned DEFAULT NULL,
  `minute` tinyint(3) unsigned NOT NULL,
  `run_order` tinyint(4) NOT NULL,
  `last_dumped` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Posts`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Posts` (
  `article_id` int(10) NOT NULL DEFAULT '0',
  `game_id` int(4) unsigned zerofill NOT NULL DEFAULT '0000',
  `user_id` int(3) unsigned zerofill NOT NULL DEFAULT '000',
  `time_stamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `text` text NOT NULL,
  `page` int(3) NOT NULL DEFAULT '0',
  `num_edits` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`article_id`),
  KEY `group_user_idx` (`game_id`,`user_id`),
  KEY `page_idx` (`page`),
  FULLTEXT KEY `text` (`text`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Replacements`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Replacements` (
  `user_id` int(3) unsigned zerofill NOT NULL DEFAULT '000',
  `game_id` int(4) unsigned zerofill NOT NULL DEFAULT '0000',
  `replace_id` int(3) unsigned zerofill NOT NULL DEFAULT '000',
  `period` enum('Day','Night') NOT NULL DEFAULT 'Day',
  `number` int(11) NOT NULL DEFAULT '0',
  `rep_comment` text,
  PRIMARY KEY (`user_id`,`game_id`,`replace_id`),
  KEY `replacements_replace_id_idx` (`replace_id`),
  KEY `replace_game_idx` (`game_id`,`replace_id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Roles`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Roles` (
  `id` int(2) unsigned zerofill NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Social_sites`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Social_sites` (
  `id` smallint(4) unsigned zerofill NOT NULL,
  `site_name` varchar(200) NOT NULL,
  `url` varchar(200) DEFAULT NULL,
  `category` enum('Chatting','Gaming','Social Media','Personal','Other') NOT NULL,
  `link` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `site_name` (`site_name`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Social_users`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Social_users` (
  `site_id` smallint(4) unsigned zerofill NOT NULL,
  `user_id` smallint(4) unsigned zerofill NOT NULL,
  `user_info` varchar(200) NOT NULL,
  PRIMARY KEY (`site_id`,`user_id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Stats`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Stats` (
  `id` int(3) unsigned zerofill NOT NULL,
  `title` varchar(100) NOT NULL DEFAULT 'Untitled',
  `sql` text NOT NULL,
  PRIMARY KEY (`id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Tally`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Tally` (
  `id` int(11) NOT NULL,
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
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `Tally_display_inverted`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `Tally_display_inverted` (
  `game_id` tinyint NOT NULL,
  `day` tinyint NOT NULL,
  `voter` tinyint NOT NULL,
  `total` tinyint NOT NULL,
  `votes_bgg` tinyint NOT NULL,
  `votes_html` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `Tally_display_lhlv`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `Tally_display_lhlv` (
  `game_id` tinyint NOT NULL,
  `day` tinyint NOT NULL,
  `votee` tinyint NOT NULL,
  `total` tinyint NOT NULL,
  `votes_bgg` tinyint NOT NULL,
  `votes_html` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `Tally_display_lhv`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `Tally_display_lhv` (
  `game_id` tinyint NOT NULL,
  `day` tinyint NOT NULL,
  `votee` tinyint NOT NULL,
  `total` tinyint NOT NULL,
  `votes_bgg` tinyint NOT NULL,
  `votes_html` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `Tally_votes`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `Tally_votes` (
  `game_id` tinyint NOT NULL,
  `day` tinyint NOT NULL,
  `voter` tinyint NOT NULL,
  `votee` tinyint NOT NULL,
  `vote_count` tinyint NOT NULL,
  `unvoted` tinyint NOT NULL,
  `nightfall` tinyint NOT NULL,
  `vote_article` tinyint NOT NULL,
  `unvote_article` tinyint NOT NULL,
  `nightfall_article` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `Timezones`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Timezones` (
  `zone` varchar(1) NOT NULL,
  `GMT` int(11) NOT NULL,
  `description` varchar(75) NOT NULL DEFAULT '',
  PRIMARY KEY (`zone`),
  UNIQUE KEY `GMT` (`GMT`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Update_calendar`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Update_calendar` (
  `id` int(5) unsigned zerofill NOT NULL,
  `action` enum('add','delete','update') NOT NULL,
  `calendar_id` text,
  `game_id` int(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Users`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Users` (
  `id` int(3) unsigned zerofill NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT 'none',
  `level` enum('0','1','2','3') NOT NULL DEFAULT '3',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `Users_game_all`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `Users_game_all` (
  `game_id` tinyint NOT NULL,
  `user_id` tinyint NOT NULL,
  `type` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `Users_game_ranks`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `Users_game_ranks` (
  `user_id` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `games_played` tinyint NOT NULL,
  `rank` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `Users_game_totals`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `Users_game_totals` (
  `user_id` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `games_played` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `Users_modded_ranks`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `Users_modded_ranks` (
  `user_id` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `games_moderated` tinyint NOT NULL,
  `rank` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `Users_modded_totals`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `Users_modded_totals` (
  `user_id` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `games_moderated` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `Users_ngrams`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Users_ngrams` (
  `id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `n` smallint(5) unsigned NOT NULL,
  `position` smallint(5) unsigned NOT NULL,
  `gram` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `Users_result_count`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `Users_result_count` (
  `user_id` tinyint NOT NULL,
  `result` tinyint NOT NULL,
  `count` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `Users_result_side_count`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `Users_result_side_count` (
  `user_id` tinyint NOT NULL,
  `result` tinyint NOT NULL,
  `side` tinyint NOT NULL,
  `count` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `Users_series_result`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `Users_series_result` (
  `series_id` tinyint NOT NULL,
  `user_id` tinyint NOT NULL,
  `side` tinyint NOT NULL,
  `winner` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `Users_start_month`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `Users_start_month` (
  `name` tinyint NOT NULL,
  `start_month` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `Votes`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Votes` (
  `id` int(11) NOT NULL,
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
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `Votes_log`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `Votes_log` (
  `game_id` tinyint NOT NULL,
  `article_id` tinyint NOT NULL,
  `day` tinyint NOT NULL,
  `voter` tinyint NOT NULL,
  `type` tinyint NOT NULL,
  `votee` tinyint NOT NULL,
  `misc` tinyint NOT NULL,
  `time_stamp` tinyint NOT NULL,
  `valid` tinyint NOT NULL,
  `edited` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `Wolfy_awards`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Wolfy_awards` (
  `id` int(2) unsigned zerofill NOT NULL,
  `award` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Wolfy_games`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Wolfy_games` (
  `award_id` int(2) unsigned zerofill NOT NULL,
  `game_id` int(3) unsigned zerofill NOT NULL,
  `year` int(4) NOT NULL,
  `award_post` int(10) NOT NULL,
  PRIMARY KEY (`award_id`,`game_id`,`year`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Wolfy_players`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Wolfy_players` (
  `award_id` int(2) unsigned zerofill NOT NULL,
  `user_id` int(3) unsigned zerofill NOT NULL,
  `year` int(4) NOT NULL,
  `award_post` int(10) NOT NULL,
  PRIMARY KEY (`award_id`,`user_id`,`year`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Wotw`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Wotw` (
  `id` int(3) unsigned zerofill NOT NULL,
  `user_id` int(3) unsigned zerofill NOT NULL,
  `num` int(3) NOT NULL,
  `start_date` date NOT NULL,
  `thread_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Final view structure for view `Moderators_active`
--

/*!50001 DROP TABLE IF EXISTS `Moderators_active`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`dbuser`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `Moderators_active` AS select `Users`.`name` AS `name` from `Users` where exists(select NULL AS `NULL` from `Moderators` where ((`Moderators`.`user_id` = `Users`.`id`) and exists(select NULL AS `NULL` from `Games` where ((`Moderators`.`game_id` = `Games`.`id`) and (`Games`.`status` = _latin1'In Progress'))))) order by `Users`.`name` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `Players_active`
--

/*!50001 DROP TABLE IF EXISTS `Players_active`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`dbuser`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `Players_active` AS select `Users`.`name` AS `name` from `Users` where exists(select NULL AS `NULL` from `Players_all` where ((`Players_all`.`user_id` = `Users`.`id`) and exists(select NULL AS `NULL` from `Games` where ((`Players_all`.`game_id` = `Games`.`id`) and (`Games`.`status` = _latin1'In Progress'))))) order by `Users`.`name` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `Players_all`
--

/*!50001 DROP TABLE IF EXISTS `Players_all`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`dbuser`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `Players_all` AS select `p`.`game_id` AS `game_id`,`p`.`user_id` AS `user_id`,_utf8'player' AS `type`,`p`.`user_id` AS `original_id`,`p`.`player_alias` AS `player_alias`,`p`.`alias_color` AS `alias_color` from `Players` `p` where (not(exists(select NULL AS `NULL` from `Replacements` where ((`Replacements`.`game_id` = `p`.`game_id`) and (`Replacements`.`user_id` = `p`.`user_id`))))) union select `r`.`game_id` AS `game_id`,`r`.`replace_id` AS `user_id`,_utf8'replacement' AS `type`,`r`.`user_id` AS `user_id`,`p`.`player_alias` AS `player_alias`,`p`.`alias_color` AS `alias_color` from (`Replacements` `r` join `Players` `p`) where ((`r`.`game_id` = `p`.`game_id`) and (`p`.`user_id` = `r`.`user_id`)) union select `r`.`game_id` AS `game_id`,`r`.`user_id` AS `user_id`,_utf8'replaced' AS `type`,`r`.`user_id` AS `original_id`,`p`.`player_alias` AS `player_alias`,`p`.`alias_color` AS `alias_color` from (`Replacements` `r` join `Players` `p`) where ((`r`.`game_id` = `p`.`game_id`) and (`p`.`user_id` = `r`.`user_id`)) order by `game_id`,`user_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `Players_month`
--

/*!50001 DROP TABLE IF EXISTS `Players_month`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`dbuser`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `Players_month` AS select distinct date_format(`g`.`start_date`,_utf8'%y-%m') AS `month`,`p`.`user_id` AS `user_id` from (`Players_all` `p` join `Games` `g`) where ((`g`.`id` = `p`.`game_id`) and (`g`.`number` is not null)) order by date_format(`g`.`start_date`,_utf8'%y-%m') */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `Players_r`
--

/*!50001 DROP TABLE IF EXISTS `Players_r`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`dbuser`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `Players_r` AS (select `p`.`game_id` AS `game_id`,`p`.`user_id` AS `user_id`,`p`.`death_phase` AS `death_phase`,`p`.`death_day` AS `death_day`,`p`.`player_alias` AS `player_alias` from `Players` `p` where (not(exists(select NULL AS `NULL` from `Replacements` `r` where ((`r`.`game_id` = `p`.`game_id`) and (`p`.`user_id` = `r`.`user_id`)))))) union (select `r`.`game_id` AS `game_id`,`r`.`replace_id` AS `replace_id`,`p`.`death_phase` AS `death_phase`,`p`.`death_day` AS `death_day`,`p`.`player_alias` AS `player_alias` from (`Replacements` `r` join `Players` `p`) where ((`p`.`game_id` = `r`.`game_id`) and (`p`.`user_id` = `r`.`user_id`))) order by `game_id`,`user_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `Players_result`
--

/*!50001 DROP TABLE IF EXISTS `Players_result`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`dbuser`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `Players_result` AS select `g`.`id` AS `game_id`,`pa`.`user_id` AS `user_id`,`pa`.`original_id` AS `original_id`,if(((`g`.`winner` = _latin1'') or (`p`.`side` = _latin1'') or isnull(`p`.`side`)),_latin1'Unknown',if((`g`.`winner` = _latin1'Other'),_latin1'Other',if((`g`.`winner` = `p`.`side`),_latin1'Won',_latin1'Lost'))) AS `result` from (((`Games` `g` join `Players_all` `pa`) join `Players` `p`) join `Users` `u`) where ((`g`.`id` = `pa`.`game_id`) and (`pa`.`game_id` = `p`.`game_id`) and (`pa`.`original_id` = `p`.`user_id`) and (`g`.`number` is not null) and (`g`.`number` <> 0) and (`u`.`id` = `pa`.`user_id`)) order by `g`.`id`,`pa`.`user_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `Tally_display_inverted`
--

/*!50001 DROP TABLE IF EXISTS `Tally_display_inverted`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`dbuser`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `Tally_display_inverted` AS select `Tally_votes`.`game_id` AS `game_id`,`Tally_votes`.`day` AS `day`,`Tally_votes`.`voter` AS `voter`,count(0) AS `total`,group_concat(if((`Tally_votes`.`unvoted` = _latin1'Yes'),concat(_latin1'[-]',concat(`Tally_votes`.`votee`,_latin1'(',`Tally_votes`.`vote_count`,_latin1')'),_latin1'[/-]'),concat(`Tally_votes`.`votee`,if((`Tally_votes`.`nightfall` = _latin1'Yes'),_latin1'*',_latin1''),_latin1'(',`Tally_votes`.`vote_count`,_latin1')')) order by `Tally_votes`.`vote_count` ASC separator ', ') AS `votes_bgg`,group_concat(if((`Tally_votes`.`unvoted` = _latin1'Yes'),concat(_latin1'<strike>',concat(`Tally_votes`.`votee`,_latin1'(',`Tally_votes`.`vote_count`,_latin1')'),_latin1'</strike>'),concat(`Tally_votes`.`votee`,if((`Tally_votes`.`nightfall` = _latin1'Yes'),_latin1'*',_latin1''),_latin1'(',`Tally_votes`.`vote_count`,_latin1')')) order by `Tally_votes`.`vote_count` ASC separator ', ') AS `votes_html` from `Tally_votes` group by `Tally_votes`.`game_id`,`Tally_votes`.`day`,`Tally_votes`.`voter` order by `Tally_votes`.`game_id`,`Tally_votes`.`day`,`Tally_votes`.`voter` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `Tally_display_lhlv`
--

/*!50001 DROP TABLE IF EXISTS `Tally_display_lhlv`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`dbuser`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `Tally_display_lhlv` AS select `Tally_votes`.`game_id` AS `game_id`,`Tally_votes`.`day` AS `day`,`Tally_votes`.`votee` AS `votee`,sum(if((`Tally_votes`.`unvoted` = _latin1'Yes'),0,1)) AS `total`,group_concat(if((`Tally_votes`.`unvoted` = _latin1'Yes'),concat(_latin1'[-]',concat(`Tally_votes`.`voter`,_latin1'(',`Tally_votes`.`vote_count`,_latin1')'),_latin1'[/-]'),concat(`Tally_votes`.`voter`,if((`Tally_votes`.`nightfall` = _latin1'Yes'),_latin1'*',_latin1''),_latin1'(',`Tally_votes`.`vote_count`,_latin1')')) order by `Tally_votes`.`vote_count` ASC separator ', ') AS `votes_bgg`,group_concat(if((`Tally_votes`.`unvoted` = _latin1'Yes'),concat(_latin1'<strike>',concat(`Tally_votes`.`voter`,_latin1'(',`Tally_votes`.`vote_count`,_latin1')'),_latin1'</strike>'),concat(`Tally_votes`.`voter`,if((`Tally_votes`.`nightfall` = _latin1'Yes'),_latin1'*',_latin1''),_latin1'(',`Tally_votes`.`vote_count`,_latin1')')) order by `Tally_votes`.`vote_count` ASC separator ', ') AS `votes_html` from `Tally_votes` group by `Tally_votes`.`game_id`,`Tally_votes`.`day`,`Tally_votes`.`votee` order by `Tally_votes`.`game_id`,`Tally_votes`.`day`,if((`Tally_votes`.`votee` = _latin1'nightfall'),NULL,1) desc,sum(if((`Tally_votes`.`unvoted` = _latin1'Yes'),0,1)) desc,max(if((`Tally_votes`.`unvoted` = _latin1'Yes'),NULL,`Tally_votes`.`vote_count`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `Tally_display_lhv`
--

/*!50001 DROP TABLE IF EXISTS `Tally_display_lhv`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`dbuser`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `Tally_display_lhv` AS select `Tally_votes`.`game_id` AS `game_id`,`Tally_votes`.`day` AS `day`,`Tally_votes`.`votee` AS `votee`,sum(if((`Tally_votes`.`unvoted` = _latin1'Yes'),0,1)) AS `total`,group_concat(if((`Tally_votes`.`unvoted` = _latin1'Yes'),concat('[-]',concat(`Tally_votes`.`voter`,'(',`Tally_votes`.`vote_count`,')'),'[/-]'),concat(`Tally_votes`.`voter`,convert(if((`Tally_votes`.`nightfall` = _latin1'Yes'),_utf8'*',_utf8'') using latin1),'(',`Tally_votes`.`vote_count`,')')) order by `Tally_votes`.`vote_count` ASC separator ', ') AS `votes_bgg`,group_concat(if((`Tally_votes`.`unvoted` = _latin1'Yes'),concat('<strike>',concat(`Tally_votes`.`voter`,'(',`Tally_votes`.`vote_count`,')'),'</strike>'),concat(`Tally_votes`.`voter`,convert(if((`Tally_votes`.`nightfall` = _latin1'Yes'),_utf8'*',_utf8'') using latin1),'(',`Tally_votes`.`vote_count`,')')) order by `Tally_votes`.`vote_count` ASC separator ', ') AS `votes_html` from `Tally_votes` group by `Tally_votes`.`game_id`,`Tally_votes`.`day`,`Tally_votes`.`votee` order by `Tally_votes`.`game_id`,`Tally_votes`.`day`,if((`Tally_votes`.`votee` = _latin1'nightfall'),NULL,1) desc,sum(if((`Tally_votes`.`unvoted` = _latin1'Yes'),0,1)) desc,min(if((`Tally_votes`.`unvoted` = _latin1'Yes'),NULL,`Tally_votes`.`vote_count`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `Tally_votes`
--

/*!50001 DROP TABLE IF EXISTS `Tally_votes`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`dbuser`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `Tally_votes` AS (select `Tally`.`game_id` AS `game_id`,`Tally`.`day` AS `day`,`u1`.`name` AS `voter`,`u2`.`name` AS `votee`,`Tally`.`vote_count` AS `vote_count`,if((`Tally`.`unvote` = 0),_latin1'No',_latin1'Yes') AS `unvoted`,if((`Tally`.`nightfall` = 0),_latin1'No',_latin1'Yes') AS `nightfall`,`Tally`.`vote_article` AS `vote_article`,`Tally`.`unvote_article` AS `unvote_article`,`Tally`.`nightfall_article` AS `nightfall_article` from ((`Tally` join `Users` `u1`) join `Users` `u2`) where ((`Tally`.`voter` = `u1`.`id`) and (`Tally`.`votee` = `u2`.`id`))) union (select `Tally`.`game_id` AS `game_id`,`Tally`.`day` AS `day`,`u1`.`name` AS `voter`,`Tally`.`misc` AS `votee`,`Tally`.`vote_count` AS `vote_count`,if((`Tally`.`unvote` = 0),_latin1'No',_latin1'Yes') AS `unvoted`,if((`Tally`.`nightfall` = 0),_latin1'No',_latin1'Yes') AS `nightfall`,`Tally`.`vote_article` AS `vote_article`,`Tally`.`unvote_article` AS `unvote_article`,`Tally`.`nightfall_article` AS `nightfall_article` from (`Tally` join `Users` `u1`) where ((`Tally`.`voter` = `u1`.`id`) and (`Tally`.`votee` = 0))) order by `game_id`,`day`,`vote_count` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `Users_game_all`
--

/*!50001 DROP TABLE IF EXISTS `Users_game_all`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`dbuser`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `Users_game_all` AS select `Players`.`game_id` AS `game_id`,`Players`.`user_id` AS `user_id`,_utf8'player' AS `type` from `Players` union select `Moderators`.`game_id` AS `game_id`,`Moderators`.`user_id` AS `user_id`,_utf8'moderator' AS `type` from `Moderators` union select `Replacements`.`game_id` AS `game_id`,`Replacements`.`replace_id` AS `user_id`,_utf8'replacement' AS `type` from `Replacements` order by `game_id`,`user_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `Users_game_ranks`
--

/*!50001 DROP TABLE IF EXISTS `Users_game_ranks`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`dbuser`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `Users_game_ranks` AS select `a`.`user_id` AS `user_id`,`a`.`name` AS `name`,`a`.`games_played` AS `games_played`,count(`b`.`games_played`) AS `rank` from (`Users_game_totals` `a` join `Users_game_totals` `b`) where ((`a`.`games_played` < `b`.`games_played`) or ((`a`.`games_played` = `b`.`games_played`) and (`a`.`user_id` = `b`.`user_id`))) group by `a`.`user_id`,`a`.`name`,`a`.`games_played` order by `a`.`games_played` desc,`a`.`name` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `Users_game_totals`
--

/*!50001 DROP TABLE IF EXISTS `Users_game_totals`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`dbuser`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `Users_game_totals` AS select `Players_all`.`user_id` AS `user_id`,`Users`.`name` AS `name`,count(0) AS `games_played` from ((`Users` join `Players_all`) join `Games`) where ((`Games`.`number` <> 0) and (`Users`.`id` = `Players_all`.`user_id`) and (`Players_all`.`game_id` = `Games`.`id`) and (`Games`.`status` in (_latin1'In Progress',_latin1'Finished'))) group by `Users`.`name` order by count(0) desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `Users_modded_ranks`
--

/*!50001 DROP TABLE IF EXISTS `Users_modded_ranks`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`dbuser`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `Users_modded_ranks` AS select `a`.`user_id` AS `user_id`,`a`.`name` AS `name`,`a`.`games_moderated` AS `games_moderated`,count(`b`.`games_moderated`) AS `rank` from (`Users_modded_totals` `a` join `Users_modded_totals` `b`) where ((`a`.`games_moderated` < `b`.`games_moderated`) or ((`a`.`games_moderated` = `b`.`games_moderated`) and (`a`.`user_id` = `b`.`user_id`))) group by `a`.`user_id`,`a`.`name`,`a`.`games_moderated` order by `a`.`games_moderated` desc,`a`.`name` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `Users_modded_totals`
--

/*!50001 DROP TABLE IF EXISTS `Users_modded_totals`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`dbuser`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `Users_modded_totals` AS select `Moderators`.`user_id` AS `user_id`,`Users`.`name` AS `name`,count(0) AS `games_moderated` from ((`Users` join `Moderators`) join `Games`) where ((`Moderators`.`user_id` <> 306) and (`Games`.`number` <> 0) and (`Users`.`id` = `Moderators`.`user_id`) and (`Moderators`.`game_id` = `Games`.`id`) and (`Games`.`status` in (_latin1'In Progress',_latin1'Finished'))) group by `Users`.`name` order by count(0) desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `Users_result_count`
--

/*!50001 DROP TABLE IF EXISTS `Users_result_count`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`dbuser`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `Users_result_count` AS (select `Players_result`.`user_id` AS `user_id`,`Players_result`.`result` AS `result`,count(0) AS `count` from `Players_result` group by `Players_result`.`user_id`,`Players_result`.`result`) union (select `Players_result`.`user_id` AS `user_id`,_utf8'Total' AS `result`,count(0) AS `count` from `Players_result` where ((`Players_result`.`result` = _latin1'Won') or (`Players_result`.`result` = _latin1'Lost')) group by `Players_result`.`user_id`) order by `user_id`,(`result` = _utf8'Unknown'),(`result` = _utf8'Other'),(`result` = _utf8'Lost'),(`result` = _utf8'Won') */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `Users_result_side_count`
--

/*!50001 DROP TABLE IF EXISTS `Users_result_side_count`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`dbuser`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `Users_result_side_count` AS (select `Players_result`.`user_id` AS `user_id`,`Players_result`.`result` AS `result`,`Players`.`side` AS `side`,count(0) AS `count` from (`Players_result` join `Players`) where ((`Players_result`.`original_id` = `Players`.`user_id`) and (`Players_result`.`game_id` = `Players`.`game_id`) and (`Players_result`.`result` <> _latin1'Unknown')) group by `Players_result`.`result`,`Players`.`side`,`Players_result`.`user_id`) union (select `Players_result`.`user_id` AS `user_id`,_latin1'Total' AS `result`,`Players`.`side` AS `side`,count(0) AS `count( * )` from (`Players_result` join `Players`) where ((`Players_result`.`original_id` = `Players`.`user_id`) and (`Players_result`.`game_id` = `Players`.`game_id`) and (`Players_result`.`result` <> _latin1'Unknown') and ((`Players_result`.`result` = _latin1'Won') or (`Players_result`.`result` = _latin1'Lost'))) group by `Players`.`side`,`Players_result`.`user_id`) order by `user_id`,`side`,(`result` = _latin1'Unknown'),(`result` = _latin1'Other'),(`result` = _latin1'Lost'),(`result` = _latin1'Won') */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `Users_series_result`
--

/*!50001 DROP TABLE IF EXISTS `Users_series_result`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`dbuser`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `Users_series_result` AS select `g`.`series_id` AS `series_id`,`p`.`user_id` AS `user_id`,`po`.`side` AS `side`,`g`.`winner` AS `winner` from ((`Players_all` `p` join `Players` `po`) join `Games` `g`) where ((`g`.`series_id` is not null) and (`g`.`status` = _latin1'Finished') and (`p`.`game_id` = `g`.`id`) and (`po`.`user_id` = `p`.`original_id`) and (`po`.`game_id` = `p`.`game_id`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `Users_start_month`
--

/*!50001 DROP TABLE IF EXISTS `Users_start_month`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`dbuser`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `Users_start_month` AS select `Users`.`name` AS `name`,date_format(min(`Games`.`start_date`),_utf8'%y-%m') AS `start_month` from ((`Users` join `Players`) join `Games`) where ((`Users`.`id` = `Players`.`user_id`) and (`Games`.`id` = `Players`.`game_id`) and (`Games`.`start_date` <> _utf8'0000-00-00')) group by `Users`.`name` order by date_format(min(`Games`.`start_date`),_utf8'%y-%m') */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `Votes_log`
--

/*!50001 DROP TABLE IF EXISTS `Votes_log`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`dbuser`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `Votes_log` AS select `Votes`.`game_id` AS `game_id`,`Votes`.`article_id` AS `article_id`,`Votes`.`day` AS `day`,`u1`.`name` AS `voter`,`Votes`.`type` AS `type`,`u2`.`name` AS `votee`,`Votes`.`misc` AS `misc`,`Posts`.`time_stamp` AS `time_stamp`,if((`Votes`.`valid` = 1),_utf8'Yes',_utf8'No') AS `valid`,if((`Votes`.`edited` = 1),_utf8'Yes',_utf8'No') AS `edited` from ((`Posts` join `Users` `u1`) join (`Votes` left join `Users` `u2` on((`Votes`.`votee` = `u2`.`id`)))) where ((`Votes`.`voter` = `u1`.`id`) and (`Votes`.`article_id` = `Posts`.`article_id`)) order by `Votes`.`day`,`Votes`.`article_id`,`Votes`.`type` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-07-15 10:34:01
