Create or Replace View Players_all AS
select p.game_id AS game_id, p.user_id AS user_id, 'player' AS `type`, p.user_id as original_id from Players p WHERE NOT EXISTS (select null from Replacements where game_id = p.game_id and user_id = p.user_id)
union
select r.game_id AS game_id, r.replace_id AS user_id, 'replacement' AS type, user_id from Replacements r
union
select r.game_id AS game_id, r.user_id AS user_id, 'replaced' AS type, r.user_id as original_id from Replacements r
order by game_id, user_id
