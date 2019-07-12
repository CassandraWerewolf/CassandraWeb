delimiter $$

CREATE FUNCTION `get_non_voters`(game int, game_day int) RETURNS text
READS SQL DATA
begin
declare nonvoters text;

select group_concat(Get_name(game,p.user_id) order by Get_name(game,p.user_id) separator ', ') into nonvoters from Players_r p where p.game_id = game and (p.death_day is null or p.death_day >= game_day) and p.user_id not in (select voter from Tally t where t.game_id = p.game_id and t.unvote=0 and t.day = game_day);
return nonvoters;
end 
$$
delimiter ;
