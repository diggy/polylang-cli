<?php

namespace Polylang_CLI\Commands;

use \WP_CLI_Command;

use \Polylang_CLI\Api\Api;

use \Polylang_CLI\Traits\Utils;
use \Polylang_CLI\Traits\SettingsErrors;

# Make sure PLL_Admin_Model is the Polylang global
if( ! defined( 'PLL_ADMIN' ) )    define( 'PLL_ADMIN',    true );
if( ! defined( 'PLL_SETTINGS' ) ) define( 'PLL_SETTINGS', true );

/**
 * Class BaseCommand
 *
 * @package Polylang_CLI
 */
class BaseCommand extends WP_CLI_Command
{
    use Utils;
    use SettingsErrors;

    protected $pll = null;
    protected $api = null;

    protected $taxonomy      = 'language';
    protected $taxonomy_term = 'term_language';

    protected $fields_term = array(
        'term_id',
        'term_taxonomy_id',
        'name',
        'slug',
        'description',
        'parent',
        'count',
        'term_group',
    );

    protected $fields_language = array(
        'term_id',
        'name',
        'slug',
        'term_group',
        //'term_taxonomy_id',
        //'taxonomy',
        //'description',
        //'parent',
        'count',
        //'tl_term_id',
        //'tl_term_taxonomy_id',
        //'tl_count',
        'locale',
        'is_rtl',
        //'flag_url',
        //'flag',
        //'home_url',
        //'search_url',
        //'host' ,
        //'mo_id',
        //'page_on_front',
        //'page_for_posts',
        //'filter',
        'flag_code',
    );

    public function __construct()
    {
        if ( ! defined( 'POLYLANG_VERSION' ) ) {
            return \WP_CLI::error( sprintf( 'This WP-CLI command requires the Polylang plugin: %s', 'wp plugin install polylang && wp plugin activate polylang' ) );
        }

        if ( version_compare( POLYLANG_VERSION, '2.0.7', '<' ) ) {
            return \WP_CLI::error( sprintf( 'This WP-CLI command requires Polylang version %s or higher: %s', '2.0.7', 'wp plugin update polylang' ) );
        }

        parent::__construct();

        # get Polylang instance
        $this->pll = \PLL();

        # make Polylang API functions available
        $this->api = new Api( PLL_INC . '/api.php' );

        // $this->api->this_func( [ 'wreaks' => 'havoc' ] );
        // var_dump( $this->api->pll_default_language( 'locale' ) ); // bad
        // var_dump( $this->api->default_language( 'locale' ) ); // good
    }

}
