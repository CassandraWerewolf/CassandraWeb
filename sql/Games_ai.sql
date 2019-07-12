drop trigger Games_ai;
delimiter $$
create trigger Games_ai
after insert on Games
for each row
begin
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
$$
delimiter ;
