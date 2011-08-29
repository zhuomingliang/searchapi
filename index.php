<?php

define('ROOT', dirname(__FILE__));

require ROOT . '/config/configure.php';
require ROOT . '/lib/SearchEngine.php';


$se = SearchEngine::getConnection();

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
//$se->commit();
#$filters = array();
#$facets = array('fields' => array('manu'));
#$sorts = array('price' => 'desc');
#print_r($se->search('苹果鱼', $filters, $sorts, $facets));
#print_r($se->setKeyword('苹果鱼')->addFilter('manu',1)->addFacet('cat_level_2')->addFacet('cat_level_1')->getResult());
print_r($se->setQuery('苹果鱼')->addFilterQuery('price', '[* TO 34]')->addFacetQuery('price:[* TO 100]')->getResult());
?>
