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
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`dbuser`@`%`*/ /*!50003 TRIGGER `Chat_rooms_bu` BEFORE UPDATE ON `Chat_rooms` FOR EACH ROW begin
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
end */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`dbuser`@`%`*/ /*!50003 TRIGGER `Chat_rooms_bd` BEFORE DELETE ON `Chat_rooms` FOR EACH ROW begin
       delete from Chat_users where room_id = old.id;
       delete from Chat_messages where room_id = old.id;
end */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`dbuser`@`%`*/ /*!50003 TRIGGER `Chat_users_bi` BEFORE INSERT ON `Chat_users` FOR EACH ROW begin
if (new.user_id = 0) then
	set new.color = '#0000CC';
end if;
end */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`dbuser`@`%`*/ /*!50003 TRIGGER `Chat_users_bu` BEFORE UPDATE ON `Chat_users` FOR EACH ROW begin
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
end */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`dbuser`@`%`*/ /*!50003 TRIGGER `Games_ai` AFTER INSERT ON `Games` FOR EACH ROW begin
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
end */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`dbuser`@`%`*/ /*!50003 TRIGGER `Games_bu` BEFORE UPDATE ON `Games` FOR EACH ROW begin
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
end */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`dbuser`@`%`*/ /*!50003 TRIGGER `Games_au` AFTER UPDATE ON `Games` FOR EACH ROW begin
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
end */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`dbuser`@`%`*/ /*!50003 TRIGGER `Games_bd` BEFORE DELETE ON `Games` FOR EACH ROW begin
       delete from Moderators where game_id = old.id;
       delete from Players where game_id = old.id;
       delete from Replacements where game_id = old.id;
       update Post_collect_slots set game_id = NULL, last_dumped = NULL where game_id = old.id;
       delete from Posts where game_id = old.id;
       delete from Votes where game_id = old.id;
       delete from Tally where game_id = old.id;
       delete from Chat_rooms where game_id = old.id;
end */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`dbuser`@`%`*/ /*!50003 TRIGGER `Games_ad` AFTER DELETE ON `Games` FOR EACH ROW begin
if old.status <> 'Sub-Thread'  and old.status <> 'Unknown' then
	INSERT INTO Update_calendar(action, calendar_id) VALUES('delete', old.calendar_id);
end if;
end */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`dbuser`@`%`*/ /*!50003 TRIGGER `Moderators_ai` AFTER INSERT ON `Moderators` FOR EACH ROW Begin	
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
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`dbuser`@`%`*/ /*!50003 TRIGGER `Players_ad` AFTER DELETE ON `Players` FOR EACH ROW Begin	
	DELETE from Replacements WHERE game_id = old.game_id AND user_id = old.user_id;
end */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`dbuser`@`%`*/ /*!50003 TRIGGER `Replacements_ai` AFTER INSERT ON `Replacements` FOR EACH ROW Begin	
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
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`dbuser`@`%`*/ /*!50003 TRIGGER `Votes_bi` BEFORE INSERT ON `Votes` FOR EACH ROW vote_check:begin
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
end */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Dumping routines for database 'werewolf'
--
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
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
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
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
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`dbuser`@`%` FUNCTION `get_non_voters`(game int, game_day int) RETURNS text CHARSET latin1
    READS SQL DATA
begin
declare nonvoters text;

select group_concat(Get_name(game,p.user_id) order by Get_name(game,p.user_id) separator ', ') into nonvoters from Players_r p where p.game_id = game and (p.death_day is null or p.death_day >= game_day) and p.user_id not in (select voter from Tally t where t.game_id = p.game_id and t.unvote=0 and t.day = game_day);
return nonvoters;
end ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`dbuser`@`%` FUNCTION `get_non_voters_count`(game int, game_day int) RETURNS int(11)
    READS SQL DATA
begin
declare nonvoters integer;
select count(*) into nonvoters from Players_r p, Users u where p.game_id = game and (p.death_day is null or p.death_day >= game_day) and p.user_id = u.id and p.user_id not in (select voter from Tally t where t.game_id = p.game_id and t.unvote=0 and t.day = game_day);
return nonvoters;
end ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
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
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
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
      END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
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
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-07-15 10:31:15
