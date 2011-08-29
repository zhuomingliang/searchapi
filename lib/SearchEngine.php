<?php
class SearchEngine {
    private $client = null;
    private $query  = null;
    private static $order = array(
        'asc'  => SolrQuery::ORDER_ASC,
        'desc' => SolrQuery::ORDER_DESC
    );

    /**
     * 新建一个 SearchEngine 对象，请不要使用 $se = new SearchEngine() 的方式
     * 建议使用 $se = SearchEngine::getConnection();
     *
     * @param $options
     *   一个关联数组包含：
     *     hostname: 搜索引擎地址，例如：http://www.example.com/ 
     *     login   : 登陆用户名
     *     password: 登陆密码
     *     port    : 端口
     *     path    : 路径，比如 sorl/core0/
     *     wt      : 写类型，xml 或者 json
     */  
    function __construct($options = null) { 
        if($options === null) {
            $options = array(
                'hostname' => SOLR_SERVER_HOSTNAME,
                'login'    => SOLR_SERVER_USERNAME,
                'password' => SOLR_SERVER_PASSWORD,
                'port'     => SOLR_SERVER_PORT,
                'path'     => SOLR_PATH_TO_SOLR,
                'wt'       => SOLR_WRITER_TYPE,
            );
        }

        $this->client = new SolrClient($options);
        $this->query  = new SolrQuery();
    }

    /**
     * 更新搜索引擎的数据
     *
     * @param $data
     *   一个关联数组包含：
     *     id             : 商品 id
     *     cat_level_1    : 一级分类 id
     *     cat_level_2    : 二级分类 id
     *     cat_level_3    : 三级分类 id
     *     cat_level_{$n} : n 级分类 id，n 最大为 99
     *     title          : 商品 id
     *     manu           : 供应商/品牌 id
     *     sales          : 销量
     *     price          : 价格
     *     date           : 上架日期
     *
     * @returns 成功返回 true，失败返回 false
     */  
    function update($data) {
        $doc = new SolrInputDocument();

        $doc->addField('id', $data['id']);
        
        for($i = 1; $i < 100; ++$i) {
            if(isset($data["cat_level_{$i}"])) {
                $doc->addField("cat_level_{$i}", (int) $data["cat_level_{$i}"]);
                continue;
            }

            break;
        }
                
        $doc->addField('title',          $data['title']);
        $doc->addField('manu',   (int)   $data['manu']);
        $doc->addField('sales',  (int)   $data['sales']);
        $doc->addField('price',  (float) $data['price']);
        $doc->addField('date',   (float) $data['date']);

        try {
            $this->client->addDocument($doc);
        } catch(SolrClientException $e) {
            return false;
        }
        
        return true;
    }

    /**
     * 删除搜索引擎的数据
     *
     * @param $id
     *    商品 id
     *
     * @returns 成功返回 true，失败返回 false
     */
    function delete($id) {
        try {
            $this->client->deleteById($id);
        } catch(SolrClientException $e) {
            return false;
        }
        
        return true;
    }

    /**
     * 提交数据，使搜索引擎更新索引
     * 调用 update 或者 delete 函数之后需要调用 commit 方法 
     * 多次 update 或者 delete 后只需要一次 commit
     *
     * @returns 成功返回 true，失败返回 false
     */
    function commit() {
        if(SOLR_AUTO_COMMIT) {
            return true;
        }

        try {
            $this->client->commit();
        } catch(SolrClientException $e) {
            return false;
        }
        
        return true;

    }

