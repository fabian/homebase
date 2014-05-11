CREATE TABLE `beacons` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL DEFAULT '',
  `major` int(11) NOT NULL,
  `minor` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `added` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `beacon` (`uuid`,`major`,`minor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `beacons_proximities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `beacon` int(11) unsigned NOT NULL,
  `accuracy` float NOT NULL,
  `proximity` varchar(255) NOT NULL DEFAULT '',
  `rssi` int(11) NOT NULL,
  `recorded` datetime NOT NULL,
  `occurred` datetime DEFAULT NULL,
  `occurred_micro` int(11) unsigned NOT NULL,
  `position_x` int(11) DEFAULT NULL,
  `position_y` int(11) DEFAULT NULL,
  `power` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `beacon` (`beacon`),
  CONSTRAINT `beacons_proximities_ibfk_1` FOREIGN KEY (`beacon`) REFERENCES `beacons` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `beacons_states` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `beacon` int(11) unsigned NOT NULL,
  `state` varchar(255) NOT NULL DEFAULT '',
  `recorded` datetime NOT NULL,
  `occurred` datetime DEFAULT NULL,
  `occurred_micro` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `beacon` (`beacon`),
  CONSTRAINT `beacons_states_ibfk_1` FOREIGN KEY (`beacon`) REFERENCES `beacons` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `config` (
  `key` varchar(255) NOT NULL DEFAULT '',
  `value` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `lights` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `number` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `added` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `number` (`number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `lights_actions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `light` int(11) unsigned NOT NULL,
  `on` tinyint(1) NOT NULL,
  `state` varchar(255) NOT NULL,
  `scheduled` datetime NOT NULL,
  `executed` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `light` (`light`),
  KEY `scheduled` (`scheduled`),
  CONSTRAINT `lights_actions_ibfk_1` FOREIGN KEY (`light`) REFERENCES `lights` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `lights_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `light` int(11) unsigned NOT NULL,
  `on` tinyint(1) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `light` (`light`),
  CONSTRAINT `lights_log_ibfk_1` FOREIGN KEY (`light`) REFERENCES `lights` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `beacons_mappings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `beacon` int(11) unsigned NOT NULL,
  `light` int(11) unsigned NOT NULL,
  `user` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `light` (`light`),
  KEY `beacon` (`beacon`),
  CONSTRAINT `beacons_mappings_ibfk_1` FOREIGN KEY (`light`) REFERENCES `lights` (`id`),
  CONSTRAINT `beacons_mappings_ibfk_2` FOREIGN KEY (`beacon`) REFERENCES `beacons` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `oauth_clients` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET latin1 NOT NULL,
  `redirect_uri` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `oauth_tokens` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `client` int(11) unsigned NOT NULL,
  `user` varchar(255) DEFAULT '',
  `token` varchar(255) NOT NULL DEFAULT '',
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `client` (`client`),
  CONSTRAINT `oauth_tokens_ibfk_1` FOREIGN KEY (`client`) REFERENCES `oauth_clients` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
