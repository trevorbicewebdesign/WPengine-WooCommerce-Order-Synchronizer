<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

   /**
    * Define custom actions here
    */
    
    public function createDummyAdminUser(){
        $I = $this;
        $adminUser = array(
            'user_login'        => 'admintest', 
            'user_pass'         => '$P$BMsX8jao/FgWuxwdWLcgRRLFmMkuSZ.',
            'user_nicename'     => 'Mister Admin',
            'user_email'        => 'anna.fried@hotmail.com',
            'user_registered'   => '2013-08-25 23:59:59',
            'user_status'       => '0',
        );
        
        $id = $I->haveInDatabase('wp_users', $adminUser);
        
        $testUserMetaRaw = array(
            'admin_color'       => 'fresh',
            'username'          => 'admintest',
            'user_email'        => 'anna.fried@hotmail.com',
            'first_name'        => 'Mister',
            'last_name'         => 'Admin',
            'nickname'          => 'misteradmin',
            'wp_capabilities'   => 'a:1:{s:13:"administrator";b:1;}',
            'wp_user_level'     => '10'
        );
        
        foreach($testUserMetaRaw as $index=>$val){
            $testUserMeta = array(
                'user_id'           => $id, 
                'meta_key'          => $index,
                'meta_value'        => $val
            );
            $I->comment($index." ".$val);
            $I->haveInDatabase('wp_usermeta', $testUserMeta);
        }
        
        return $id;
    }
    public function getNewGuid() {
        mt_srand( ( double )microtime() * 10000 );
        $charid = strtoupper( md5( uniqid( rand(), true ) ) );
        $hyphen = chr( 45 );
        $uuid =
            substr( $charid, 0, 8 ) . $hyphen .
        substr( $charid, 8, 4 ) . $hyphen .
        substr( $charid, 12, 4 ) . $hyphen .
        substr( $charid, 16, 4 ) . $hyphen .
        substr( $charid, 20, 12 );

        return $uuid;
    }
    public function createDummyUser($userData=array()){
        $I = $this;
        
        $default = array(
              "user_login"      => "annaf"
            , "first_name"      => "Anna"
            , "last_name"       => "Fried"
            , "user_nicename"   => "Anna Fried"
            , "user_email"      => "anna.fried@burningman.org"
            , 'bpguid'          => "FFD8D6CC-91B4-DD73-264F-EC63D7C6838B"
        );
        
        $default['name'] = $default['first_name']." ".$default['last_name'];
        
        $testUser = array(
            'user_login'        => isset($userData['user_login'])?$userData['user_login']:$default['user_login'], 
            'user_pass'         => '$P$BMsX8jao/FgWuxwdWLcgRRLFmMkuSZ.',
            'user_nicename'     => isset($userData['user_nicename'])?$userData['user_nicename']:$default['user_nicename'], 
            'user_email'        => isset($userData['user_email'])?$userData['user_email']:$default['user_email'], 
            'user_registered'   => date("Y-m-d H:i:s")
        );
        
        $id = $I->haveInDatabase('wp_users', $testUser);
        
        
        $testUserMetaRaw = array(
              'first_name'      => isset($userData['first_name'])?$userData['first_name']:$default['first_name']
            , 'last_name'       => isset($userData['last_name'])?$userData['last_name']:$default['last_name']
            , 'nickname'        => 'annaf'
            , 'wp_capabilities' => 'a:1:{s:10:"subscriber";b:1;}'
            , 'wp_user_level'   => '0'
            , 'bpguid'          => isset($userData['bpguid'])?$userData['bpguid']:$default['bpguid']
        );
        
        foreach($testUserMetaRaw as $index=>$val){
            $testUserMeta = array(
                'user_id'           => $id, 
                'meta_key'          => $index,
                'meta_value'        => $val
            );
            $I->haveInDatabase('wp_usermeta', $testUserMeta);
        }
        
        return $id;
    }
    
    public function changeOauthClientSettings($settings){
        $I = $this;

        $defaults = array(
              "client_id"               => "psupaon3ocH1qAYznZfEXfihSz92p0eOsIeD7BGv"
            , "client_secret"           => "OowfZvPnv51skkJdUTfjUu8ndm1kYIQM6n15TRto"
            , "server_url"              => "https://oauth.burningman.org"
            , "enable_account_linking"  => "1"
            , "enable_account_creation" => "0"
            , "redirect_to_dashboard"   => "0"
        );
        
        $settings = array(
              "client_id"               => isset($settings['client_id'])?$settings['client_id']:$defaults['client_id']
            , "client_secret"           => isset($settings['client_secret'])?$settings['client_secret']:$defaults['client_secret']
            , "server_url"              => isset($settings['server_url'])?$settings['server_url']:$defaults['server_url']
            , "enable_account_linking"  => isset($settings['enable_account_linking'])?$settings['enable_account_linking']:$defaults['enable_account_linking']
            , "enable_account_creation" => isset($settings['enable_account_creation'])?$settings['enable_account_creation']:$defaults['enable_account_creation']
            , "redirect_to_dashboard"   => isset($settings['redirect_to_dashboard'])?$settings['redirect_to_dashboard']:$defaults['redirect_to_dashboard']
        );
        codecept_debug($settings);

        
        $I->updateInDatabase('wp_options', array('option_value'=>serialize($settings)), array('option_name'=>'wposso_options'));
        
        return;
    }
}
