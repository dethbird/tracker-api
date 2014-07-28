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


#-- social media id

ALTER TABLE  `activity` ADD  `social_media_id` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `social_user_id` ,
ADD INDEX (  `social_media_id` );

ALTER TABLE  `activity` ADD  `date_updated` DATETIME NULL DEFAULT NULL AFTER  `date_added`;


ALTER TABLE  `activity` CHANGE  `type`  `type` ENUM(  'normal',  'socialmedia' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'normal';

UPDATE `activity` SET `type` = 'socialmedia' WHERE `type` = "";