-- This migration creates an inverted index for RO-crate descriptions.
--
-- Prerequisites:
--    1. Install postgresql-contrib (requires sudo): sudo apt-install postgresql-contrib
--    2. Connect as postgres user and run the SQL commands in the migration

CREATE INDEX ro_crate_descr_idx ON ro_crate USING gin (experiment_description gin_trgm_ops);  
