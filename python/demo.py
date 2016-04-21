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
# data = {'nickname': 'lily-3', 'email': "lily-3@sina.cn"}
# result = mysqldb.table(table).insert(data)
# print 'insert(),添加单条数据。'
# print 'id: ', result
# print mysqldb.get_lastsql()
# print ''

#insert many
# data = [
#     {'nickname': 'bright', 'email': "bright@sina.cn"},
#     {'nickname': '临', 'email': "lin@sina.cn"},
#     {'nickname': '兵', 'email': "bing@sina.cn"},
#     {'nickname': '斗', 'email': "dou@sina.cn"},
#     {'nickname': '者', 'email': "zhe@sina.cn"},
#     {'nickname': '皆', 'email': "jie@sina.cn"},
#     {'nickname': '阵', 'email': "zhen@sina.cn"},
#     {'nickname': '列', 'email': "lie@sina.cn"},
#     {'nickname': '前', 'email': "qian@sina.cn"},
#     {'nickname': '行', 'email': "xing@sina.cn"}
# ]
# result = mysqldb.table(table).insertmany(data)
# print 'insertmany(),添加多条数据。'
# print 'id: ', result
# print mysqldb.get_lastsql()
# print ''

#delete
# where = [
#     ('id', '>', '2'),
#     ('id', 'in', (3,4,5,6))
# ]
# result = mysqldb.table(table).where(where).delete()
# print 'delete(),删除数据。'
# print 'affected: ', result
# print mysqldb.get_lastsql()
# print ''

#update
where = ('id', '=', '2')
data = {'nickname':'bright-1-new', 'email':'bright-1-new@126.com'}
result = mysqldb.table(table).where(where).update(data)
print 'update(),更新数据。'
print 'affected: ', result
print mysqldb.get_lastsql()
print ''

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
