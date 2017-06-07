<?php

namespace Polylang_CLI\Commands;

use \Polylang_CLI\Traits\Cpt;

if ( ! class_exists( 'Polylang_CLI\Commands\TaxonomyCommand' ) ) {

/**
 * Inspect and manage WordPress taxonomies and their translation status.
 *
 * @package Polylang_CLI
 */
class TaxonomyCommand extends BaseCommand
{
    use Cpt;

    /**
     * Enable translation for taxonomies.
     *
     * ## OPTIONS
     *
     * <taxonomies>
     * : Taxonomy or comma-separated list of taxonomies to enable translation for.
     *
     * ## EXAMPLES
     *
     *     wp pll taxonomy enable genre
     *
     * @alias manage
     */
    public function enable( $args, $assoc_args ) {

        return $this->manage( __METHOD__, 'taxonomies', $args[0] );
    }

    /**
     * Disable translation for taxonomies.
     *
     * ## OPTIONS
     *
     * <taxonomies>
     * : Taxonomy or comma-separated list of taxonomies to disable translation for.
     *
     * ## EXAMPLES
     *
     *     wp pll taxonomy disable genre
     *
     * @alias unmanage
     */
    public function disable( $args, $assoc_args ) {

        return $this->manage( explode( '::', __METHOD__ )[1], 'post_types', $args[0] );
    }

    /**
     * List taxonomies with their translation status.
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Render output in a particular format.
     * ---
     * default: table
     * options:
     *   - table
     *   - csv
     *   - ids
     *   - json
     *   - count
     *   - yaml
     * ---
     *
     * ## EXAMPLES
     *
     *     wp pll taxonomy list
     *
     * @subcommand list
     */
    public function list_( $args, $assoc_args ) {

        $formatter = $this->cli->formatter( $assoc_args, array( 'name', '_builtin', 'public', 'hierarchical', 'translated' ), 'name' );

        if ( isset( $assoc_args['object_type'] ) ) {
            $assoc_args['object_type'] = array( $assoc_args['object_type'] );
        }

        $taxonomies = get_taxonomies( $assoc_args, 'objects' );

        $taxonomies = array_map( function( $taxonomy ) {
            $taxonomy->object_type = implode( ', ', $taxonomy->object_type );
            return $taxonomy;
        }, $taxonomies );

        $translated = $this->pll->model->get_translated_taxonomies();

        foreach ( $taxonomies as $taxonomy => $obj ) {

            $obj->translated = ( isset( $translated[$taxonomy] ) ) ? '1' : '';
        }

        $formatter->display_items( $taxonomies );
    }

}

}
