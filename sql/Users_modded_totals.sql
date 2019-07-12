CREATE OR REPLACE VIEW Users_modded_totals AS
SELECT 
	Moderators.user_id AS user_id,
	Users.name AS name,
	COUNT(0) AS games_moderated 
FROM 
	((Users JOIN Moderators) JOIN Games) WHERE ((Moderators.user_id <> 306) AND (Games.number <> 0) AND (Users.id = Moderators.user_id) AND (Moderators.game_id = Games.id) AND (Games.status IN ('In Progress', 'Finished'))) GROUP BY Users.name ORDER BY COUNT(0) DESC
