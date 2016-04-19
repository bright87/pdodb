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
    _table = ''

    #最后一次执行的SQL信息
    _lastsql = None

    #当前要执行的sql
    _sql = None

    #要查询的字段，插入数据时的数据字段
    _fields = None

    #查询条件 WHERE
    _where = ''

    #limit
    _limit = ''

    #排序规则 ORDER BY
    _order = ''

    #分组 GROUP BY
    _group = ''

    #HAVING
    _having = ''

    #预处理绑定的数值
    _bind_param = None

    #调试模式 True:调试模式; False:非调试模式
    _debug = True

    #新增或需要更新的数据
    _data = None

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

    def set_debug(self, debug):
        """
        设置调试模式
        :param debug: bool True or False
        :return: None
        """
        self._debug = debug
        return

    def table(self, table):
        """
        设置表名
        :param table: string 数据表名
        :return: self
        """
        #调试模式，执行断言。
        if self._debug:
            assert isinstance(table, str) and ''!=table, "表名必须是字符串，且不能为空。"
        self._table = table
        return self

    def fields(self, fields=()):
        """
        设置要查询的字段
        :param fields: list 要查询的字段列表，默认是空该列表，表示要获取所有字段的值。
        :return: self
        """
        #调试模式，执行断言。
        if self._debug:
            assert isinstance(fields, tuple) or isinstance(fields, list), "字段必须是list或者tuple"
            assert 0<len(fields), "字段列表不能为空"
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
        #调试模式，执行断言。
        if self._debug:
            assert isinstance(where, tuple) or isinstance(where, list), "where条件必须是list或者tuple"
            assert 0<len(where), "where条件不能为空"

        #tuple where
        if isinstance(where, tuple):
            self._where = self._buildwhere(where)

        #list where
        if isinstance(where, list):
            condition_list = []
            self._bind_param = []
            for item in where:
                condition_list.append(self._buildwhere(item))
            self._where = ' AND '.join(condition_list)

        self._where = 'WHERE ' + self._where
        return self

    def group(self, group):
        """
        分组规则
        :param group: string
        :return: self
        """
        #调试模式，执行断言。
        if self._debug:
            assert isinstance(group, str) and ''!=group, "group必须是字符串，并且不能为空."

        if '' == group:
            return self

        self._group = 'GROUP BY ' + group
        return self

    def having(self):
        """

        :return:
        """
        pass

    def order(self, order):
        """
        排序规则
        :param order: string 排序规则
        :return: self
        """
        #调试模式，执行断言。
        if self._debug:
            assert isinstance(order, str) and ''!=order, "order必须是字符串，并且不能为空."

        if '' == order:
            return self

        self._order = 'ORDER BY ' + order
        return self

    def limit(self, offset, limit):
        """
        设置sql LIMIT
        :param offset: integer 偏移量
        :param limit: integer 偏移量
        :return: self
        """
        if self._debug:
            assert isinstance(offset, int) and 0 <= offset, 'offset必须是大于等于0的整数'
            assert isinstance(limit, int) and 0 < limit, 'offset必须是大于0的整数'

        self._limit = 'LIMIT ' + str(offset) + ',' + str(limit)
        return self

    def _buildwhere(self, where):
        """
        生成SQL where条件表达式
        :param where: tuple 查询条件元组
        :return: string 拼装好的where表达式
        """
        if self._bind_param is None:
            self._bind_param = []

        condition = ''
        if where[1] is not 'in':
            condition = "`" + where[0] + "`" + where[1] + "%s"

            #预处理绑定数据
            self._bind_param.append(where[2])
        else:
            placeholder = ['%s' for i in where[2]]
            condition = "`" + where[0] + "` " + where[1] + " (" + ",".join( placeholder ) + ")"

            #预处理绑定数据
            prepare_value = map(str, where[2])
            self._bind_param += prepare_value
        return condition


    def insert(self, data):
        """
        添加数据，添加单条
        :param data: dict 新数据
        :return: integer 新数据ID
        """
        validate = isinstance(data, dict) and 0 < len(data)
        if self._debug:
            assert validate, "data必须为dict并且不能为空"
            assert isinstance(self._table, str) and ''!=self._table, '数据表名必须是字符串，并且不能为空'
        if not validate:
            return 0
        if isinstance(data, dict):
            self._fields = data.keys()
            self._bind_param = data.values()
        self._sql = self._buildsql('INSERT')
        self._beforeExecute()
        result = self._execute()
        self._afterExecute()
        return result


    def find(self):
        """
        查找一条数据
        :return: dict
        """
        #调试模式，执行断言。
        if self._debug:
            assert isinstance(self._table, str) and ''!=self._table, '数据表名必须是字符串，并且不能为空'

        self.limit(0, 1)
        self._sql = self._buildsql('SELECT')
        validate_sql = self._sql is not None and '' != self._sql

        #调试模式，执行断言。
        if self._debug:
            assert validate_sql, 'SQL语句不能为空'

        if not validate_sql:
            return {}

        # self._beforeExecute()
        # result = self._query()
        # self._afterExecute()
        # return result[0]

    def select(self):
        """
        查找多条数据
        :return:
        """
        #调试模式，执行断言。
        if self._debug:
            assert isinstance(self._table, str) and ''!=self._table, '数据表名必须是字符串，并且不能为空'

        self._sql = self._buildsql('SELECT')
        validate_sql = self._sql is not None and '' != self._sql

        #调试模式，执行断言。
        if self._debug:
            assert validate_sql, 'SQL语句不能为空'

        if not validate_sql:
            return []

        self._beforeExecute()
        result = self._query()
        self._afterExecute()
        return result


    def _beforeExecute(self):
        """
        执行SQL语句之前要做的准备工作，要执行的操作
        :return: None
        """
        self._lastsql = {}
        self._lastsql['prepare_sql'] = self._sql
        self._lastsql['bind_param']  = self._bind_param
        return

    def _afterExecute(self):
        """
        sql语句执行后要执行的操作
        :return:
        """
        #初始化（重置）sql语句中各关键词的值
        self._initSqlParam()
        return

    def _initSqlParam(self):
        """
        初始化（重置）sql语句中各关键词的值
        :return: None
        """
        #当前要执行的sql
        self._sql = None

        #要查询的字段
        self._fields = None

        #查询条件 WHERE
        self._where = ''

        #limit
        self._limit = ''

        #排序规则 ORDER BY
        self._order = ''

        #分组 GROUP BY
        self._group = ''

        #HAVING
        self._having = ''

        #预处理绑定的数值
        self._bind_param = None
        return

    def _query(self):
        """
        执行sql语句，select查询操作，返回结果集
        :return:
        """
        cursor = self._connecter.cursor(cursorclass=MySQLdb.cursors.DictCursor)
        cursor.execute(self._sql, self._bind_param)
        result = []
        for row in cursor.fetchall():
            result.append(row)
        cursor.close()
        return result

    def _execute(self):
        """
        执行sql语句，insert delete update 等不需要返回结果集的操作
        :return: int 影响行数
        """
        option = self._sql[0:self._sql.index(' ')]
        option = option.upper()

        cursor = self._connecter.cursor()
        if 'INSERT' == option:
            cursor.execute(self._sql, self._bind_param)
            result = cursor.lastrowid
        else:
            result = cursor.execute(self._sql, self._bind_param)
        cursor.close()
        self._connecter.commit()
        return result

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
            placeholder = ['%s' for i in self._bind_param]
            sql = 'INSERT INTO ' + self._table + ' (`' + '`,`'.join(self._fields) + '`) VALUES (' + ','.join(placeholder) + ')'
        elif 'DELETE' == operation_upper:
            pass
        elif 'UPDATE' == operation_upper:
            pass
        elif 'SELECT' == operation_upper:
            if self._fields is None:
                self._fields = '*'
            sql = 'SELECT ' + self._fields + ' FROM ' + self._table + ' ' + self._where + ' ' + self._group + ' ' + self._having + ' ' + self._order + ' ' + self._limit
        else:
            pass
        return sql

    def get_lastsql(self):
        """
        最后执行的SQL
        :return: string
        """
        return self._lastsql

