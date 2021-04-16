
ALTER TABLE system_configuration add column home_page integer, add column help_page integer;
INSERT INTO system_configuration (admin_email,home_page,help_page) values (null,null,null);
CREATE TABLE pages (id serial primary key, title text, content text);