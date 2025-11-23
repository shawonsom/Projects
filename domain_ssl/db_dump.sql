/* MySQL - Database - Domain_SSL*/
/* *** Shahriar Alam *** */
CREATE DATABASE /*! IF NOT EXISTS*/`Domain_SSL`;

USE `Domain_SSL`;

/*Table structure for table `ssl_list` */

DROP TABLE IF EXISTS `ssl_list`;

CREATE TABLE `ssl_list` (
  `id` int NOT NULL AUTO_INCREMENT,
  `domain_name` varchar(200) DEFAULT NULL,
  `expire_date` date DEFAULT NULL,
  `last_updated` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `mail_to` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb3;

/*Data for the table `ssl_list` */

insert  into `ssl_list`(`id`,`domain_name`,`expire_date`,`last_updated`,`mail_to`) values 
(1,'google.com','2025-12-01','2025-11-23 11:50:54','shawonsom@gmail.com'),
(2,'btcl.com.bd','2026-04-23','2025-11-23 12:17:15','shawonsom@gmail.com;shawonsom@outlook.com');
