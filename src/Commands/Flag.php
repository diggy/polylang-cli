<?php

namespace Polylang_CLI\Commands;

if ( ! class_exists( 'Polylang_CLI\Commands\FlagCommand' ) ) {

/**
 * Inspect and manage Polylang country flags.
 *
 * @package Polylang_CLI
 */
class FlagCommand extends BaseCommand {

    /**
     * List Polylang country flags.
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

    /**
     * Set Polylang country flag for language.
     *
     * Run `wp pll flag list` to get a list of valid flag values.
     * Pass an empty string as second parameter to delete the flag value.
     *
     * ## OPTIONS
     *
     * <language-code>
     * : Language code (slug) for the language to update. Required.
     *
     * <flag-code>
     * : Valid flag code for the language to update. Required.
     *
     * ## EXAMPLES
     *
     *     # set flag for Dutch language
     *     $ wp pll flag set nl nl
     *
     *     # delete flag for Dutch language
     *     $ wp pll flag set nl ""
     */
    public function set( $args, $assoc_args )
    {
        $this->cli->runcommand(
            "pll lang update {$args[0]} --flag={$args[1]}",
            array( 'return' => false, 'launch' => true, 'exit_error' => false )
        );
    }
}

}
