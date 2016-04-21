<?php
/**
 * PDO操作数据库
 * 
 * @author 董光明 <dongguangming@17house.com>
 * @date 2016-02-16 11:28:03
 */
class PDODb
{
    /**
     * 上次执行的SQL语句
     * @var mixt
     */
    public $mLastSql;
    
    /**
     * 数据库连接
     * @var obj 
     */
    private $mConnect;
    
    /**
     * 遥操作的数据表名称
     * @var string
     */
    private $mTableName = '';
    
    /**
     * 数据表主键
     * @var string 
     */
    private $mPrimaryKey = '';
    
    /**
     * 预处理sql
     * @var string 
     */
    private $mPrepareSql = '';
    
    /**
     * 绑定的参数
     * @var array 
     */
    private $mBindParam = [];
    
    /**
     * 要查询的字段
     * @var type 
     */
    private $mFields = '*';
    
    /**
     * sql where条件
     * @var mixt
     */
    private $mWhere = '';
    
    /**
     * sql limit
     * @var string 
     */
    private $mLimit = '';
    
    /**
     *
     * @var string
     */
    private $mOrder = '';
    
    /**
     *
     * @var string
     */
    private $mGroup = '';  
    
    /**
     *
     * @var string
     */
    private $mHaving = '';
    
    public function __construct( $pHost, $pDbuser, $pDbpass, $pDbname, $pPort=3306, $pEncode = 'utf8' )
    {
        //连接数据库
        $this->connectDb($pHost, $pDbuser, $pDbpass, $pDbname, $pPort, $pEncode);
    }
    
    /**
     * 设置主键
     * @author 董光明 <dongguangming@17house.com>
     * @date 2016-02-16 15:22
     * @param string $tPrimaryKey 主键
     * @return null
     */
    public function setPrimaryKey($tPrimaryKey)
    {
        $this->mPrimaryKey = $tPrimaryKey;
        return $this;
    }
    
    /**
     * 设置数据表名
     * @author 董光明 <dongguangming@17house.com>
     * @date 2016-02-16 14：13
     * @param string $pTableName
     * @return boolean|\PDODb
     */
    public function table( &$pTableName )
    {
        if ( empty($pTableName) ) {
            return false;
        }
        
        $this->mTableName = $pTableName;
        
        return $this;
    }
    
    /**
     * 插入单条数据
     * @author 董光明 <dongguangming@17house.com>
     * @date 2016-02-16 13:38
     * @param array $pData 要insert的数据
     * @return int 插入成功返回最后插入行的ID或序列值，失败返回0
     */
    public function insert( &$pData )
    {
        if ( empty($pData) || !is_array($pData) || empty($this->mTableName) ) {
            return 0;
        }
        
        //预处理语句
        $tFields = array_keys( $pData );
        $tPlaceHolder = trim( str_repeat('?,', count($tFields)), ',' );
        $this->mPrepareSql = "INSERT INTO `{$this->mTableName}` (`" . implode('`,`', $tFields) . "`) VALUES({$tPlaceHolder})";
        $this->mBindParam  = array_values($pData);
        
        //执行sql
        $this->beforeExecute();
        $tSth = $this->mConnect->prepare( $this->mPrepareSql );
        if ( !$tSth->execute( $this->mBindParam ) ) {
            $tSth->closeCursor();
            $this->afterExecute();
            return 0;
        }
        $tSth->closeCursor();
        $this->afterExecute();
        
        return $this->mConnect->lastInsertId();
        
    }//end insert
    
    /**
     * 批量插入
     * @author 董光明 <dongguangming@17house.com>
     * @date 2016-02-16 14:57
     * @param array $pData 要插入的数据
     * @return int 返回最后插入行的ID或序列值
     */
    public function insertMany( &$pData )
    {
        if ( empty($pData) || !is_array($pData) || empty($this->mTableName) ) {
            return 0;
        }
        
        //预处理SQL
        $tFields = array_keys($pData[0]);
        $tPlaceHolder = trim( str_repeat('?,', count($tFields)), ',' );
        $this->mPrepareSql = "INSERT INTO `{$this->mTableName}` (`" . implode('`,`', $tFields) . "`) VALUES({$tPlaceHolder})";
        
        //生成绑定数据
        $temp = $pData;
        array_walk($temp, function(&$pParam){
            $pParam = array_values($pParam);
        });
        $this->mBindParam  = $temp;

        //执行sql
        $this->beforeExecute();
        $tSth = $this->mConnect->prepare( $this->mPrepareSql );
        foreach ( $pData as $key => $value ) {
            $tBindParam = array_values($value);
            $tSth->execute( $tBindParam );
        }
        $tSth->closeCursor();
        $this->afterExecute();
        
        return $this->mConnect->lastInsertId();
    }//end insertAll
    
