drop trigger Games_ad;
delimiter $$
create trigger Games_ad
after delete on Games
for each row
begin
if old.status <> 'Sub-Thread'  and old.status <> 'Unknown' then
	INSERT INTO Update_calendar(action, calendar_id) VALUES('delete', old.calendar_id);
end if;
end
$$
delimiter ;
