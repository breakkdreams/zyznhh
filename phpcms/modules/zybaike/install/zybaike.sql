-- phpMyAdmin SQL Dump
-- version 4.0.4
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2019 年 04 月 29 日 02:42
-- 服务器版本: 5.6.12-log
-- PHP 版本: 5.4.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- 数据库: `zyrule`
--

-- --------------------------------------------------------

--
-- 表的结构 `zy_baike`
--

CREATE TABLE IF NOT EXISTS `zy_baike` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL COMMENT '用户id',
  `username` varchar(30) NOT NULL COMMENT '用户名称',
  `userhead` varchar(255) NOT NULL COMMENT '用户头像',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '发布时间',
  `title` varchar(255) NOT NULL COMMENT '发布标题',
  `content` text NOT NULL COMMENT '发布内容',
  `image` varchar(255) NOT NULL COMMENT '发布图片',
  `category` varchar(20) NOT NULL COMMENT '文章类别',
  `browse-number` int(11) NOT NULL DEFAULT '0' COMMENT '浏览量',
  `fabulous-number` int(11) NOT NULL DEFAULT '0' COMMENT '点赞量',
  `report-number` int(11) NOT NULL DEFAULT '0' COMMENT '举报量',
  `forward-number` int(11) NOT NULL DEFAULT '0' COMMENT '转发数',
  `comment-number` int(11) NOT NULL DEFAULT '0' COMMENT '评论数',
  `Collection` int(11) DEFAULT '-1' COMMENT '收藏',
  `islock` int(11) DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ID` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16 ;

--
-- 转存表中的数据 `zy_baike`
--
