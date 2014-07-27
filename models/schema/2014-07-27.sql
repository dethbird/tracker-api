ALTER TABLE  `activity` DROP FOREIGN KEY  `activity_ibfk_1` ;
ALTER TABLE  `goal` DROP FOREIGN KEY  `goal_ibfk_1` ;

UPDATE `activity_type` SET `id` = `id` + 1000 WHERE `id` < 31;
UPDATE `activity` SET `activity_type_id` = `activity_type_id` + 1000 WHERE `activity_type_id` < 31;
UPDATE `goal` SET `activity_type_id` = `activity_type_id` + 1000 WHERE `activity_type_id` < 31;


ALTER TABLE  `activity` ADD FOREIGN KEY (  `activity_type_id` ) REFERENCES  `activity_type` (
`id`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;

ALTER TABLE  `goal` ADD FOREIGN KEY (  `activity_type_id` ) REFERENCES  `activity_type` (
`id`
) ON DELETE RESTRICT ON UPDATE RESTRICT ;