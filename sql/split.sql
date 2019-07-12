delimiter $$

CREATE FUNCTION `split`(input TEXT,  delimiter VARCHAR(10), trim_type int, col int) RETURNS varchar(255) DETERMINISTIC
BEGIN
  -- Trim types
  -- 0 - Do nothing
  -- 1 - Trim both
  -- 2 - Trim left only
  -- 3 - Trim right only
  DECLARE cur_position INT DEFAULT 1 ;
  DECLARE remainder TEXT;
  DECLARE cur_string VARCHAR(1000);
  DECLARE result_string VARCHAR(1000);
  DECLARE delimiter_length TINYINT UNSIGNED;
  DECLARE loop_count TINYINT UNSIGNED;
  SET loop_count=0;
  SET remainder = input;
  SET delimiter_length = CHAR_LENGTH(delimiter);
  WHILE CHAR_LENGTH(remainder) > 0 AND cur_position > 0 AND loop_count<col DO
    SET cur_position = INSTR(remainder, delimiter);
    IF cur_position = 0 THEN
      SET cur_string = remainder;
    ELSE
      SET cur_string = LEFT(remainder, cur_position - 1);
    END IF;
    SET result_string=cur_string;
    SET remainder = SUBSTRING(remainder, cur_position + delimiter_length);
    SET loop_count=loop_count+1;
  END WHILE;
  CASE trim_type
    WHEN 1 THEN RETURN TRIM(BOTH ' ' FROM result_string);
    WHEN 2 THEN RETURN TRIM(LEADING ' ' FROM result_string);
    WHEN 3 THEN RETURN TRIM(TRAILING ' ' FROM result_string);
    ELSE RETURN result_string;
  END CASE;
  RETURN result_string;
END

$$
delimiter ;
