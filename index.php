<?php

define('ROOT', dirname(__FILE__));

require ROOT . '/config/configure.php';
require ROOT . '/lib/SearchEngine.php';


$se = new SearchEngine();

/*
$data = array(
   'id'	=> '969',
   'cat_level_1' => 3,
   'cat_level_2' => 3,
   'cat_level_3' => 5,
   'title' => '亨氏乐维滋果汁泥-苹果香蕉120gx24袋',
   'manu' => '1',
   'price' => '99.00',
   'sales' => '152',
);
$data = array(
   'id'	=> '968',
   'cat_level_1' => 1,
   'cat_level_2' => 4,
   'cat_level_3' => 5,
   'title' => '限时折扣 阿尔帝鲜味海鲜珍品(什锦鱼)200g！',
   'manu' => '2',
   'price' => '33.35',
   'sales' => '122',
);
 */

//$se->update($data);
$se->commit();
?>
