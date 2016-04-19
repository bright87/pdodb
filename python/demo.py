# -*- coding: utf-8 -*-
import mysql

host = "192.168.193.129"
user = "root"
passwd = "root"
db = "test"
port = 3306
charset="utf8"

mysqldb = mysql.MySQL(host, user, passwd, db, port, charset)
table = 'user'

#设置调试模式
#True 表示调试模式，当有错误时会执行断言，帮助定位问题
mysqldb.set_debug(True)

#insert one
data = {'nickname': 'lily-3', 'email': "lily-3@sina.cn"}
result = mysqldb.table(table).insert(data)
print 'insert(),添加单条数据。'
print 'id: ', result
print mysqldb.get_lastsql()
print ''

#insert many


#find
where = ('id', '>', '2')
fields = ('id', 'nickname')
order = 'id DESC'
result = mysqldb.table(table).where(where).fields(fields).order(order).find()
print 'find()，查找单条数据。'
print result
print mysqldb.get_lastsql()
print ''

#select
where = [
    ('id', '>', '2'),
    ('id', 'in', (3,4,5,6))
]
fields = ('id', 'nickname')
order = 'id DESC'
result = mysqldb.table(table).where(where).fields(fields).order(order).select()
print 'select()，查找多条数据。'
print result
print mysqldb.get_lastsql()
