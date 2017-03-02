<?php

namespace Polylang_CLI\Commands;

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
