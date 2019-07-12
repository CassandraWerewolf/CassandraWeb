drop function Get_tally //
CREATE FUNCTION Get_tally (gid int(11), nday int(3), tallytype char(5), tallysource char(4))
RETURNS TEXT
DETERMINISTIC
BEGIN
 DECLARE ret text;
 DECLARE strikeBegin char(8);
 DECLARE strikeEnd char(9);
 
 drop temporary table if exists T_Tally;              
 CREATE TEMPORARY TABLE T_Tally
   SELECT * from Tally where game_id=gid and day = nday;
   
 IF tallysource = 'bgg'
 THEN
   SET strikeBegin = '[-]';
   SET strikeEnd = '[/-]';
 ELSE
   SET strikeBegin = '<strike>';
   SET strikeEnd = '</strike>';  
 END IF;
 
 IF tallytype = 'lhv' THEN  
     SELECT CAST( GROUP_CONCAT( '[b]', votee,  '[/b] - ', total,  ' - ', vote_str,  '\n' SEPARATOR  '' ) AS CHAR ) into ret
       FROM (
         SELECT
           1 AS dummy,
           IF(votee, Get_name(game_id, votee), misc) AS votee,
           SUM( IF( unvote, 0, 1 ) ) AS total,        
           GROUP_CONCAT( IF( unvote,
             CONCAT(strikeBegin, CONCAT(Get_name(game_id, voter), '(',vote_count,')'), strikeEnd),
             CONCAT(Get_name(game_id, voter), IF(nightfall,'*',''), '(',vote_count, ')') )
             ORDER BY vote_count
             SEPARATOR  ', ') AS vote_str
         FROM T_Tally
         GROUP BY votee
         ORDER BY total DESC , MIN( IF( unvote, NULL , vote_count ) ) ASC
       ) AS tab
       GROUP BY dummy;  
 ELSEIF tallytype = 'lhlv' THEN  
     SELECT CAST( GROUP_CONCAT( '[b]', votee,  '[/b] - ', total,  ' - ', vote_str,  '\n' SEPARATOR  '' ) AS CHAR ) into ret
       FROM (
         SELECT
           1 AS dummy,
           IF(votee, Get_name(game_id, votee), misc) AS votee,
           SUM( IF( unvote, 0, 1 ) ) AS total,        
           GROUP_CONCAT( IF( unvote,
             CONCAT(strikeBegin, CONCAT(Get_name(game_id, voter), '(',vote_count,')'), strikeEnd),
             CONCAT(Get_name(game_id, voter), IF(nightfall,'*',''), '(',vote_count, ')') )
             ORDER BY vote_count
             SEPARATOR  ', ') AS vote_str
         FROM T_Tally
         GROUP BY votee
         ORDER BY total DESC , MAX( IF( unvote, NULL , vote_count ) ) ASC
       ) AS tab
       GROUP BY dummy;  
 ELSEIF tallytype = 'inv' THEN
     SELECT CAST( GROUP_CONCAT( '[b]', voter,  '[/b] - ', total,  ' - ', vote_str,  '\n' SEPARATOR  '' ) AS CHAR ) into ret
       FROM (
         SELECT
           1 AS dummy,
           Get_name(game_id, voter) AS voter,
           Count(1) AS total,        
           GROUP_CONCAT( IF( unvote,
             CONCAT(strikeBegin, CONCAT(IF(votee, Get_name(game_id, votee), misc), '(',vote_count,')'), strikeEnd),
             CONCAT(IF(votee, Get_name(game_id, votee), misc), IF(nightfall,'*',''), '(',vote_count, ')') )
             ORDER BY vote_count
             SEPARATOR  ', ') AS vote_str
         FROM T_Tally
         GROUP BY voter
         ORDER BY voter ASC
       ) AS tab
       GROUP BY dummy;        
 END IF;
   
 return ret;
END //

