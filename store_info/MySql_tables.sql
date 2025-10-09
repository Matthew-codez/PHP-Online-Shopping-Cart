USE GGG;
CREATE TABLE IF NOT EXISTS `store_info` (
  `storeID` varchar(10),
  `name` varchar(50),
  `description` varchar(200),
  `welcome` TEXT,
  `css_file` varchar(250),
  `email` varchar(100),
  PRIMARY KEY  (`storeID`)
);

LOAD DATA INFILE 'C:/wamp64/www/shopping/data/store_info/store_info.txt'
INTO TABLE store_info;

CREATE TABLE IF NOT EXISTS `inventory` (
  `storeID` varchar(10),
  `productID` varchar(10),
  `name` varchar(100),
  `description` varchar(200),
  `price` FLOAT,
  PRIMARY KEY  (`productID`)
);
LOAD DATA INFILE 'C:/wamp64/www/shopping/data/store_info/inventory.txt'
INTO TABLE inventory;