drop trigger Moderators_ai;
delimiter $$
create trigger Moderators_ai
after insert on Moderators
for each row
Begin	
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
$$
delimiter ;
