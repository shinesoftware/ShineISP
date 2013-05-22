ALTER TABLE provinces drop foreign key provinces_state_id_states_state_id;
ALTER TABLE provinces DROP INDEX provinces_state_id_idx;
ALTER TABLE `provinces` CHANGE `state_id` `region_id` INT( 11 ) NULL DEFAULT NULL;
ALTER TABLE `provinces` ADD INDEX ( `region_id` );
RENAME TABLE `states` TO `regions` ;
ALTER TABLE `regions` CHANGE `state_id` `region_id` INT( 11 ) NOT NULL AUTO_INCREMENT;
ALTER TABLE `regions` DROP INDEX `states_country_id_idx` , ADD INDEX `region_country_id_idx` ( `country_id` ) ;
ALTER TABLE `provinces` ADD FOREIGN KEY ( `region_id` ) REFERENCES `regions` (`region_id`) ON DELETE CASCADE ON UPDATE RESTRICT ;

