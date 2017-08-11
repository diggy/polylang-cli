<?php

namespace Polylang_CLI\Commands;

if ( ! class_exists( 'Polylang_CLI\Commands\DoctorCommand' ) ) {

/**
 * Troubleshoot Polylang.
 *
 * @package Polylang_CLI
 */
class DoctorCommand extends BaseCommand {

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
            return $this->cli->success( 'All translatable post and term objects are assigned to a language.' );
        }

        foreach ( $untranslated as $type => $object_ids ) {

            if ( empty( $object_ids ) ) {
                continue;
            }

            $type = rtrim( $type, 's' );

            $this->cli->warning( sprintf( "%d untranslated %s objects:", count( $object_ids ), $type ) );

            switch ( $type ) :

                case 'post' :

                    $this->cli->command( array( $type, 'list' ),
                        array(
                            'post_type' => implode( ',', $this->pll->model->get_translated_post_types() ),
                            'post__in' => implode( ',', $object_ids ),
                            'format' => $assoc_args['format']
                        )
                    );

                    break;

                case 'term' :

                    $formatter = $this->cli->formatter( $assoc_args, $this->fields_term, 'term' );

                    $terms = get_terms( array(
                        'taxonomy'   => null,
                        'hide_empty' => false,
                        'include'    => $object_ids
                    ) );

                    $formatter->display_items( $terms );

                    break;

                default :

                    $this->cli->error( sprintf( 'Invalid type: %s', $type ) );

            endswitch;
        }
    }

    /**
     * Translate untranslated posts and taxonomies in bulk.
     *
     * ## EXAMPLES
     *
     *     wp pll doctor translate
     *
     * @alias mass-translate
     */
    public function translate( $args, $assoc_args ) {

        if ( empty( $arsg[0] ) ) {

            $default_lang = $this->api->default_language();

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

            $this->cli->success( sprintf( 'Assigned %d posts and %d terms to the default language %s.', $posts, $terms, $default_lang ) );
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
     *     wp pll doctor recount
     */
    public function recount() {

        $this->cli->command( array( 'term', 'recount', $this->taxonomy ) );
    }

    /**
     * Mass install, update and prune core, theme and plugin language files.
     *
     * ## EXAMPLES
     *
     *     $ wp pll doctor language
     */
    public function language( $args, $assoc_args ) {

        $languages   = wp_list_pluck( $this->pll->model->get_languages_list(), 'locale', 'slug' );
        $locales_pll = array_unique( array_values( $languages ) );

        # see WP_CLI\CommandWithTranslation::get_all_languages()
        $locales_core   = wp_get_installed_translations( 'core' );
        $locales_core   = ! empty( $locales_core['default'] ) ? array_keys( $locales_core['default'] ) : array();
        $locales_core[] = 'en_US';

        $locales_orphan  = array_diff( $locales_core, $locales_pll );
        $locales_missing = array_diff( $locales_pll, $locales_core );

        # unset WP default locale
        if ( ( $key = array_search( 'en_US', $locales_orphan ) ) !== false ) {
            unset( $locales_orphan[$key] );
        }

        # prune superfluous language files
        if ( ! empty( $locales_orphan ) ) {

            $this->cli->confirm( sprintf( "%d superfluous core language packs were detected (%s).\nUninstall these language files?", count( $locales_orphan ), implode( ', ', $locales_orphan ) ), $assoc_args );

            foreach( $locales_orphan as $locale ) {

                $this->cli->runcommand(
                    "language core uninstall $locale",
                    array( 'return' => false, 'launch' => true, 'exit_error' => false )
                );
            }
        }

        # install missing language files
        if ( ! empty( $locales_missing ) ) {

            $this->cli->confirm( sprintf( "%d core language packs are missing (%s).\nInstall missing language files?", count( $locales_missing ), implode( ', ', $locales_missing ) ), $assoc_args );

            foreach( $locales_missing as $locale ) {

                $this->cli->runcommand(
                    "language core install $locale",
                    array( 'return' => false, 'launch' => true, 'exit_error' => false )
                );
            }
        }

        # update outdated language files (core, themes and plugins)
        $this->cli->log( 'Searching for updates...' );

        ob_start();

        $this->cli->command( array( 'language', 'core', 'list' ), array( 'field' => 'language', 'update' => 'available', 'format' => 'json' ) );

        $locales_outdated = ob_get_clean();

        $locales_outdated = json_decode( $locales_outdated );

        if ( ! empty( $locales_outdated ) ) {

            $this->cli->confirm( sprintf( "%d core language packs have updates available (%s).\nUpdate outdated language files?", count( $locales_outdated ), implode( ', ', $locales_outdated ) ), $assoc_args );

            $this->cli->runcommand(
                "language core update",
                array( 'return' => false, 'launch' => true, 'exit_error' => false )
            );
        }

        # done
        $this->cli->log( 'All done.' );
    }

    /**
     * Detect changes in Polylang API functions.
     *
     * ## EXAMPLES
     *
     *     wp pll doctor api
     */
    public function api () {

        $raw = \Polylang_CLI\Api\Api::functions_raw();
        $ref = \Polylang_CLI\Api\Api::functions_xref();

        return ( $raw == $ref ) ? $this->cli->success( 'There are no Polylang API changes.' ) : $this->cli->warning( 'Polylang API changes detected.' );
    }

}

}
