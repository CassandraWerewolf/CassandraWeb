drop trigger Players_au;
delimiter $$
create trigger Players_au
after update on Players
for each row
begin
declare done bool default false;
declare room int(4);
declare cur cursor for select id from Chat_rooms where game_id = new.game_id;
declare continue handler for sqlstate '02000' set done = true;

if((old.death_phase is null or old.death_phase = "") and new.death_phase is not null) then
	open cur;
    room_loop: loop
    	fetch cur into room;
		if (done) then
			close cur;
			leave room_loop;
		end if;
		UPDATE Chat_users set `lock` = 'Secure' where user_id = new.user_id and room_id = room;
    end loop room_loop;
end if;
end
$$
delimiter ;