    /**
     * 搜索商品数据
     *
     * @params $keywords
     *   搜索关键词
     *
     * @params $filters
     *   一个关联数组包含以下一个或多个 key => value：
     *     id             : 商品 id
     *     cat_level_1    : 一级分类 id
     *     cat_level_2    : 二级分类 id
     *     cat_level_3    : 三级分类 id
     *     cat_level_{$n} : n 级分类 id，n 最大为 99
     *     title          : 商品 id
     *     manu           : 供应商/品牌 id
     *     sales          : 销量
     *     price          : 价格
     *     date           : 上架日期
     *
     * @params $sorts
     *   一个关联数组包含以下一个或多个 key => value：
     *     id             : 按商品 id 排序, asc 或 desc
     *     cat_level_1    : 按一级分类 id 排序, asc 或 desc
     *     cat_level_2    : 按二级分类 id 排序, asc 或 desc
     *     cat_level_3    : 按三级分类 id 排序, asc 或 desc
     *     cat_level_{$n} : 按 n 级分类 id 排序, asc 或 desc，n 最大为 99
     *     title          : 按商品 id 排序, asc 或 desc
     *     manu           : 按供应商/品牌 id 排序, asc 或 desc
     *     sales          : 按销量 排序, asc 或 desc
     *     price          : 按价格 排序, asc 或 desc
     *     date           : 按上架日期 排序, asc 或 desc
     *
     * @params $facets
     *   一个关联数字包含一下一个或者多个 key => value:
     *     fields    : 一个包含field name 的数组，可选值为: cat_level_1 ... cat_level_{$n}, manu, sales, price, date
     *     mincount : 设置至少商品数量，低于该数量的不返回
     * 
     * @params $start
     *   从第几个开始返回
     * @params $rows
     *   最多返回多少行
     * @returns 返回数组
     *   array(
     *      'responseHeader' => array(
     *          'status' => 0,
     *          'QTime'  => 0,
     *          'params' => array(
     *              'q'    => escape($keyword),
     *              'sort' => 'price asc'  # 可选
     *              'fq'   => array('cat_level_1:1', manu:2')  # 可选, key 和 value 以冒号分隔
     *              'facet.field' => array('cat_level_1', cat_level_2')  # 可选
     *          )
     *      ),
     *      'response'       => array(
     *          'numFound'  => $count, # 搜索结果数量
     *          'start'     => $start, # 从第几个开始返回
     *          'docs'      => array(  # 如果没有搜索结果, 则为空数组
     *              0 => array(
     *                  'id'               => $id, # 商品 id
     *                  'cat_level_1'      => $cat_level_1, # 一级分类 id
     *                  'cat_level_2'      => $cat_level_2, # 二级分类 id
     *                  'cat_level_3'      => $cat_level_3, # 三级分类 id
     *                  'cat_level_{$n}'   => $cat_level_{$n}, # n 级分类 id
     *                  'title'            => $title, # 商品标题
     *                  'manu'             => $manu, # 供应商/品牌
     *                  'price'            => $price, # 价格
     *                  'sales'            => $sale, # 销量
     *                  'date'             => $date, # 上架日期
     *              ),
     *              ...
     *          )
     *      )
     *  )
     */
    /*
    function search($keywords = '', $filters = array(), $sorts = array(), $facets = array(), $start = 0, $rows = SOLR_RESULT_ROWS) {
        $query    = new SolrQuery();
        $keywords = SolrUtils::escapeQueryChars($keywords);
        
        if(empty($keywords)) {
            $keywords = '*';
        }

        $query->setQuery($keywords)->setStart($start)->setRows($rows);

        foreach($filters as $key => $value) {
            $query->addFilterQuery("{$key}:{$value}");
        }

        foreach($sorts as $key => $value) {
            $query->addSortField($key, self::$order[$value]);
        }

        if(!empty($facets['fields'])) {
            $query->setFacet(true);

            foreach($facets['fields'] as $value) {
                $query->addFacetField($value);
            }

            if(!empty($facets['mincount'])) {
                $query->setFacetMinCount($facets['mincount']);
            }
        }

        try {
            $response = $this->client->query($query)->getResponse();
        } catch(SolrClientException $e) {
            return array(
                'responseHeader' => array(
                    'status' => 0,
                    'QTime'  => 0,
                    'params' => array(
                        'q'     => $keywords
                    )
                ),
                'response'      => array(
                    'numFound'  => 0,
                    'start'     => 0,
                    'doc'       => array()
                )
            );
        }

        return $response;
    }
    */

    /**
     * 设置搜索关键词
     *
     * @params $keywords
     *   搜索关键词
     *
     * @returns 当前对象
     */
    public function setKeyword($keywords = '') {
        $keywords = SolrUtils::escapeQueryChars($keywords);
        
        if(empty($keywords)) {
            $keywords = '*';
        }

        $this->query->setQuery($keywords);

        return $this;
    }

    /**
     * 设置从第几个开始返回
     *
     * @params $start
     *   从第几个开始返回
     *
     * @returns 当前对象
     */
    public function setStart($start = 0) {
        $this->query->setStart($start);
        
        return $this;
    }
     
    /**
     * 设置最多返回多少行
     *
     * @params $rows
     *   最多返回多少行
     *
     * @returns 当前对象
     */
    public function setRows($rows = SOLR_RESULT_ROWS) {
        $this->query->setRows($rows);

        return $this;
    }

