CREATE TABLE jupyter_server(
id bigserial primary key,
manifest varchar(100),
image text,
project varchar(100),
server_id varchar(20),
created_at timestamp,
deleted_at timestamp,
created_by text,
deleted_by text,
project_end_date timestamp,
url text,
active boolean default 'f',
expires_on timestamp
);

create index jupyter_server_server_id_idx on jupyter_server(server_id);
create index jupyter_server_project_idx on jupyter_server(project);
create index jupyter_server_expires_idx on jupyter_server(expires_in);
create index jupyter_server_active_idx on jupyter_server(active);
create index jupyter_server_created_by_idx on jupyter_server(created_by);

CREATE TABLE jupyter_images(
id bigserial primary key,
description text,
image text
);

CREATE INDEX jupyter_images_description_idx on jupyter_images(description);