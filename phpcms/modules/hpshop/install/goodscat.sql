-- phpMyAdmin SQL Dump
-- version 4.0.3
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2018 年 05 月 11 日 15:50
-- 服务器版本: 5.5.25
-- PHP 版本: 5.4.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `300c`
--

-- --------------------------------------------------------

--
-- 表的结构 `zy_goodscat`
--

CREATE TABLE IF NOT EXISTS `zy_goodscat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cate_name` varchar(30) NOT NULL COMMENT '商品分类名称',
  `cate_img` varchar(100) NOT NULL COMMENT '栏目图片',
  `isshow` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1:显示 2：隐藏',
  `description` varchar(255) NOT NULL COMMENT '描述',
  `sort` smallint(6) NOT NULL DEFAULT '500' COMMENT '排序',
  `pid` smallint(6) NOT NULL DEFAULT '0' COMMENT '上级ID',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


--
-- 转存表中的数据 `zy_goodscat`
--

-- INSERT INTO `zy_goodscat` (`id`, `cate_name`, `cate_img`, `isshow`, `description`, `sort`, `pid`) VALUES
-- (1, '蛋糕展示柜', '', 1, '', 1, 0),
-- (2, '开放柜系列', '', 1, '', 2, 0),
-- (3, '分割机', '', 1, '', 3, 0),
-- (4, '整形机', '', 1, '', 4, 0),
-- (5, '酥皮机', '', 1, '', 5, 0),
-- (6, '其他烘焙设备', '', 1, '', 6, 0),
-- (7, '两层圆弧型蛋糕展示柜', '', 1, '', 500, 1),
-- (8, '三层圆弧型蛋糕展示柜', '', 1, '', 500, 1),
-- (9, '直圆弧型蛋糕展示柜', '', 1, '', 500, 1),
-- (10, '两层圆弧型前移门蛋糕展示柜', '', 1, '', 500, 1),
-- (11, '三层圆弧型前移门蛋糕展示柜', '', 1, '', 500, 1),
-- (12, '四层圆弧型前移门蛋糕展示柜', '', 1, '', 500, 1),
-- (13, '两层直角蛋糕柜', '', 1, '', 500, 1),
-- (14, '三层直角蛋糕柜', '', 1, '', 500, 1),
-- (15, '四层直角蛋糕柜', '', 1, '', 500, 1),
-- (16, '单层直角精品柜', '', 1, '', 500, 1),
-- (17, 'L型蛋糕展示柜', '', 1, '', 500, 1),
-- (18, '桌上型冷柜', '', 1, '', 500, 1),
-- (19, '桌上型热柜', '', 1, '', 500, 1),
-- (20, '蛋挞柜', '', 1, '', 500, 1),
-- (21, '开放式三明治柜', '', 1, '', 500, 2),
-- (22, '立式开放柜', '', 1, '', 500, 2),
-- (23, '单面开放柜', '', 1, '', 500, 2),
-- (24, '中心开放柜', '', 1, '', 500, 2),
-- (25, '半自动分割滚圆机', '', 1, '', 500, 3),
-- (26, '吐司切片机', '', 1, '', 500, 3),
-- (27, '全自动分割滚圆机', '', 1, '', 500, 3),
-- (28, '吐司整形机', '', 1, '', 500, 4),
-- (29, '法棍整形机', '', 1, '', 500, 4),
-- (30, '桌上型酥皮机', '', 1, '', 500, 5),
-- (31, '落地式酥皮机', '', 1, '', 500, 5),
-- (32, '搅拌机', '', 1, '', 500, 6),
-- (33, '压面机', '', 1, '', 500, 6),
-- (34, '醒发箱', '', 1, '', 500, 6),
-- (35, '冷藏冷冻醒发箱', '', 1, '', 500, 6),
-- (36, '醒发室', '', 1, '', 500, 6),
-- (37, '打蛋机', '', 1, '', 500, 6),
-- (38, '热风炉', '', 1, '', 500, 6),
-- (39, '转炉', '', 1, '', 500, 6),
-- (40, '电烤炉', '', 1, '', 500, 6),
-- (41, '煤气炉', '', 1, '', 500, 6),
-- (42, '电炸炉', '', 1, '', 500, 6),
-- (43, '披萨炉', '', 1, '', 500, 6),
-- (44, '轨道式烤炉', '', 1, '', 500, 6),
-- (45, '冷水机/冰水机', '', 1, '', 500, 6);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
