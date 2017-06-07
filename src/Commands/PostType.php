<?php

namespace Polylang_CLI\Commands;

use \Polylang_CLI\Traits\Cpt;

if ( ! class_exists( 'Polylang_CLI\Commands\PostTypeCommand' ) ) {

/**
 * Inspect and manage WordPress post types and their translation status.
 *
 * @package Polylang_CLI
 */
class PostTypeCommand extends BaseCommand
{
    use Cpt;

    /**
     * Enable translation for post types.
     *
     * ## OPTIONS
     *
     * <post_types>
     * : One or a comma-separated list of post types to enable translation for.
     *
     * ## EXAMPLES
     *
     *     wp pll post-type enable book
     *
     * @alias manage
     */
    public function enable( $args, $assoc_args ) {

        return $this->manage( explode( '::', __METHOD__ )[1], 'post_types', $args[0] );
    }

    /**
     * Disable translation for post types.
     *
     * ## OPTIONS
     *
     * <post_types>
     * : One or a comma-separated list of post types to disable translation for.
     *
     * ## EXAMPLES
     *
     *     wp pll post-type disable book
     *
     * @alias unmanage
     */
    public function disable( $args, $assoc_args ) {

        return $this->manage( explode( '::', __METHOD__ )[1], 'post_types', $args[0] );
    }

    /**
     * List post types with their translation status.
     *
     * ## EXAMPLES
     *
     *     wp pll post-type list
     *
     * @subcommand list
     */
    public function list_( $args, $assoc_args ) {

        $post_types = get_post_types( $assoc_args, 'objects' );

        $translated = $this->pll->model->get_translated_post_types();

        foreach ( $post_types as $post_type => $obj ) {

            $obj->translated = ( isset( $translated[$post_type] ) ) ? '1' : '';
        }

        $formatter = $this->cli->formatter( $assoc_args, array( 'name', 'public', 'hierarchical', 'translated' ), 'post' );

        $formatter->display_items( $post_types );
    }

}

}
