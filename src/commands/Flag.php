<?php

namespace Polylang_CLI\Commands;

/**
 * Class Flag
 *
 * @package Polylang_CLI
 */
class Flag extends BaseCommand {

    /**
     * List Polylang flags.
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Accepted values: table, csv, json, count, yaml. Default: table
     *
     * ## EXAMPLES
     *
     *     $ wp pll flag list
     *     $ wp pll flag list --format=csv
     *
     * @subcommand list
     */
    public function list_( $args, $assoc_args )
    {
        if ( ! defined ( 'PLL_SETTINGS_INC' ) ) {
            $this->cli->error( sprintf( 'The %s constant is not defined.', 'PLL_SETTINGS_INC' ) );
        }

        /*
         * Get predefined flags info
         *
         * The require makes a $flags variable available
         *
         * $flags contains an array with:
         *   key = flag file name (without the extension)
         *   value = translated country name (marked for translation with __())
         */
        require( PLL_SETTINGS_INC . '/flags.php' );

        $flag_objects = array();

        foreach ( $flags as $file => $name ) {

            $flag_object = new \stdClass();
            $flag_object->file = $file;
            $flag_object->name = $name;
            $flag_objects[] = $flag_object;
        }

        $formatter = $this->cli->formatter( $assoc_args, array( 'file', 'name' ) );

        $formatter->display_items( $flag_objects );
    }

}
