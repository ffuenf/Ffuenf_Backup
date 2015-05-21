-- add table prefix if you have one
DELETE FROM core_config_data WHERE path like 'aoe_backup/%';
DELETE FROM core_resource WHERE code = 'aoe_backup_setup';