CREATE TABLE wcf1_user_iplog (
  entryID int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  userID int(10) unsigned NOT NULL,
  ipAddress varchar(39) NOT NULL,
  userAgent varchar(256) NOT NULL,
  timestamp int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE wcf1_user_iplog_multiaccounts (
  entryID int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  userID int(10) unsigned NOT NULL,
  multiaccountID int(10) unsigned NOT NULL,
  ipAddress varchar(39) NOT NULL,
  timestamp int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;