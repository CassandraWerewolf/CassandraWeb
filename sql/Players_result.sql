Create or Replace View Players_result AS
SELECT g.id AS game_id, pa.user_id AS user_id, pa.original_id as original_id, 
if(( g.winner = '' OR p.side = '' OR isnull( p.side )), 'Unknown', 
  if( g.winner = 'Other' , 'Other', 
    if( g.winner = p.side, 'Won', 'Lost' ) ) ) AS result
FROM Games g, Players_all pa, Players p
WHERE g.id = pa.game_id
AND pa.game_id = p.game_id
AND pa.original_id = p.user_id
AND number IS NOT NULL
AND number != 0
ORDER BY g.id, pa.user_id
