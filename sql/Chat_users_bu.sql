drop trigger Chat_users_bu;
delimiter $$
create trigger Chat_users_bu
before update on Chat_users
for each row
begin
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
end
$$
delimiter ;
