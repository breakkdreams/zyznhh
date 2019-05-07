-- SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
-- SET time_zone = "+00:00";


-- CREATE TABLE IF NOT EXISTS `zy_community` (
--  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
--   `catid` int(11) NOT NULL COMMENT '分类id',
--   `userid` int(11) NOT NULL COMMENT '用户id',
--   `userhead` varchar(255) NOT NULL COMMENT '用户头像',
--   `username` varchar(150) NOT NULL COMMENT '用户名',
--   `content` varchar(255) NOT NULL COMMENT '内容',
--   `img` varchar(255) DEFAULT NULL COMMENT '图片',
--   `likes` int(11) NOT NULL DEFAULT '0' COMMENT '点赞数',
--   `comment` int(11) NOT NULL DEFAULT '0' COMMENT '评论数',
--   `forward` int(11) NOT NULL DEFAULT '0' COMMENT '转发数',
--   `addtime` int(11) NOT NULL COMMENT '添加时间',
--   `status` int(11) NOT NULL DEFAULT '0' COMMENT '状态(0正常 -1被举报)',
--   `log` varchar(50) DEFAULT NULL COMMENT '经度',
--   `lat` varchar(50) DEFAULT NULL COMMENT '纬度',
--   PRIMARY KEY (`id`)
-- ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='社区' AUTO_INCREMENT=1 ;


-- CREATE TABLE IF NOT EXISTS `zy_likes` (
--   `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
--   `beuserid` int(11) NOT NULL COMMENT '被点赞用户',
--   `userid` int(11) NOT NULL COMMENT '点赞用户',
--   `articleid` int(11) NOT NULL COMMENT '文章id',
--   `addtime` int(20) NOT NULL COMMENT '时间',
--   PRIMARY KEY (`id`)
-- ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='点赞';


-- CREATE TABLE IF NOT EXISTS `zy_zycomment` (
--   `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
--   `articleid` int(11) NOT NULL COMMENT '文章id',
--   `pid` int(11) NOT NULL DEFAULT '0' COMMENT '父级id',
--   `likes` int(11) NOT NULL DEFAULT '0' COMMENT '点赞数',
--   `addtime` int(11) NOT NULL COMMENT '添加时间',
--   `content` varchar(255) NOT NULL COMMENT '内容',
--   `img` varchar(255) NOT NULL COMMENT '图片',
--   `userid` int(11) NOT NULL COMMENT '用户id',
--   `username` varchar(255) NOT NULL COMMENT '用户名',
--   `userhead` varchar(255) NOT NULL COMMENT '用户头像',
--   PRIMARY KEY (`id`)
-- ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='社区评论';

-- CREATE TABLE IF NOT EXISTS `zy_report` (
--   `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
--   `articleid` int(11) NOT NULL COMMENT '文章id',
--   `content` varchar(255) DEFAULT NULL COMMENT '内容',
--   `status` int(11) NOT NULL DEFAULT '0' COMMENT '举报状态(1举报成功 0审核 -1恶意举报)',
--   `score` int(11) NOT NULL DEFAULT '0' COMMENT '积分值',
--   `userid` int(11) NOT NULL COMMENT '用户id',
--   `addtime` int(11) NOT NULL COMMENT '时间',
--   PRIMARY KEY (`id`)
-- ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='举报';

-- CREATE TABLE IF NOT EXISTS `zy_follow` (
--   `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
--   `befollowid` int(11) NOT NULL COMMENT '被关注人',
--   `userid` int(11) NOT NULL COMMENT '关注人',
--   `addtime` int(11) NOT NULL COMMENT '时间',
--   PRIMARY KEY (`id`)
-- ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='关注';