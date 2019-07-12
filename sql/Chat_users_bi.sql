drop trigger Chat_users_bi;
delimiter $$
create trigger Chat_users_bi
before insert on Chat_users
for each row
begin
if (new.user_id = 0) then
	set new.color = '#0000CC';
end if;
end
$$
delimiter ;
