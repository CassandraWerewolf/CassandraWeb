drop trigger Games_bu;

delimiter $$
create trigger Games_bu
before update on Games
for each row
begin
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
$$
delimiter ;
