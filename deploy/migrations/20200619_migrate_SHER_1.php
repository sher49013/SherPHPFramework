<?php


class migrate_SHER_1
{
    public static function migrate()
    {
        // put database commands into this function for upwards migration
        if (!db_table_exists('user')) {
            db_execute("CREATE TABLE `user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varchar(250) NOT NULL,
  `email` varchar(250) NOT NULL,
  `password` varchar(250) NOT NULL,
  `created_at`int(11) NOT NULL DEFAULT '0',
  `modified_at`int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        }
        
        
    }

    public static function revert()
    {
        // put database commands into this function for backwards migration to revert changes made in migrate function
    }
}