<?php

namespace Polylang_CLI\Commands;

/**
 * Inspect Polylang procedural API functions.
 *
 * @package Polylang_CLI
 */
class Api extends BaseCommand {

    /**
     * List Polylang procedural API functions.
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Accepted values: table, csv, json, count, yaml. Default: table
     *
     * ## EXAMPLES
     *
     *     $ wp pll api list
     *     $ wp pll api list --format=csv
     *
     * @subcommand list
     */
    public function list_( $args, $assoc_args )
    {
        $api_functions = array();

        foreach ( $this->api->functions() as $index => $func ) {

            $obj = new \stdClass();

            $obj->index = $index;
            $obj->function = $func;

            $api_functions[] = $obj;
        }

        $formatter = $this->cli->formatter( $assoc_args, array( 'function' ) );

        $formatter->display_items( $api_functions );
    }

}
