DROP TABLE IF EXISTS `zy_member_detail`;
-- phpMyAdmin SQL Dump
-- version 4.5.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: 2019-01-17 05:25:20
-- 服务器版本： 5.7.11
-- PHP Version: 5.6.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `phpcmsv9`
--

-- --------------------------------------------------------

--
-- 表的结构 `zy_member_detail`
--

CREATE TABLE IF NOT EXISTS `zy_member_detail` (
  `id` smallint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `userid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `birthday` char(80)  COMMENT '生日',
 PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- 
-- 出入数据
-- 
INSERT INTO `zy_member_detail` (`id`,`userid`, `birthday`) VALUES
(1,1, '2019-03-20');