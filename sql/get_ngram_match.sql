-- drop function get_ngram_match;

delimiter $$

CREATE FUNCTION get_ngram_match(the_name VARCHAR(50), the_id INT, q INT, tol DOUBLE) 
RETURNS INT
DETERMINISTIC
BEGIN
	DECLARE i INT DEFAULT 1;
	DECLARE current_sim DOUBLE DEFAULT 0;
	DECLARE max_sim DOUBLE DEFAULT 0;
	DECLARE found INT DEFAULT 0;
	DECLARE top_user INT DEFAULT 0;
	DECLARE l_user_id INT DEFAULT 0;
	DECLARE done BOOL DEFAULT FALSE;
	DECLARE cur_1 CURSOR FOR SELECT user_id from Players WHERE game_id = the_id;
	DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = TRUE;

	open cur_1;
	my_loop: LOOP
		FETCH cur_1 INTO l_user_id;

		IF done THEN
			CLOSE cur_1;
			LEAVE my_loop;
		END IF;

		SET current_sim = comp_ngrams(the_name, l_user_id, q);

		IF current_sim >= tol THEN
			SET found = found + 1;
			IF current_sim > max_sim THEN
				SET max_sim = current_sim;
				SET top_user = l_user_id;
			END IF;
		END IF;	
	END LOOP;	

	IF found > 1 THEN
		SET top_user = -1;
	END IF;	

	RETURN top_user;
END 
$$
delimiter ;
