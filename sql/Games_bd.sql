drop trigger Games_bd;

delimiter $$
CREATE TRIGGER `Games_bd` BEFORE DELETE ON `Games` 
FOR EACH ROW begin
       delete from Moderators where game_id = old.id;
       delete from Players where game_id = old.id;
       delete from Replacements where game_id = old.id;
       update Post_collect_slots set game_id = NULL, last_dumped = NULL where game_id = old.id;
       delete from Posts where game_id = old.id;
       delete from Votes where game_id = old.id;
       delete from Tally where game_id = old.id;
       delete from Chat_rooms where game_id = old.id;
end
$$
delimiter ;
