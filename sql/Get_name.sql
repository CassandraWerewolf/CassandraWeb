drop function Get_name;
delimiter //
CREATE FUNCTION `Get_name`(gid int(11), uid int(6))
    RETURNS char(50)
    DETERMINISTIC
BEGIN
 DECLARE ret char(50);
 IF ((select vote_by_alias from Games where id=gid) = 'Yes') THEN
   select player_alias into ret from Players_r p where p.user_id=uid and p.game_id=gid;
 END IF;
 IF (ret is null) THEN
   select name into ret from Users u where u.id=uid;
 END IF; 
 return ret;
END 
//
delimiter ;
