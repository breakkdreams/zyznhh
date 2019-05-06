SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


CREATE TABLE IF NOT EXISTS `zy_community` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `catid` int(11) NOT NULL COMMENT '分类id',
  `userid` int(11) NOT NULL COMMENT '用户id',
  `userhead` int(255) NOT NULL COMMENT '用户头像',
  `username` int(150) NOT NULL COMMENT '用户名',
  `content` int(255) NOT NULL COMMENT '内容',
  `img` int(255) DEFAULT NULL COMMENT '图片',
  `likes` int(11) NOT NULL DEFAULT '0' COMMENT '点赞数',
  `comment` int(11) NOT NULL DEFAULT '0' COMMENT '评论数',
  `forward` int(11) NOT NULL DEFAULT '0' COMMENT '转发数',
  `addtime` int(11) NOT NULL COMMENT '添加时间',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '状态(0正常 -1被举报)',
  `log` varchar(50) DEFAULT NULL COMMENT '经度',
  `lat` varchar(50) DEFAULT NULL COMMENT '纬度',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='社区' AUTO_INCREMENT=1 ;