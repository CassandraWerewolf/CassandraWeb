drop trigger Replacements_ai;
delimiter $$
create trigger Replacements_ai
after insert on Replacements
for each row
Begin	
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
$$
delimiter ;
