delimiter $$

CREATE FUNCTION `get_non_voters_count`(game int, game_day int) RETURNS integer
READS SQL DATA
begin
declare nonvoters integer;
select count(*) into nonvoters from Players_r p, Users u where p.game_id = game and (p.death_day is null or p.death_day >= game_day) and p.user_id = u.id and p.user_id not in (select voter from Tally t where t.game_id = p.game_id and t.unvote=0 and t.day = game_day);
return nonvoters;
end 
$$
delimiter ;
