-- --------------------------------------------------------
-- Ahost One v24.11.2 Admin Login Fix
-- admins tablosu için primary key/auto_increment garanti edilir.
ALTER TABLE `admins` ADD PRIMARY KEY (`id`);
ALTER TABLE `admins` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
