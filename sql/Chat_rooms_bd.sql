drop trigger Chat_rooms_bd;
delimiter $$
CREATE TRIGGER `Chat_rooms_bd` BEFORE DELETE ON `Chat_rooms` 
FOR EACH ROW begin
       delete from Chat_users where room_id = old.id;
       delete from Chat_messages where room_id = old.id;
end
$$
delimiter ;

