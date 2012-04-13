CREATE TABLE profile_packets (
	guid TEXT PRIMARY KEY NOT NULL,
	sentfrom TEXT NOT NULL,
	sentdate TEXT NOT NULL,
	raw TEXT NOT NULL,
	sentto TEXT NOT NULL
);