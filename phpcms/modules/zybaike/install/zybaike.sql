
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";



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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=18 ;


INSERT INTO `zy_baike` (`id`, `userid`, `username`, `userhead`, `time`, `title`, `content`, `image`, `category`, `browse-number`, `fabulous-number`, `report-number`, `forward-number`, `comment-number`, `Collection`, `islock`) VALUES
(14, 0, '', '', '2019-04-29 01:12:09', '测试', ' cs', '', '类', 0, 0, 0, 0, 0, -1, 0),
(15, 5, '哈哈', 'imageurl', '2019-04-29 02:13:18', '测试', ' 测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试长春市长春市传送传送传送传送传送传送出传送传送传送传送传送传送传送传送传送传送传送长春长春市长春市长传送传送传送传送传送传送传送传送出测试测试测试测试测试测试测试测试测试测试测试测试长春市长春市传送传送传送传送传送传送出传送传送传送传送传送菜市场传送传送传送传送传送传送出传送传送传送传送传送传送传送传送传送传送传送传送传送尺寸测试测试测试测试测试测试测试测试测试测试测试测试长春市长春市传送传送传送传送传送传送出传送传送传送传送传送测试测试测试测试测试测试测试测试测试测试测试测试长春市长春市传送传送传送传送传送传送出传送传送传送传送传送测试测试测试测试测试测试测试测试测试测试测试测试长春市长春市传送传送传送传送传送传送出传送传送传送传送传送从传送传送传送传送传送双层双层双层厕所上传送传送传送传送传送传送测试测试测试测试测试测试测试测试测试测试测试测试长春市长春市传送传送传送传送传送传送出传送传送传送传送传送测试测试测试从超市菜场超市超市商场商场商场商场传送传送传传送传送传送传送传送传送传送传送传送传送实测实测实测实测实测实测尺寸陈思成成车身尺寸尺寸实测实测实测传送传送传送出参数上次传传送传送传送传送传送从超市菜市场超市超市菜场传送传送传送传送出传送传送传送传送传送传送出参数传送传送传送传送传送传送传送传送出此次四川四川省四川四川四川超市菜市场菜市场传送传送传送传送传送测试测试测试测试测试测试测试测试测试测试测试测试长春市长春市传送传送传送传送传送传送出传送传送传送传送传送', '', '类', 0, 0, 0, 0, 0, -1, 0),
(12, 0, '', '', '2019-04-29 01:05:18', '文章题目', ' 文章', '', '', 0, 0, 0, 0, 0, -1, 0),
(13, 0, '', '', '2019-04-29 01:08:33', '张召忠', ' 张召忠', 'image', '', 0, 0, 0, 0, 0, -1, 0),
(16, 0, '', '', '2019-04-29 09:36:16', '', '', '', '', 0, 0, 0, 0, 0, -1, 0),
(17, 0, '', '', '2019-04-30 01:03:38', '', '', '', '', 0, 0, 0, 0, 0, -1, 0);

