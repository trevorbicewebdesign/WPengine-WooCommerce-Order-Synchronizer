<?php
use tad\FunctionMocker\FunctionMocker;

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
class IntegrationTester extends \Codeception\Actor
{
    use _generated\IntegrationTesterActions;

   /**
    * Define custom actions here
    */

    public function functionMock($functionName, $result){
        codecept_debug("Mocking $functionName");
        \tad\FunctionMocker\FunctionMocker::replace( $functionName, $result);
    }
    
    public function createDonation($args){
        $I = $this;
        $faker = Faker\Factory::create();
        
        
        $user = new stdClass;
        $user->first_name      = $faker->firstName;
        $user->last_name       = $faker->lastName;
        $user->user_login      = $faker->colorName.$user->last_name;
        $user->billing_email   = $faker->email;
        $user->billing_city    = $faker->city;
        $user->billing_state   = $faker->state;
        

        $postmeta = [
            "_billing_first_name"=>isset($args["billing_first_name"])?$args["billing_first_name"]:$faker->firstName,
            "_billing_last_name"=>isset($args["billing_last_name"])?$args["billing_last_name"]:$faker->lastName,
            "_billing_city"=>isset($args["billing_city"])?$args["billing_city"]:$faker->city,
            "_billing_state"=>isset($args["billing_state"])?$args["billing_state"]:$faker->state,
            "_billing_country"=>isset($args["billing_country"])?$args["billing_country"]:"US",
            "_billing_email"=>isset($args["billing_email"])?$args["billing_email"]:$faker->email,
            "_customer_note"=>isset($args["billing_email"])?$args["billing_email"]:$faker->email,
            "_order_total"=>isset($args["order_total"])?$args["order_total"]:$faker->randomNumber(2),
            "_campaign_id"=>isset($args["campaign_id"])?$args["campaign_id"]:null
        ];
        
        $wp_posts = [
            "post_author"           => 1,
            "post_date"             => date("Y-m-d H:i:s"),
            "post_date_gmt"         => date("Y-m-d H:i:s"),
            "post_content"          => "",
            "post_title"            => "Order &ndash; June 11, 2020 @ 01:20 AM",
            "post_excerpt"          => isset($args["donate_note"])?$args["donate_note"]:"",
            "post_status"           => "wc-completed",
            "comment_status"        => "closed",
            "ping_status"           => "closed",
            "post_password"         => "wc_order_lnUv6ccOZpxnn",
            "post_name"             => "order-jun-11-2020-0120-am",
            "to_ping"               => "",
            "pinged"                => "",
            "post_modified"         => date("Y-m-d H:i:s"),
            "post_modified_gmt"     => date("Y-m-d H:i:s"),
            "post_content_filtered" => "",
            "post_parent"           => 0,
            "guid"                  => "https://local.donate.burningman.org/?post_type=shop_order&#038;p=32817",
            "menu_order"            => 0,
            "post_type"             => "shop_order",
            "post_mime_type"        => "",
            "comment_count"         => 0,
            "meta_input"            => $postmeta
        ];
            
        $post_id = $I->havePostInDatabase($wp_posts);
        $total = $I->grabFromDatabase( 'wp_postmeta', 'meta_value', [ 'meta_key'=>'_order_total', 'post_id'=>$post_id ] );
        codecept_debug("total=$total");
        return $total;
    }
}
