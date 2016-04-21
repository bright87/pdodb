# pdodb
pdo方式操作数据库<br>
分为PHP版和Python版<br>
两个版本功能基本相同，但是提供的公共方法名称不尽相同。<br>
数据表结构<br>
--<br>
-- 表的结构 \`user\`<br>
--<br>
<pre>
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL,
  `nickname` varchar(64) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;
</pre>
