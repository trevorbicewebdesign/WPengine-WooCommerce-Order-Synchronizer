<?php

class bm_woocommerce_ordersyncTest extends \Codeception\TestCase\WPTestCase {
    /**
     * @var \IntegrationTester
     */
    protected $tester;
    protected function _before() {
        
        
        codecept_debug("_before");
        $path = realpath(__DIR__ . '/../..').DIRECTORY_SEPARATOR;
        $require = $path."/bm-woocommerce-ordersync.php";
        require_once( $require );        
    }
    protected function _after() {
        codecept_debug("_after");
    }

    public function test_getPostConflicts(){
       
        $this->tester->havePostInDatabase([
            'post_type'     => 'post',
            'post_title'    => 'Alice in Wonderland',
        ]);
        
        $params = [
           'wpengine'=>'bmordersynch'
        ];
        
        $clientCallback = $this->make("bm_woocommerce_ordersync", $params);

        $post_conflicts = $clientCallback->getPostConflicts();
        $this->tester->assertEquals(1, count($post_conflicts));
    }
    
    public function test_get_overwrite_postmeta(){
       
        $this->tester->havePostInDatabase([
            'post_type'     => 'post',
            'post_title'    => 'Alice in Wonderland',
            'meta_input'     => [
                'test'=>true    
            ]
        ]);
        
        $params = [
           'wpengine'=>'bmordersynch'
        ];
        
        $clientCallback = $this->make("bm_woocommerce_ordersync", $params);

        $post_conflicts = $clientCallback->get_overwrite_postmeta();
        $this->tester->assertEquals(3, count($post_conflicts));
    }
}
