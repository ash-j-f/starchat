--NOTE: This file is executed as individual statements automatically by a PHP script if the
--"DatabaseAutoSetup" directive in config.ini is set to "true", and the database has not yet
--been set up. Individual commands are separated by semicolons. Comments are ignored.

--USERS
create table users(
	user_id serial PRIMARY KEY,
	username text UNIQUE NOT NULL CHECK (username ~ '^[0-9a-zA-Z]+$') CHECK (char_length(username) <= 16),
	email text UNIQUE NOT NULL CHECK (char_length(email) <= 128),
	password_hash text NOT NULL,
	creation_date timestamptz default now(),
	deleted bool default false,
	system_account bool default false,
	avatar_override text,
	game_points int default 0,
	steam text CHECK (char_length(steam) <= 128),
	twitch text CHECK (char_length(twitch) <= 128),
	bio text CHECK (char_length(bio) <= 128),
	playing text CHECK (char_length(playing) <= 128)
);
create index on users (creation_date);
create index on users (deleted);
--Ensure all usernames are unique regardless of capitalisation.
create UNIQUE index ON users (LOWER(username));
--Ensure all emails are unique regardless of capitalisation.
create UNIQUE index ON users (LOWER(email));
--Automatically add a row into user_login_lock table when a user is added.
create or replace function insert_user_login_lock() returns trigger as $$
BEGIN
	insert into user_login_lock (user_id) values(NEW.user_id);
	RETURN NULL;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER insert_user_login_lock_trigger AFTER INSERT ON users FOR EACH ROW EXECUTE PROCEDURE insert_user_login_lock();
--Prevent game points dropping below zero.
create or replace function game_points_check() returns trigger as $$
BEGIN
	--Avoid trigger recursion.
	IF OLD.game_points = NEW.game_points THEN
		RETURN NULL;
	END IF;
	
	IF NEW.game_points < 0 THEN
		update users set game_points = 0 where user_id = NEW.user_id; 
	END IF;
	
	RETURN NULL;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER game_points_check_trigger AFTER INSERT OR UPDATE ON users FOR EACH ROW EXECUTE PROCEDURE game_points_check();

--LOGIN CHECK ROW LOCKS
create table user_login_lock(
	user_id int REFERENCES users PRIMARY KEY
);

--CHANNELS
create table channels(
	channel_id serial PRIMARY KEY,
	channelname text UNIQUE NOT NULL CHECK (char_length(channelname) <= 48),
	channeldesc text CHECK (char_length(channeldesc) <= 128),
	owner_id int REFERENCES users (user_id) NOT NULL,
	public bool default true,
	creation_date timestamptz default now(),
	deleted bool default false
);
create index on channels (creation_date);
create index on channels (deleted);
create index on channels (public);
create index on channels (owner_id);
--Ensure all channel names are unique regardless of capitalisation.
create UNIQUE index ON channels (LOWER(channelname));

--MESSAGES
create table messages(
	message_id serial PRIMARY KEY,
	channel_id int REFERENCES channels NOT NULL,
	user_id int REFERENCES users NOT NULL,
	message text NOT NULL CHECK (char_length(message) <= 1024),
	system_message bool default false,
	recipient_id int REFERENCES users (user_id) default NULL,
	creation_date timestamptz default now(),
	deleted bool default false,
	stream_account text CHECK (char_length(stream_account) <= 128),
	stream_at_time timestamptz default null,
	stream_advertised bool default false
);
create index on messages (channel_id);
create index on messages (user_id);
create index on messages (creation_date);
create index on messages (deleted);
create index on messages (recipient_id);

--MEMBERS
create type member_role as ENUM ('admin', 'mod', 'regular');
create table members(
	member_id serial PRIMARY KEY,
	channel_id int references channels NOT NULL,
	user_id int references users NOT NULL,
	role member_role default 'regular' NOT NULL,
	creation_date timestamptz default now(),
	lastseen timestamptz default '1-1-1970',
	lastseen_typing timestamptz default null,
	UNIQUE (channel_id, user_id) 
);
create index on members (channel_id);
create index on members (user_id);
create index on members (lastseen);
create index on members (lastseen_typing);

--BANS
create table bans(
	ban_id serial PRIMARY KEY,
	channel_id int references channels NOT NULL,
	user_id int references users NOT NULL,
	banned_by_user_id int references users (user_id) NOT NULL,
	creation_date timestamptz default now(),
	UNIQUE (user_id, channel_id)
);
create index on bans (user_id);
create index on bans (channel_id);
create index on bans (creation_date);

--GAMES
create table games(
	game_id serial PRIMARY KEY,
	channel_id int references channels NOT NULL UNIQUE,
	game_data text
);

--INSERTS
insert into users (username, email, password_hash, system_account, avatar_override) values('GameBot', 'gamebot_fakeemail@nowhere.xyz', '0', 't', '/images/gamebot.png');
