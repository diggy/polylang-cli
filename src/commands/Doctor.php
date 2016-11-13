<?php

namespace Polylang_CLI\Commands;

/**
 * Class Doctor
 *
 * @package Polylang_CLI
 */
class Doctor extends BaseCommand {

    /**
     * List untranslated post and term objects (translatable).
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
     *   - json
     *   - count
     *   - yaml
     * ---
     *
     * ## EXAMPLES
     *
     *     wp pll doctor check
     */
    public function check( $args, $assoc_args ) {

        $untranslated = $this->pll->model->get_objects_with_no_lang();

        if ( empty( $untranslated ) ) {
            return \WP_CLI::success( 'All translatable post and term objects are assigned to a language.' );
        }

        foreach ( $untranslated as $type => $object_ids ) {

            if ( empty( $object_ids ) ) {
                continue;
            }

            $type = rtrim( $type, 's' );

            \WP_CLI::warning( sprintf( "%d untranslated %s objects:", count( $object_ids ), $type ) );

            switch ( $type ) :

                case 'post' :

                    \WP_CLI::run_command( array( $type, 'list' ), array( 'post_type' => implode( ',', $this->pll->model->get_translated_post_types() ), 'post__in' => implode( ',', $object_ids ), 'format' => $assoc_args['format'] ) );

                    break;

                case 'term' :

                    //$formatter = new \WP_CLI\Formatter( $assoc_args, array( 'term_id', 'name', 'slug' ) );
                    $formatter = $this->get_formatter( $assoc_args );

                    $terms = get_terms( array(
                        'taxonomy'   => null,
                        'hide_empty' => false,
                        'include'    => $object_ids
                    ) );

                    $formatter->display_items( $terms );

                    break;

                default :

                    \WP_CLI::error( sprintf( 'Invalid type: %s', $type ) );

            endswitch;
        }
    }

    /**
     * Translate untranslated posts and taxonomies in bulk
     *
     * ## EXAMPLES
     *
     *     wp pll doctor translate
     *
     * @alias mass-translate
     */
    public function translate( $args, $assoc_args ) {

        if ( empty( $arsg[0] ) ) {

            $default_lang = pll_default_language();

            $posts = $terms = 0;

            if ( $untranslated = $this->pll->model->get_objects_with_no_lang() ) {

                if ( ! empty( $untranslated['posts'] ) ) {
                    $posts = count( $untranslated['posts'] );
                    $this->pll->model->set_language_in_mass( 'post', $untranslated['posts'], $default_lang );
                }

                if ( ! empty( $untranslated['terms'] ) ) {
                    $terms = count( $untranslated['terms'] );
                    $this->pll->model->set_language_in_mass( 'term', $untranslated['terms'], $default_lang );
                }
            }

            \WP_CLI::success( sprintf( 'Assigned %d posts and %d terms to the default language %s.', $posts, $terms, $default_lang ) );
        }
    }

    /**
     * Recalculate number of posts assigned to each language taxonomy term.
     *
     * In instances where manual updates are made to the terms assigned to
     * posts in the database, the number of posts associated with a term
     * can become out-of-sync with the actual number of posts.
     *
     * This command runs wp_update_term_count() on the language taxonomy's terms
     * to bring the count back to the correct value.
     *
     * ## EXAMPLES
     *
     *     wp pll lang recount
     */
    public function recount() {

        \WP_CLI::run_command( array( 'term', 'recount', $this->taxonomy ) );
    }

}
