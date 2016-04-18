# -*- coding: utf-8 -*-
# MySQL数据库操作模块
# 针对MySQL数据库做增删改查等普通操作

"""
轻量级的python操作MySQL的数据库类
2016-04-18 10:28
bright87@163.com
"""

import MySQLdb

class MySQL:
    """
        MySQL数据库操作类
    """

    #数据库连接句柄，连接对象实例。
    _connecter = None

    #数据表名
    _table = None

    #最后一次执行的SQL信息
    _lastsql = None

    #当前要执行的sql
    _sql = None

    #要查询的字段
    _fields = None

    #查询条件 WHERE
    _where = None

    #limit
    _limit = None

    #排序规则 ORDER BY
    _order = None

    #分组 GROUP BY
    _group = None

    #HAVING
    _having = None

    #预处理绑定的数值
    _prepare_value = None

    def __init__(self, host, user, passwd, db, port=3306, charset="utf8"):
        """

        :param host: string 数据库主机名/IP
        :param user: string 数据库账号
        :param passwd: string 数据库密码
        :param db: string 要连接的数据库
        :param port: integer TCP/IP port to connect to
        :param charset: string 数据库字符集
        :return:
        """
        self._connecter = MySQLdb.connect(host=host, user=user, passwd=passwd, db=db, port=port, charset=charset)


    def __del__(self):
        self._connecter.close()

    def table(self, table):
        """
        设置表名
        :param table: string 数据表名
        :return: self
        """
        self._table = table
        return self

    def fields(self, fields=[]):
        """
        设置要查询的字段
        :param fields: list 要查询的字段列表，默认是空该列表，表示要获取所有字段的值。
        :return: self
        """
        if 0 >= len(fields):
            return self
        self._fields = '`' + '`,`'.join(fields) + '`'
        return self

    def where(self, where):
        """
        设置where条件
        :param where: tuple or list 查询条件
            tuple:
                单一查询条件， WHERE 字段 操作符 值
                ('id', '=', 3), ('id', '>=', 3), ('id', '>', 3), ('id', '<=', 3), ('id', '<', 3) ('id', 'in', (1,2,3))
            list:
                多查询条件， WHERE 字段 操作符 值 AND 字段 操作符 值 ...
                [
                    ('id', '=', 3), ('id', '>=', 3), ('id', '>', 3), ('id', '<=', 3), ('id', '<', 3) ('id', 'in', (1,2,3))
                ]

        :return: self
        """
        #tuple where
        if isinstance(where, tuple):
            self._where = self._buildwhere(where)

        #list where
        if isinstance(where, list):
            condition_list = []
            self._prepare_value = []
            for item in where:
                condition_list.append(self._buildwhere(item))

            self._where = ' AND '.join(condition_list)

        self._where = 'WHERE ' + self._where
        # print self._where #TEST
        # print self._prepare_value #TEST
        return self

    def order(self, order):
        """
        排序规则
        :param order: string 排序规则
        :return: self
        """
        return self

    def _buildwhere(self, where):
        """
        生成SQL where条件表达式
        :param where: tuple 查询条件元组
        :return: string 拼装好的where表达式
        """
        if self._prepare_value is None:
            self._prepare_value = []

        condition = ''

        if where[1] is not 'in':
            condition = "`" + where[0] + "`" + where[1] + "'%s'"

            #预处理绑定数据
            self._prepare_value.append(where[2])
        else:
            placeholder = ['%s' for i in range(0, len(where[2]))]
            condition = "`" + where[0] + "` " + where[1] + " ('" + "','".join( placeholder ) + "')"

            #预处理绑定数据
            prepare_value = map(str, where[2])
            self._prepare_value += prepare_value

        return condition

    def find(self):
        """
        查找一条数据
        :return: dict
        """
        self._sql = self._buildsql('SELECT')
        print self._sql

    def select(self):
        """
        查找多条数据
        :return:
        """
        pass

    def _buildsql(self, operation):
        """
        创建sql语句
        :param operation: string insert or delete or update or select
        :return: string 拼装好的sql语句
        """
        if '' == operation:
            return ''

        #操作转为大写
        operation_upper = operation.upper()

        sql = ''
        if 'INSERT' == operation_upper:
            sql = 'INSERT '
        elif 'DELETE' == operation_upper:
            pass
        elif 'UPDATE' == operation_upper:
            pass
        elif 'SELECT' == operation_upper:
            if self._fields is None:
                self._fields = '*'
            sql = 'SELECT ' + self._fields + ' ' + self._where
        else:
            pass

        return sql

