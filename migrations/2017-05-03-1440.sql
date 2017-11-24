ALTER TABLE `admin_user`
ADD `photo` varchar(256) COLLATE utf8_czech_ci NULL AFTER `email`,
ADD `description` text COLLATE utf8_czech_ci NULL AFTER `photo`;
