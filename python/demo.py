# -*- coding: utf-8 -*-
import mysql

host = "192.168.193.129"
user = "root"
passwd = "root"
db = "ultrax"
port = 3306
charset="utf8"

mysqldb = mysql.MySQL(host, user, passwd, db, port, charset)

table = 'sop_data_business'
# where = ('id', '>', '6')
where = ('id', 'in', (1,2,34))
where = [
    ('id', '>', '6'),
    ('id', 'in', (1,2,34)),
    ('buisiness_id', 'in', (1,2,34))
]

fields = ('bisiness_id', 'is_hidden', 'company_id', 'business_name',)
mysqldb.table(table).where(where).fields(fields).find()
# print mysqldb

