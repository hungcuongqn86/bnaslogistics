ALTER TABLE `bnaslogist_com`.`roles`
ADD COLUMN `position` VARCHAR(100) NULL AFTER `updated_at`;

UPDATE `bnaslogist_com`.`roles` SET `position` = 'Administrator' WHERE (`id` = '1');
UPDATE `bnaslogist_com`.`roles` SET `position` = 'Quản lý' WHERE (`id` = '2');
UPDATE `bnaslogist_com`.`roles` SET `position` = 'Chuyên viên' WHERE (`id` = '3');
UPDATE `bnaslogist_com`.`roles` SET `position` = 'Thành viên' WHERE (`id` = '4');
