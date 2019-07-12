drop trigger Games_au;
delimiter $$
create trigger Games_au
after update on Games
for each row
begin
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
$$
delimiter ;