    /**
     * 根据主键的值删除数据
     * @author 董光明 <dongguangming@17house.com>
     * @date 2016-02-16 15:49
     * @param int $pPrimaryKeyValue 主键的值
     * @return int
     */
    public function deleteByPrimaryKey( $pPrimaryKeyValue )
    {
        $tPrimaryKeyValue = intval($pPrimaryKeyValue);
        if ( 0 >= $tPrimaryKeyValue ) {
            return 0;
        }
        
        //主键
        $tPrimaryKey = $this->mPrimaryKey;
        if ( empty($tPrimaryKey) ) {
            $tFields = $this->getFields();
            $tPrimaryKey = $tFields['primary_key'];
        }
        
        //预处理sql
        $this->mPrepareSql = "DELETE FROM {$this->mTableName} WHERE {$tPrimaryKey}=?";
        $this->mBindParam  = array($tPrimaryKeyValue);
        
        //执行sql
        $this->beforeExecute();
        $tSth = $this->mConnect->prepare($this->mPrepareSql);
        $tDeleteResult = $tSth->execute($this->mBindParam);
        $this->afterExecute();
        
        if ( $tDeleteResult ) {
            $tDeleteResult = $tSth->rowCount();
            $tSth->closeCursor();
            return $tDeleteResult;
        } else {
            $tSth->closeCursor();
            return 0;
        }
    }
    
    /**
     * 根据预先设定好的where条件删除数据
     * @author 董光明 <dongguangming@17house.com>
     * @date 2016-02-16 16:03
     * @return int 返回删除的行数（受影响行数）
     */
    public function delete()
    {
        $this->mPrepareSql = "DELETE FROM {$this->mTableName} {$this->mWhere}";
        
        $this->beforeExecute();
        if ( !empty($this->mBindParam) ) {
            
        } else {
            
        }
        $this->beforeExecute();
    }
    
    /**
     * 根据主键更新
     * @author 董光明 <dongguangming@17house.com>
     * @date 2016-04-21 16:02
     * @param int $pPrimaryKey 主键
     * @param array $pData 新数据
     * @return null
     */
    public function updateByPrimaryKey( $pPrimaryKey, $pData )
    {
        //TODO
    }
    
    /**
     * 更新数据
     * @author 董光明 <dongguangming@17house.com>
     * @date 2016-04-21 16:33
     * @param type $pData
     * @return int
     */
    public function update( $pData )
    {
        if ( empty($pData) || !is_array($pData) ) {
            return 0;
        }
        
        $tUp = [];
        $tValues = [];
        foreach ( $pData as $key => $value ) {
            $tUp[] = "`{$key}`=?";
            $tValues[] = $value;
        }
        
        $this->mPrepareSql = "UPDATE {$this->mTableName} SET " . implode(',', $tUp) . ' ' . $this->mWhere;
        $this->mBindParam = array_merge($tValues, $this->mBindParam);
        
        $this->beforeExecute();
        $tSth = $this->mConnect->prepare($this->mPrepareSql);
        $tDeleteResult = $tSth->execute($this->mBindParam);
        $this->afterExecute();
        
        if ( $tDeleteResult ) {
            $tAffected = $tSth->rowCount();
            $tSth->closeCursor();
            return $tAffected;
        } else {
            $tSth->closeCursor();
            return 0;
        }
    }
    
    /**
     * 单条数据
     * @author 董光明 <dongguangming@17house.com>
     * @return array
     */
    public function find()
    {
        $this->limit(0, 1);
        $this->mPrepareSql = "SELECT {$this->mFields} FROM {$this->mTableName} {$this->mWhere}"
        . " {$this->mGroup} {$this->mOrder} {$this->mLimit}";
        
        $this->beforeExecute();
        $tSth = $this->mConnect->prepare($this->mPrepareSql);
        $tDeleteResult = $tSth->execute($this->mBindParam);
        $this->afterExecute();
        
        if ( $tDeleteResult ) {
            $tData = array_pop($tSth->fetchAll(PDO::FETCH_ASSOC));
            $tSth->closeCursor();
            return $tData;
        } else { //查询失败
            $tSth->closeCursor();
            return [];
        }
    }
    
    /**
     * 查找多条数据
     * @author 董光明 <dongguangming@17house.com>
     * @return array
     */
    public function select()
    {
        $this->mPrepareSql = "SELECT {$this->mFields} FROM {$this->mTableName} {$this->mWhere}"
        . " {$this->mGroup} {$this->mOrder} {$this->mLimit}";
        
        $this->beforeExecute();
        $tSth = $this->mConnect->prepare($this->mPrepareSql);
        $tDeleteResult = $tSth->execute($this->mBindParam);
        $this->afterExecute();
        
        if ( $tDeleteResult ) {
            $tData = $tSth->fetchAll(PDO::FETCH_ASSOC);
            $tSth->closeCursor();
            return $tData;
        } else { //查询失败
            return [];
        }
    }
    
