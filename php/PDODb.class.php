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
    private $mTableName;
    
    /**
     * 数据表主键
     * @var string 
     */
    private $mPrimaryKey;
    
    /**
     * 预处理sql
     * @var string 
     */
    private $mPrepareSql;
    
    /**
     * 绑定的参数
     * @var array 
     */
    private $mBindParam;
    
    /**
     * sql where条件
     * @var mixt
     */
    private $mWhere;
    
    /**
     * sql limit
     * @var string 
     */
    private $mLimit;
    
    /**
     *
     * @var string
     */
    private $mOrder;
    
    /**
     *
     * @var string
     */
    private $mGroup;  
    
    /**
     *
     * @var string
     */
    private $mHaving;
    
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
            $this->afterExecute();
            return 0;
        }
        
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
            return $tSth->rowCount();
        } else {
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
        $this->mPrepareSql = "DELETE FROM {$this->mTableName} WHERE {$this->mWhere}";
        
        $this->beforeExecute();
        if ( !empty($this->mBindParam) ) {
            
        } else {
            
        }
        $this->beforeExecute();
    }
    
    public function updateByPrimaryKey()
    {
        
    }
    
    public function update()
    {
        
    }
    
    public function find()
    {
        
    }
    
    public function select()
    {
        
    }
    
    /**
     * 设置WHERE条件
     * @param mixt $pWhere
     * @return \PDODb
     */
    public function where( &$pWhere )
    {
        if (is_string($pWhere) ) {
            $this->mWhere = trim($pWhere);
            return $this;
        }
        
        if ( is_array($pWhere) ) {
            $this->mWhere = '';
            foreach ( $pWhere as $key => $value ) {
                if ( is_array($value) ) {
                    $this->mWhere .= "`{$value[0]}`{$value[1]}? AND";
                    $this->mBindParam[] = $value[2];
                } else {
                    $this->mWhere .= "`{$key}`=>'?' AND ";
                    $this->mBindParam[] = $value;
                }
                
            }
            $this->mWhere = rtrim($this->mWhere, ' AND ');
            
            return $this;
        }
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
    private function getFields()
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
                
        return $tFields;
    }
    
//    public function 
}
