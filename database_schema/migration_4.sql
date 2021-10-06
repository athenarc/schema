ALTER TABLE software ADD COLUMN shared boolean default 'f';
ALTER TABLE software ADD COLUMN gpu boolean default 'f';
ALTER TABLE software_upload ADD COLUMN gpu boolean default 'f';