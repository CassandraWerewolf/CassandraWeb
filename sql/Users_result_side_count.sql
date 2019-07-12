Create or Replace View Users_result_side_count AS
(
SELECT Players_result.user_id, result, side, count( * ) AS count
FROM Players_result, Players
WHERE Players_result.original_id = Players.user_id
AND Players_result.game_id = Players.game_id
AND result != 'Unknown'
GROUP BY result, side, Players_result.user_id
)
UNION (

SELECT Players_result.user_id, 'Total' AS result, side, count( * )
FROM Players_result, Players
WHERE Players_result.original_id = Players.user_id
AND Players_result.game_id = Players.game_id
AND result != 'Unknown'
AND (
result = 'Won'
OR result = 'Lost'
)
GROUP BY side, Players_result.user_id
)
ORDER BY user_id, side, result = 'Unknown', result = 'Other', result = 'Lost', result = 'Won'
