
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


CREATE TABLE `keyboardposts` (
  `id` mediumint(12) NOT NULL,
  `keyboard_id` smallint(5) NOT NULL,
  `post_id` mediumint(9) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `keyboardposts`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `keyboardposts`
  MODIFY `id` mediumint(12) NOT NULL AUTO_INCREMENT;
COMMIT;

