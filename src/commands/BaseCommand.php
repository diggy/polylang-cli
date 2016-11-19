<?php

namespace Polylang_CLI\Commands;

use \Polylang_CLI\Api\Api;
use \Polylang_CLI\Api\Cli;

use \Polylang_CLI\Traits\Utils;
use \Polylang_CLI\Traits\SettingsErrors;

# make sure PLL_Admin_Model is available
if( ! defined( 'PLL_ADMIN' ) )    define( 'PLL_ADMIN',    true );
if( ! defined( 'PLL_SETTINGS' ) ) define( 'PLL_SETTINGS', true );

/**
 * Class BaseCommand
 *
 * @package Polylang_CLI
 */
class BaseCommand extends \WP_CLI_Command
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
        parent::__construct();

        $this->cli = new Cli();

        # check if Polylang plugin is installed
        if ( ! defined( 'POLYLANG_VERSION' ) ) {
            return $this->cli->error( sprintf( 'This WP-CLI command requires the Polylang plugin: %s', 'wp plugin install polylang && wp plugin activate polylang' ) );
        }

        # check Polylang required version
        if ( version_compare( POLYLANG_VERSION, '2.0.7', '<' ) ) {
            return $this->cli->error( sprintf( 'This WP-CLI command requires Polylang version %s or higher: %s', '2.0.7', 'wp plugin update polylang' ) );
        }

        # get Polylang instance
        $this->pll = \PLL();

        # make Polylang API functions available
        $this->api = new Api( PLL_INC . '/api.php' );
    }

}
