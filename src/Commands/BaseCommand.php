<?php

namespace Polylang_CLI\Commands;

use \Polylang_CLI\Api\Api;
use \Polylang_CLI\Api\Cli;

use \Polylang_CLI\Traits\Properties;
use \Polylang_CLI\Traits\Utils;
use \Polylang_CLI\Traits\SettingsErrors;

# make sure PLL_Admin_Model is available
if( ! defined( 'PLL_ADMIN' ) )    define( 'PLL_ADMIN',    true );
if( ! defined( 'PLL_SETTINGS' ) ) define( 'PLL_SETTINGS', true );

if ( ! class_exists( 'Polylang_CLI\Commands\BaseCommand' ) ) {

/**
 * Class BaseCommand
 *
 * @package Polylang_CLI
 */
class BaseCommand extends \WP_CLI_Command
{
    use Properties, Utils, SettingsErrors;

    public function __construct()
    {
        parent::__construct();

        # invoke WP_CLI wrapper
        $this->cli = new Cli();

        # check if Polylang plugin is installed
        if ( ! defined( 'POLYLANG_VERSION' ) ) {
            return $this->cli->error( sprintf( 'This WP-CLI command requires the Polylang plugin: %s (%s)', 'wp plugin install polylang && wp plugin activate polylang', ABSPATH ) );
        }

        # check Polylang required version
        if ( version_compare( POLYLANG_VERSION, '2.0.9', '<' ) ) {
            return $this->cli->error( sprintf( 'This WP-CLI command requires Polylang version %s or higher: %s', '2.0.9', 'wp plugin update polylang' ) );
        }

        # get Polylang instance (global)
        $this->pll = \PLL();

        # make Polylang API functions available
        $this->api = new Api( PLL_INC . '/api.php' );
    }

}

}
