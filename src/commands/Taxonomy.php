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

}
