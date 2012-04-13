CREATE TABLE my_contacts (
	guid TEXT PRIMARY KEY NOT NULL,
	displayname TEXT NOT NULL,
	groupid INTEGER NOT NULL DEFAULT 0,
	rawdata TEXT
);