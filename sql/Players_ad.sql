delimiter $$
create trigger Players_ad
after delete on Players
for each row
Begin	
	DELETE from Replacements WHERE game_id = old.game_id AND user_id = old.user_id;
end
$$
delimiter ;
