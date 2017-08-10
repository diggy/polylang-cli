<?php

namespace Polylang_CLI\Commands;

if ( ! class_exists( 'Polylang_CLI\Commands\StringCommand' ) ) {

/**
 * Inspect and manage Polylang string translations.
 *
 * @package Polylang_CLI
 */
class StringCommand extends BaseCommand {

    /**
     * List string translations.
     *
     * ## OPTIONS
     *
     * [<language-code>]
     * : The language code (slug) to get the string translations for. Optional.
     *
     * [--fields=<value>]
     * : Limit the output to specific object fields. Valid values are: name, string, context, multiline, translations, row.
     *
     * [--format=<format>]
     * : Accepted values: table, csv, json, count, yaml. Default: table
     *
     * [--s=<value>]
     * : Search for a string in `name` and `string` fields.
     *
     * [--orderby=<value>]
     * : Define which column to sort.
     *
     * [--order=<value>]
     * : Define the order of the results, asc or desc.
     *
     * ## EXAMPLES
     *
     *     $ wp pll string list --s="WordPress site"
     *
     *     $ wp pll string list --order=asc --orderby=string
     *
     *     $ wp pll string list de --fields=string,translations
     *
     *     $ wp pll string list es --format=csv
     *
     * @subcommand list
     */
    public function list_( $args, $assoc_args ) {

        if ( isset( $args[0] ) && ! $this->pll->model->get_language( $args[0] ) ) {
            $this->cli->error( sprintf( '%s is not a valid language slug.', $args[0] ) );
        }

        foreach ( array( 's', 'order', 'orderby' ) as $_g ) {
            if ( $value = $this->cli->flag( $assoc_args, $_g ) ) {
                $_GET[$_g] = $value;
            }
        }

        $fields = $this->cli->flag( $assoc_args, 'fields' );

        add_filter( 'pll_strings_per_page', function( $per_page ) { return PHP_INT_MAX; } );

        $GLOBALS['hook_suffix'] = null;

        $string_table = new \PLL_Table_String( $this->pll->model->get_languages_list() );

        $string_table->prepare_items();

        $keys = $items = array();

        foreach ( $string_table->items as $data ) {

            $keys = array_merge( $keys, array_keys( $data ) );

            if ( isset( $args[0] ) ) {
                $data['translations'] = $data['translations'][$args[0]];
            }

            if ( $fields ) {
                $data = array_intersect_key( $data, array_flip( explode( ',', $fields ) ) );
            }

            $items[] = (object) $data;
        }

        $keys = array_unique( $keys );

        if ( $fields ) {
            $keys = array_intersect_key( array_combine( $keys, $keys ), explode( ',', $fields ) );
        }

        $formatter = $this->cli->formatter( $assoc_args, $keys );

        $formatter->display_items( $items );
    }

}

}
