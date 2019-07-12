drop function comp_ngrams;

delimiter $$

CREATE FUNCTION comp_ngrams(the_name VARCHAR(50), the_id INT, q INT, warp DOUBLE) 
RETURNS DOUBLE
DETERMINISTIC
BEGIN
	DECLARE str_len INT;
	DECLARE i INT DEFAULT 1;
	DECLARE m INT DEFAULT 0;
	DECLARE d INT DEFAULT 0;
	DECLARE ngram_check INT DEFAULT 0;
	DECLARE new_str VARCHAR(54);
	DECLARE ngram VARCHAR(5);
	DECLARE ngrams_1 INT DEFAULT 0;
	DECLARE ngrams_2 INT DEFAULT 0;
	DECLARE ngrams_all INT DEFAULT 0;

	SET str_len = (SELECT CHARACTER_LENGTH(the_name));
	SET new_str = (SELECT LPAD(RPAD(UPPER(the_name),str_len+q-1,'#'),str_len+(2*(q-1)),'%'));
	SET ngrams_1 = str_len + q - 1;
	SET ngrams_2 = (SELECT count(*) FROM Users_ngrams u WHERE u.user_id = the_id and u.n = q);

	REPEAT
		SET ngram = (SELECT SUBSTR(new_str, i, q));
		SET ngram_check = (SELECT 1 FROM Users_ngrams u WHERE u.user_id = the_id and u.n = q and u.gram = ngram);

		IF ngram_check = 1 THEN
			SET m = m + 1;
		END IF;

	 	SET i = i + 1;
	UNTIL i>=str_len+q END REPEAT;

	SET ngrams_all = ngrams_1 + ngrams_2;
	SET d = ngrams_all - m;

	SET ngrams_all = ngrams_all * warp * warp;
	SET d = d * warp * warp;

	RETURN 2 * (ngrams_all - d)/ngrams_all;
END 
$$
delimiter ;
