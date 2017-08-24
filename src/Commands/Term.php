<?php

namespace Polylang_CLI\Commands;

if ( ! class_exists( 'Polylang_CLI\Commands\TermCommand' ) ) {

/**
 * Inspect and manage WordPress taxonomy terms and their translations.
 *
 * @package Polylang_CLI
 */
class TermCommand extends BaseCommand {

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
            $this->cli->error( "Term doesn't exist." );
        }

        $terms = $this->api->get_term_translations( $term_id );

        $obj_array = array();

        if ( $this->cli->flag( $assoc_args, 'api' ) ) {

            foreach ( $terms as $slug => $term_id ) {
                $obj = new \stdClass();
                $obj->slug = $slug;
                $obj->term_id = $term_id;
                $obj_array[$term_id] = $obj;
            }

            $formatter = $this->cli->formatter( $assoc_args, array( 'slug', 'term_id' ), 'ID' );

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

            $formatter = $this->cli->formatter( $assoc_args, $this->fields_term, 'term' );
        }

        $formatter->display_items( $obj_array );
    }

    /**
     * Delete an existing taxonomy term and its translations.
     *
     * Errors if the term doesn't exist, or there was a problem in deleting it.
     *
     * ## OPTIONS
     *
     * <taxonomy>
     * : Taxonomy of the term to delete.
     *
     * <term-id>...
     * : One or more IDs of terms to delete.
     *
     * ## EXAMPLES
     *
     *     # Delete a term (English) and its translations (Spanish, French)
     *     $ wp pll term delete post_tag 56
     *     Deleted post_tag 56.
     *     Deleted post_tag 57.
     *     Deleted post_tag 58.
     *     Success: Deleted 3 of 3 terms.
     */
    public function delete( $args, $assoc_args ) {

        $taxonomy    = array_shift( $args );
        $is_taxonomy = get_taxonomy( $taxonomy );

        if ( empty( $is_taxonomy ) ) {
            $this->cli->error( sprintf( '%s is not a registered taxonomy.', sanitize_text_field( $taxonomy ) ) );
        }

        if ( ! $this->api->is_translated_taxonomy( $taxonomy ) ) {
            $this->cli->error( 'Polylang does not manage languages and translations for this taxonomy.' );
        }

        $term_ids = array_filter( wp_parse_id_list( $args ) );

        $all_term_ids = array();

        foreach ( $term_ids as $term_id ) {

            $all_term_ids[] = $term_id;

            $term_translations = $this->api->get_term_translations( $term_id );

            if ( ! empty( $term_translations ) ) {

                foreach ( $term_translations as $translation ) {
                    $all_term_ids[] = $translation;
                }
            }
        }

        $this->cli->runcommand(
            "term delete $taxonomy  " . implode( ' ', array_unique( array_filter( $all_term_ids ) ) ),
            array( 'return' => false, 'launch' => false, 'exit_error' => true )
        );
    }

    /**
     * Duplicate a taxonomy term to one or more languages.
     *
     * ## OPTIONS
     *
     * <taxonomy>
     * : Taxonomy of the term to duplicate
     *
     * <term-id>
     * : ID of the term to duplicate
     *
     * [<language-code>]
     * : Language code (slug), or comma-separated list of language codes, to duplicate the term to. Omit to duplicate to all languages. Optional.
     *
     * ## EXAMPLES
     *
     *     # Duplicate term 18 of the category taxonomy to all other languages.
     *     $ wp pll term duplicate category 18
     */
    public function duplicate( $args, $assoc_args ) {

        list( $taxonomy, $term_id ) = $args;

        if ( ! $this->api->is_translated_taxonomy( $taxonomy ) ) {
            $this->cli->error( 'Polylang does not manage languages and translations for this taxonomy.' );
        }

        $term_id = absint( $term_id );

        $term = get_term_by( 'id', $term_id, $taxonomy );

        if ( empty( $term ) ) {
            $this->cli->error( sprintf( '%d is not a valid taxonomy term object.', $term_id ) );
        }

        if ( empty( $this->api->get_term_language( $term_id ) ) ) {
            $this->cli->error( sprintf( 'There is no language associated with term %d.', $term_id ) );
        }

        $slugs = isset( $args[2] ) && $args[2]
            ? array_map( 'sanitize_title_with_dashes', explode( ',', $args[2] ) )
            : array_diff( $this->api->languages_list(), array( $this->api->get_term_language( $term_id, 'slug' ) ) );

        foreach ( $slugs as $slug ) {

            if ( ! in_array( $slug, $this->api->languages_list() ) ) {
                $this->cli->warning( sprintf( '%s is not a valid language.', $slug ) );
                continue;
            }

            $this->duplicate_term( $taxonomy, $term, $slug );
        }
    }

    private function duplicate_term( $taxonomy, $term, $slug )
    {
        $term_id           = absint( $term->term_id );
        $term_language     = $this->api->get_term_language( $term_id );

        if ( $slug === $term_language ) {

            $this->cli->warning( sprintf( 'Term %d (%s) cannot be duplicated to itself.', $term_id, $slug ) );

        } else {

            $term_data = get_term( $term_id, $taxonomy, 'ARRAY_A' );

            # check for translated post parent
            $term_parent_id = get_term( $term->parent, $taxonomy );

            if ( $term_parent_id && ! is_wp_error( $term_parent_id ) ) {
                if ( $parent = $this->pll->model->term->get_translation( $term_parent_id, $slug ) ) {
                    $term_data['parent'] = absint( $parent );
                }
            }

            # check if translation already exists
            $exists = $this->api->get_term( $term_id, $slug );

            $term_data['slug'] = sanitize_title( $term_data['name'] . '-' . $slug );

            # insert or update translation
            if ( ! empty( $exists ) ) {
                $term_data['ID']   = absint( $exists );
                $duplicate = wp_update_term( $term_data['ID'], $taxonomy, wp_slash( $term_data ) );
            } else {
                unset( $term_data['ID'] );
                $duplicate = wp_insert_term( $term->name, $taxonomy, wp_slash( $term_data ) );
            }

            if ( empty( $duplicate ) ) {
                $this->cli->warning( sprintf( 'Could not duplicate term %d to %s.', $term_id, $slug ) );
            } elseif ( is_wp_error( $duplicate ) ) {
                $this->cli->warning( sprintf( 'Term ID %d: %s (%s)', $term_id, $duplicate->get_error_message(), $slug ) );
            } else {

                # set term language
                $this->api->set_term_language( $duplicate['term_id'], $slug );

                # save term translations
                $this->api->save_term_translations( array_unique( array_merge( array( $term_language => $term_id, $slug => $duplicate['term_id'] ), $this->api->get_term_translations( $term_id ) ) ) );

                # sync taxonomies and post meta, if applicable
                $sync = new \PLL_Admin_Sync( $this->pll );
                $sync->pll_save_term( $term_id, $taxonomy, $this->api->get_term_translations( $term_id ) );

                # success message
                $msg = $exists
                    ? 'Updated term %3$d (%4$s) < term %1$d (%2$s)'
                    : 'Created term %3$d (%4$s) < term %1$d (%2$s)';

                $this->cli->success( sprintf( $msg, $term_id, $term_language, $duplicate['term_id'], $slug ) );
            }
        }
    }

    /**
     * Get a list of taxonomy terms for a language.
     *
     * ## OPTIONS
     *
     * <taxonomy>
     * : List terms of one or more taxonomies. Required.
     *
     * <language-code>
     * : The language code (slug) to get the taxonomy terms for. Required.
     *
     * [--<field>=<value>]
     * : Filter by one or more fields (see get_terms() $args parameter for a list of fields).
     *
     * [--field=<field>]
     * : Prints the value of a single field for each term.
     *
     * [--fields=<fields>]
     * : Limit the output to specific object fields.
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
     * ## AVAILABLE FIELDS
     *
     * These fields will be displayed by default for each term:
     *
     * * term_id
     * * term_taxonomy_id
     * * name
     * * slug
     * * description
     * * parent
     * * count
     *
     * These fields are optionally available:
     *
     * * url
     *
     * ## EXAMPLES
     *
     *     # List post categories
     *     $ wp pll term list color nl --format=csv
     *     term_id,term_taxonomy_id,name,slug,description,parent,count
     *     2,2,Rood,rood,,0,1
     *     3,3,Blauw,blauw,,0,1
     *
     *     # List post tags
     *     $ wp pll term list post_tag en --fields=name,slug
     *     +-----------+-------------+
     *     | name      | slug        |
     *     +-----------+-------------+
     *     | Articles  | articles    |
     *     | aside     | aside       |
     *     +-----------+-------------+
     *
     * @subcommand list
     */
    public function list_( $args, $assoc_args )
    {
        list ( $taxonomy, $language ) = $args;

        $this->pll->curlang = $this->pll->model->get_language( $this->pll->options['default_lang'] );
        new \PLL_Frontend_Filters( $this->pll );

        $this->cli->command(
            array( 'term', 'list', $taxonomy ),
            array_merge( array( 'lang' => $language ), $assoc_args )
        );
    }

    /**
     * Generate some taxonomy terms and their translations.
     *
     * Creates a specified number of sets of new terms and their translations with dummy data.
     *
     * ## OPTIONS
     *
     * <taxonomy>
     * : The taxonomy for the generated terms.
     *
     * [--count=<number>]
     * : How many sets of terms to generate?
     * ---
     * default: 5
     * ---
     *
     * [--max_depth=<number>]
     * : Generate child terms down to a certain depth.
     * ---
     * default: 1
     * ---
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
     *   - ids
     * ---
     *
     * ## EXAMPLES
     *
     *     # Generate some post categories, and translations.
     *     $ wp pll term generate category --count=3 --format=ids
     *     115 116 117 118 119 120
     */
    public function generate( $args, $assoc_args ) {

        list ( $taxonomy ) = $args;

        if ( ! $this->api->is_translated_taxonomy( $taxonomy ) ) {
            $this->cli->error( 'Polylang does not manage languages and translations for this taxonomy.' );
        }

        $languages = $this->api->languages_list();

        $count = $this->cli->flag( $assoc_args, 'count' );
        $count = ( $count < 1 ) ? 1 : absint( $count );
        $count = $count * count( $languages );

        ob_start();

        $this->cli->command(
            array( 'term', 'generate', $taxonomy ),
            array_merge( $assoc_args, array( 'count' => $count, 'format' => 'ids' ) )
        );

        $ids = ob_get_clean();

        $term_ids = wp_parse_id_list( $ids );

        $terms = array_chunk( $term_ids, count( $languages ) );

        foreach ( $terms as $i => $chunk ) {

            $terms[$i] = array_combine( $languages, $chunk );

            foreach ( $terms[$i] as $lang => $term_id ) {

                $this->api->set_term_language( $term_id, $lang );
            }

            $this->api->save_term_translations( $terms[$i] );
        }

        $format = $this->cli->flag( $assoc_args, 'format' );

        if ( 'ids' !== $format ) {

            return $this->cli->command(
                array( 'term', 'list', $taxonomy ),
                array( 'format' => $format, 'include' => implode( ',', $term_ids ) )
            );
        }

        echo implode( ' ', $term_ids );
    }

}

}
