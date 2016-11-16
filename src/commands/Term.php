<?php

namespace Polylang_CLI\Commands;

/**
 * Class Term
 *
 * @package Polylang_CLI
 */
class Term extends BaseCommand {

    /**
     * Get details about a translated term.
     *
     * ## OPTIONS
     *
     * <taxonomy>
     * : Taxonomy of the term to get
     *
     * <term-id>
     * : ID of the term to get
     *
     * [--field=<field>]
     * : Instead of returning the whole term, returns the value of a single field.
     *
     * [--fields=<fields>]
     * : Limit the output to specific fields. Defaults to all fields.
     *
     * [--format=<format>]
     * : Render output in a particular format.
     * ---
     * default: table
     * options:
     *   - table
     *   - csv
     *   - json
     *   - yaml
     * ---
     *
     * [--api]
     * : Use the Polylang API function pll_get_term_translations()
     *
     * ## EXAMPLES
     *
     *     # Get details about a category with term ID 18.
     *     $ wp pll term get category 18
     */
    public function get( $args, $assoc_args ) {

        list( $taxonomy, $term_id ) = $args;

        $term = get_term_by( 'id', $term_id, $taxonomy );

        if ( ! $term ) {
            \WP_CLI::error( "Term doesn't exist." );
        }

        $terms = $this->api->get_term_translations( $term_id );

        $obj_array = array();

        if ( $this->get_flag_value( $assoc_args, 'api' ) ) {

            foreach ( $terms as $slug => $term_id ) {
                $obj = new \stdClass();
                $obj->slug = $slug;
                $obj->term_id = $term_id;
                $obj_array[$term_id] = $obj;
            }

            $formatter = new \WP_CLI\Formatter( $assoc_args, array( 'slug', 'term_id' ), 'ID' );

        } else {

            foreach ( $terms as $term_id ) {

                $term = get_term_by( 'id', $term_id, $taxonomy );

                if ( empty( $assoc_args['fields'] ) ) {

                    $term_array = get_object_vars( $term );
                    $assoc_args['fields'] = array_keys( $term_array );
                }

                $term->count = (int) $term->count;
                $term->parent = (int) $term->parent;

                $obj_array[$term_id] = $term;
            }

            $formatter = $this->get_formatter( $assoc_args );
        }

        $formatter->display_items( $obj_array );
    }

}
