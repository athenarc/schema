create table trs_endpoints(
id serial not null primary key,
name text,
url text,
push_tools boolean,
get_workflows boolean
);
