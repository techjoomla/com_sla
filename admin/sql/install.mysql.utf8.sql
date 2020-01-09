CREATE TABLE IF NOT EXISTS `#__tj_slas` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `title` varchar(255) COLLATE utf8_bin NOT NULL,
  `description` text NOT NULL,
  `params` text NOT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;


CREATE TABLE IF NOT EXISTS `#__tj_sla_services` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sla_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__tj_sla table.',
  `sla_activity_type_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__tj_sla_activity_types table.',
  `title` varchar(255) COLLATE utf8_bin NOT NULL,
  `description` text NOT NULL,
  `params` text NOT NULL,
  `ideal_time` int(11) unsigned NOT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;


CREATE TABLE IF NOT EXISTS `#__tj_sla_activities` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sla_activity_type_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__tj_sla_activity_types table.',
  `sla_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__tj_sla table.',
  `sla_service_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__tj_sla_services table.',
  `cluster_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__tj_clusters table.',
  `todo_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__jlike_todos table.',
  `license_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__tjmultiagency_licences table.',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__tj_sla_cluster_xref` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sla_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__tj_sla table.',
  `cluster_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__tj_clusters table.',
  `license_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__tjmultiagency_licences table.',
  `lead_consultant_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__users table.',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__tj_sla_activity_types` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_bin NOT NULL,
  `description` text NOT NULL,
  `params` text NOT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;
