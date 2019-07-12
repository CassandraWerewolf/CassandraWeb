drop procedure get_ngrams;

delimiter $$

CREATE PROCEDURE get_ngrams(str varchar(50), n int) 
BEGIN
	DECLARE str_len INT;
	DECLARE i INT;
	DECLARE new_str VARCHAR(54);

	set str_len = (select character_length(str));

	set new_str = (select lpad(rpad(str,str_len+n-1,'#'),str_len+(2*(n-1)),'%'));

	set i = 1;

	repeat
		select i as pos, substr(new_str, i, n) as ngram;
	 	set i = i + 1;
	until i>=str_len+n end repeat;
END 
$$
delimiter ;
