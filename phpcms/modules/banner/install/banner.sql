SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `zy_banner` (
  `id` smallint(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` smallint(10) unsigned NOT NULL COMMENT '类型1.url 2.商品id 3.分类id 4.无',
  `option` char(255) COMMENT '对应的url或商品id或分类id',
  `img` char(255) COMMENT '图片',
  `sort` smallint(10) unsigned NOT NULL COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='轮播图' AUTO_INCREMENT=1 ;