drop trigger Votes_bi;
delimiter $$
create trigger Votes_bi
before insert on Votes
for each row
vote_check:begin
declare tally_id int(11);
declare tally_nightfall int(11);
declare new_count int(11);
declare new_tally int(1);
	
if new.valid != 1 then
	leave vote_check;
end if;

set new_tally := 0;

set new_count := (select max(vote_count) from Tally where game_id = new.game_id and day = new.day);
if(new_count is NULL) then
	set new_count := 1;
else
	set new_count := new_count + 1;
end if;

if(new.type = 'vote') then
	select id, nightfall INTO tally_id, tally_nightfall from Tally where Tally.game_id=new.game_id and Tally.day=new.day and Tally.voter=new.voter and Tally.unvote=0;
	if(tally_id is null) then
		insert into Tally(game_id, day, votee, voter, vote_article, vote_count,misc) Values(new.game_id, new.day, new.votee, new.voter,new.article_id, new_count,new.misc);
		set new_tally := 1;
	elseif(new.misc = 'nightfall') then
		update Tally set nightfall=1, nightfall_article=new.article_id where id=tally_id;
		set new_tally = 1;
	elseif(tally_nightfall = 0) then
		update Tally set Tally.unvote=1, Tally.unvote_article=new.article_id where Tally.game_id=new.game_id and Tally.day=new.day and Tally.voter=new.voter and Tally.unvote=0 and Tally.nightfall=0;
		insert into Tally(game_id, day, votee, voter, vote_article, vote_count,misc) Values(new.game_id, new.day, new.votee, new.voter,new.article_id, new_count,new.misc);
		set new_tally := 1;
	end if;
elseif(new.type = 'unvote') then
		update Tally set Tally.unvote=1, Tally.unvote_article=new.article_id where Tally.game_id=new.game_id and Tally.day=new.day and Tally.voter=new.voter and Tally.unvote=0 and Tally.nightfall=0;
		set new_tally = 1;
end if;

if(new_tally = 1) then
	update Games set updated_tally = 1 where Games.id = new.game_id;
end if;	

end;
$$
delimiter ;
