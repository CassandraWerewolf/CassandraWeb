drop procedure pop_user_ngrams;

delimiter $$

CREATE PROCEDURE pop_user_ngrams(q int) 
BEGIN
	DECLARE str_len INT;
	DECLARE i INT;
	DECLARE new_str VARCHAR(54);
	DECLARE l_name VARCHAR(54);
	DECLARE ngram VARCHAR(5);
	DECLARE l_user_id INT;
	declare l_loop_end INT default 0;
	declare cur_1 cursor for select id, UPPER(name) from Users order by id;
	declare continue handler for sqlstate '02000' set l_loop_end = 1;

	DELETE FROM Users_ngrams WHERE n = q; 
	
	open cur_1;
	repeat
		fetch cur_1 into l_user_id, l_name;

		if not l_loop_end then 
			set str_len = (SELECT CHARACTER_LENGTH(l_name));
			set new_str = (SELECT LPAD( RPAD(l_name,str_len+q-1,'#' ), str_len+(2*(q-1)),'%'));
			set i = 1;
			repeat
				set ngram = (SELECT SUBSTR(new_str, i, q));
				INSERT INTO Users_ngrams(user_id, n, position, gram) VALUES(l_user_id, q, i, ngram);
	 			set i = i + 1;
			until i>=str_len+q end repeat;
		end if;
	until l_loop_end end repeat;

	close cur_1;
END 
$$
delimiter ;
