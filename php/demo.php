<?php

/**
 * PDODb类测试
 * @author 董光明 <dongguangming@17house.com>
 * @date 2016-02-16 11:27:36
 */

include dirname(__FILE__) . '/PDODb.class.php';

$tHost   = '127.0.0.1';
$tDbuser = 'root';
$tDbpass = 'root';
$tDbname = 'test';

$tPDODb = new PDODb($tHost, $tDbuser, $tDbpass, $tDbname);

//table name
$tTableName = 'user';

//insertOne
//$tData = [
//    'nickname'  => 'bright',
//    'email'     => 'bright@163.com'
//];
//
//$tInsertResult = $tPDODb->table($tTableName)->insertOne($tData);
//dump($tInsertResult);
//dump($tPDODb->mLastSql);

//insertAll
//$tData = [
//    [
//        'nickname'  => 'bright-1',
//        'email'     => 'bright-1@163.com'
//    ],
//    [
//        'nickname'  => 'bright-2',
//        'email'     => 'bright-2@163.com'
//    ]
//];
//$tInsertResult = $tPDODb->table($tTableName)->insertAll($tData);
//dump($tInsertResult);
//dump($tPDODb->mLastSql);

//deleteByPrimaryKey
//$tDeleteResult = $tPDODb->table($tTableName)->deleteByPrimaryKey(1);
//dump($tDeleteResult);

//delete
//$tWhere = array(
//    array('id', '>', 1),
//    'nickname' => 'bright-2',
//);
//$tDeleteResult = $tPDODb->table($tTableName)->where($tWhere)->delete();

////delete
//$tWhere = ['id'=>2, 'email'=>'a'];
//$tDeletResult = $tPDODb->table($tTableName)->where($tWhere)->delete();

//update
//$tWhere = ['id', '=', 2];
//$tData = ['nickname'=>'a', 'email'=>'a@b.com'];
//$tUpdateResult = $tPDODb->table($tTableName)->where($tWhere)->update($tData);
//dump($tUpdateResult);

//find
//$tWhere = ['id', '=', 2];
//$tWhere = ['id', 'in', [2,3,4]];
//$tOrder = 'id ASC';
//$tData = $tPDODb->table($tTableName)->where($tWhere)->order($tOrder)->find();
//dump($tData);
//
////select
//$tWhere = ['id', 'in', [2,3,4]];
//$tOrder = 'id ASC';
//$tGroup = 'id';
//$tData = $tPDODb->table($tTableName)->where($tWhere)->order($tOrder)->group($tGroup)->select();
//dump($tData);
//dump($tPDODb->getLastSql());

//fields
$tData = $tPDODb->table($tTableName)->getFields();
dump($tData);
dump($tPDODb->getLastSql());
/**
 * 打印参数
 * @author 董光明 <dongguangming@17house.com>
 * @date 2016-02-16 13:50
 * @param mixt $pParam
 * @return null
 */
function dump( &$pParam )
{
    echo '<pre>';
    var_dump($pParam);
    echo '</pre>';
    return;
}



