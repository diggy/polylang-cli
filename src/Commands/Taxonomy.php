<?php

namespace Polylang_CLI\Commands;

use \Polylang_CLI\Traits\Cpt;

/**
 * Class Taxonomy
 *
 * @package Polylang_CLI
 */
class Taxonomy extends BaseCommand
{
    use Cpt;

    /**
     * Enable translation for taxonomies
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
     * Disable translation for taxonomies
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
     * ## EXAMPLES
     *
     *     wp pll taxonomy list
     *
     * @subcommand list
     */
    public function list_( $args, $assoc_args ) {

        $taxonomies = get_taxonomies( $assoc_args, 'objects' );

        $translated = $this->pll->model->get_translated_taxonomies();

        foreach ( $taxonomies as $taxonomy => $obj ) {

            $obj->translated = ( isset( $translated[$taxonomy] ) ) ? '1' : '';
        }

        $formatter = $this->cli->formatter( $assoc_args, array( 'name', '_builtin', 'public', 'hierarchical', 'translated' ), 'name' );

        $formatter->display_items( $taxonomies );
    }

}
