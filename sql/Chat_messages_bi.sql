drop trigger Chat_messages_bi;
delimiter $$
create trigger Chat_messages_bi
before insert on Chat_messages
for each row
Begin	
declare result text;
declare mon int(1);

set mon = (select monitor from Chat_rooms where id = new.room_id);

if(mon = 1 and new.user_id != 306) then
	set result = (select regexp_substr(new.message, '\\[.+\\]'));
	if(result is not NULL) then
		insert into Chat_message_actions(message_id, type_id, misc) values(new.id, 0, result);
		set new.message = (select concat('COMMAND ACCEPTED: ', new.message));
		set new.user_id = 306;
	end if;
end if;

end;

$$
delimiter ;
