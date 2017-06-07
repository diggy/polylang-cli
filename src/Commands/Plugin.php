<?php

namespace Polylang_CLI\Commands;

if ( ! class_exists( 'Polylang_CLI\Commands\PluginCommand' ) ) {

/**
 * Manage the Polylang plugin itself.
 *
 * @package Polylang_CLI
 */
class PluginCommand extends BaseCommand {

    /**
     * Uninstall Polylang and optionally remove all data.
     *
     * ## OPTIONS
     *
     * [--force]
     * : Ignores the Polylang `uninstall` setting and force deletes all data.
     *
     * [--skip-delete]
     * : If set, the plugin files will not be deleted. Only the uninstall procedure
     * will be run.
     *
     * ## EXAMPLES
     *
     *     $ wp pll uninstall
     *     $ wp pll uninstall --force
     *     $ wp pll uninstall --force --skip-delete
     */
    public function uninstall( $args, $assoc_args ) {

        $force = $this->cli->flag( $assoc_args, 'force' );

        if ( empty( $option['uninstall'] ) && empty( $force ) ) {
            $this->cli->error( 'The Polylang plugin could not be uninstalled due to the plugin\'s settings. Use --force to override.' );
        }

        add_filter( 'pre_option_polylang', function( $option ){
            $option['uninstall'] = 1;
            return $option;
        } );

        $skip_delete = $this->cli->flag( $assoc_args, 'skip-delete' );

        $this->cli->runcommand(
            "plugin uninstall polylang --deactivate" . ( $skip_delete ? ' --skip-delete' : '' ),
            array( 'return' => false, 'launch' => false, 'exit_error' => true )
        );
    }

}

}