    /**
     * 设置过滤查询
     *
     * @params $field, $value
     *   id             : 商品 id
     *   cat_level_1    : 一级分类 id
     *   cat_level_2    : 二级分类 id
     *   cat_level_3    : 三级分类 id
     *   cat_level_{$n} : n 级分类 id，n 最大为 99
     *   title          : 商品 id
     *   manu           : 供应商/品牌 id
     *   sales          : 销量
     *   price          : 价格
     *   date           : 上架日期
     *
     * @returns 当前对象
     */
    public function addFilter($field, $value) {
        $this->query->addFilterQuery("{$field}:{$value}");

        return $this;
    }

    /**
     * 添加排序
     *
     * @params $field, $order
     *   id             : 按商品 id 排序, asc 或 desc
     *   cat_level_1    : 按一级分类 id 排序, asc 或 desc
     *   cat_level_2    : 按二级分类 id 排序, asc 或 desc
     *   cat_level_3    : 按三级分类 id 排序, asc 或 desc
     *   cat_level_{$n} : 按 n 级分类 id 排序, asc 或 desc，n 最大为 99
     *   title          : 按商品 id 排序, asc 或 desc
     *   manu           : 按供应商/品牌 id 排序, asc 或 desc
     *   sales          : 按销量 排序, asc 或 desc
     *   price          : 按价格 排序, asc 或 desc
     *   date           : 按上架日期 排序, asc 或 desc
     *
     * @returns 当前对象
     */
    public function addSort($field, $order = 'desc') {
        $this->query->addSortField($field, self::$order[$order]);

        return $this;
    }

    /**
     * 设置分组查询
     *
     * @params $field
     *     可选值为: cat_level_1 ... cat_level_{$n}, manu, sales, price, date
     * 
     * @returns 当前对象
     */
    public function addFacet($field) {
        static $is_not_set_facet = true;
        
        if($is_not_set_facet) {
            $this->query->setFacet(true);

            $is_not_set_facet = false;
        }

        $this->query->addFacetField($field);

        return $this;
    }
   
    /**
     * 设置数量过滤，低于该数量的结果不返回
     *
     * @params $mincount
     *   至少商品数量
     *
     * @returns 当前对象
     */
    public function addFacetMinCount($mincount) {
        $this->query->setFacetMinCount($mincount);

        return $this;
    }

    /*
     *
     * 获取结果
     *
     * @returns 返回数组
     *   array(
     *      'responseHeader' => array(
     *          'status' => 0,
     *          'QTime'  => 0,
     *          'params' => array(
     *              'q'    => escape($keyword),
     *              'sort' => 'price asc'  # 可选
     *              'fq'   => array('cat_level_1:1', manu:2')  # 可选, key 和 value 以冒号分隔
     *              'facet.field' => array('cat_level_1', cat_level_2')  # 可选
     *          )
     *      ),
     *      'response'       => array(
     *          'numFound'  => $count, # 搜索结果数量
     *          'start'     => $start, # 从第几个开始返回
     *          'docs'      => array(  # 如果没有搜索结果, 则为空数组
     *              0 => array(
     *                  'id'               => $id, # 商品 id
     *                  'cat_level_1'      => $cat_level_1, # 一级分类 id
     *                  'cat_level_2'      => $cat_level_2, # 二级分类 id
     *                  'cat_level_3'      => $cat_level_3, # 三级分类 id
     *                  'cat_level_{$n}'   => $cat_level_{$n}, # n 级分类 id
     *                  'title'            => $title, # 商品标题
     *                  'manu'             => $manu, # 供应商/品牌
     *                  'price'            => $price, # 价格
     *                  'sales'            => $sale, # 销量
     *                  'date'             => $date, # 上架日期
     *              ),
     *              ...
     *          )
     *      )
     *  )
     */
    public function getResult() {
        try {
            $response = $this->client->query($this->query)->getResponse();
        } catch(SolrClientException $e) {
            return array(
                'responseHeader' => array(
                    'status' => 0,
                    'QTime'  => 0,
                    'params' => array(
                        'q'     => $keywords
                    )
                ),
                'response'      => array(
                    'numFound'  => 0,
                    'start'     => 0,
                    'doc'       => array()
                )
            );
        }

        return $response;
    }

    /**
     * 获取到搜索引擎链接。
     * @param $options
     *   一个关联数组包含：
     *     hostname: 搜索引擎地址，例如：http://www.example.com/ 
     *     login   : 登陆用户名
     *     password: 登陆密码
     *     port    : 端口
     *     path    : 路径，比如 sorl/core0/
     *     wt      : 写类型，xml 或者 json
     */  
    public static function getConnection($options = null) {
        static $se_connection = null;

        if($se_connection !== null){
            return $se_connection;
        }

        return $se_connection = new SearchEngine($options);
    }
}
?>