    /**
     * 设置myslq limit
     * @param type $pOffset
     * @param type $pLimit
     * @return \PDODb
     */
    public function limit( $pOffset, $pLimit )
    {
        $this->mLimit = "LIMIT {$pOffset},{$pLimit}";
        return $this;
    }
    
    /**
     * 设置WHERE条件
     * @param mixt $pWhere
     * @return \PDODb
     */
    public function where( &$pWhere )
    {
        if (is_string($pWhere) ) {
            $this->mWhere = 'WHERE ' . trim($pWhere);
            return $this;
        }
        
        if ( is_array($pWhere) ) {
            $tOption = strtoupper($pWhere[1]);
            switch ($tOption) {
                case '=':
                case '>=':
                case '<=':
                case '!=':
                case '<>':
                case '>':
                case '<':
                    $this->mWhere = "WHERE `{$pWhere[0]}`{$pWhere[1]}?";
                    $this->mBindParam[] = $pWhere[2];
                    break;

                case 'IN':
                    $tPlaceholder = array_fill(0, count($pWhere[2]), '?');
                    $this->mWhere = "WHERE `{$pWhere[0]}` {$pWhere[1]} (" . implode(',', $tPlaceholder) . ")";
                    $this->mBindParam = $pWhere[2];
                    break;
                
                case 'LIKE':
                    //TODO
                    break;
                default:
                    break;
            }
            
            
            return $this;
        }
    }
    
    /**
     * 排序
     * @author 董光明 <dongguangming@17house.com>
     * @date 2016-04-21 16:47
     * @param string $pOrder 排序
     * @return \PDODb
     */
    public function order( $pOrder )
    {
        $tOrder = trim($pOrder);
        if ( empty($tOrder) ) {
            return $this;
        }
        
        $this->mOrder = "ORDER BY {$tOrder}";
        
        return $this;
    }
    
    /**
     * 分组
     * @author 董光明 <dongguangming@17house.com>
     * @date 2016-04-21 16:47
     * @param string $pGroup 分组
     * @return \PDODb
     */
    public function group( $pGroup )
    {
        $tGroup = trim( $pGroup );
        if ( empty($tGroup) ) {
            return $this;
        }
        
        $this->mGroup = "GROUP BY {$tGroup}";
        
        return $this;
    }
    
    /**
     * 连接数据库
     * @param string $pHost 数据库主机地址
     * @param string $pDbuser 数据库账号
     * @param string $pDbpass 数据库密码
     * @param string $pDbname 数据库名称
     * @param int $pPort 数据库端口
     * @param string $pEncode 编码
     * @return null
     */
    private function connectDb( $pHost, $pDbuser, $pDbpass, $pDbname, $pPort=3306, $pEncode = 'utf8' )
    {
        $tDsn = "mysql:host={$pHost};port={$pPort};dbname={$pDbname}";
        $this->mConnect = new PDO($tDsn, $pDbuser, $pDbpass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '.$pEncode));
        return;
    }
    
    public function getLastSql()
    {
        return $this->mLastSql;
    }
    
    /**
     * 执行sql语句之前的处理
     * @return null
     */
    private function beforeExecute()
    {
        //记录sql信息
        $this->mLastSql = array();
        $this->mLastSql['prepare_sql'] = $this->mPrepareSql;
        $this->mLastSql['bind_param']  = $this->mBindParam;
        
        return;
    }
    
    /**
     * 执行sql语句之后处理
     * @return null
     */
    private function afterExecute()
    {
        $this->mWhere = '';
        $this->mOrder = '';
        $this->mGroup = '';
        $this->mHaving = '';
        $this->mLimit = '';
        $this->mPrepareSql = '';
        $this->mBindParam  = array();
        return;
    }
    
    /**
     * 获取数据表字段
     * @author 董光明 <dongguangming@17house.com>
     * @date 2016-02-16 15:38
     * @return array
     */
    public function getFields()
    {
        $tFields = array();
        
        $tSth = $this->mConnect->prepare("DESC {$this->mTableName}");
        $tSth->execute();
        while ($field = $tSth->fetch(PDO::FETCH_ASSOC))
        {
            $tFields['field'][] = $field['Field'];
            if ($field['Key'] == 'PRI')
            {
                $tFields['primary_key'] = $field['Field'];
            }
        }
        $tSth->closeCursor();
        return $tFields;
    }
    
//    public function 
}
