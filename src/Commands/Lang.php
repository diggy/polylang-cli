<?php

namespace Polylang_CLI\Commands;

if ( ! class_exists( 'Polylang_CLI\Commands\LangCommand' ) ) {

/**
 * Manage Polylang language taxonomy and taxonomy terms.
 *
 * @package Polylang_CLI
 */
class LangCommand extends BaseCommand
{
    /* LIST METHODS ***********************************************************/

    /**
     * List installed languages.
     *
     * List installed languages as Polylang objects. Passing `--pll=0` will output the result of `wp term list language`
     *
     * ## OPTIONS
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
     * : Accepted values: table, csv, json, count, yaml. Default: table
     *
     * [--pll=<value>]
     * : Pass 0 to list languages as WP term objects.
     *
     * ## AVAILABLE FIELDS (POLYLANG OBJECT)
     *
     * These fields will be displayed by default for each term:
     *
     * * term_id
     * * name
     * * slug
     * * term_group
     * * count
     * * locale
     * * is_rtl
     * * flag_code
     * ---
     * * term_taxonomy_id
     * * taxonomy
     * * description
     * * parent
     * * tl_term_id
     * * tl_term_taxonomy_id
     * * tl_count
     * * flag_url
     * * flag
     * * home_url
     * * search_url
     * * host
     * * mo_id
     * * page_on_front
     * * page_for_posts
     * * filter
     *
     * ## AVAILABLE FIELDS (WP TERM OBJECT)
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
     * There are no optionally available fields.
     *
     * ## EXAMPLES
     *
     *     # list languages as wp term objects
     *     $ wp pll lang list --pll=0
     *
     *     # list properties of languages as Polylang objects
     *     $ wp pll lang list --fields=host,mo_id,flag_code
     *
     * @subcommand list
     */
    public function list_( $args, $assoc_args ) {

        if ( '0' === $this->cli->flag( $assoc_args, 'pll' ) ) {
            return $this->cli->command( array( 'term', 'list', $this->taxonomy ), $assoc_args );
        }

        $languages = $this->pll->model->get_languages_list();

        # invoke formatter
        $formatter = $this->cli->formatter( $assoc_args, $this->fields_language, 'language' );

        # force LTR for table and csv display, see https://git.io/vynJY and https://git.io/vynJZ
        foreach ( $languages as $language ) {

            if ( wp_validate_boolean( $language->is_rtl ) ) {
                $language->name = "\xe2\x80\x8e" . $language->name;
            }
        }

        # display items
        $formatter->display_items( $languages );
    }

    /**
     * Get the URL for a language.
     *
     * ## OPTIONS
     *
     * <language-code>
     * : The language code (slug) to get the URL for. Required.
     *
     * ## EXAMPLES
     *
     *     wp pll lang url en
     *     wp pll lang url es
     */
    public function url( $args ) {

        $term_id = $this->get_lang_id_by_slug( $args[0] );

        if ( empty( $term_id ) ) {
            $this->cli->error( sprintf( 'Invalid language code: %s', $args[0] ) );
        }

        $this->cli->runcommand( "term list {$this->taxonomy} --include={$term_id} --field=url" );
    }

    /* CRUD METHODS ***********************************************************/

    /**
     * Get a language.
     *
     * ## OPTIONS
     *
     * <language-code>
     * : ID of the term to get
     *
     * [--field=<field>]
     * : Instead of returning the whole term, returns the value of a single field.
     *
     * [--fields=<fields>]
     * : Limit the output to specific fields. Defaults to all fields.
     *
     * [--format=<format>]
     * : Accepted values: table, json, csv, yaml. Default: table
     *
     * ## EXAMPLES
     *
     *     wp pll lang get en --format=json
     */
    public function get( $args, $assoc_args ) {

        $term_id = $this->get_lang_id_by_slug( $args[0] );

        $this->cli->command( array( 'term', 'get', $this->taxonomy, $term_id ), $assoc_args );
    }

    /**
     * Create a language.
     *
     * ## OPTIONS
     *
     * <name>
     * : Language name (used only for display). Required.
     *
     * <language-code>
     * : Language code (slug, ideally 2-letters ISO 639-1 language code). Required.
     *
     * <locale>
     * : WordPress locale. Required.
     *
     * [--rtl=<bool>]
     * : Right-to-left or left-to-right. Optional. Default: false
     *
     * [--order=<int>]
     * : Language order. Optional.
     *
     * [--flag=<string>]
     * : Country code, see flags.php. Optional.
     *
     * [--no_default_cat=<bool>]
     * : If set, no default category will be created for this language. Optional.
     *
     * ## EXAMPLES
     *
     *     $ wp pll lang create Français fr fr_FR
     *
     *     $ wp pll lang create Arabic ar ar_AR --rtl=true --order=3
     *
     *     $ wp pll lang create --prompt
     *     1/7 <name>: Français
     *     2/7 <language-code>: fr
     *     3/7 <locale>: fr_FR
     *     4/7 [--rtl=<bool>]: 0
     *     5/7 [--order=<int>]: 5
     *     6/7 [--flag=<string>]: fr
     *     7/7 [--no_default_cat=<bool>]:
     *     Success: Language added.
     */
    public function create( $args, $assoc_args ) {

        list( $name, $slug, $locale ) = $args;

        # parse args
        $defaults = array(
            'rtl'            => false,
            'order'          => 0,
            'flag'           => false,
            'no_default_cat' => false,
        );
        $assoc_args = wp_parse_args( $assoc_args, $defaults );

        # modify data array
        $assoc_args['term_group'] = $assoc_args['order'];
        unset( $assoc_args['order'] );

        list( $rtl, $flag, $no_default_cat, $term_group ) = array_values( $assoc_args );

        $language = $this->pll->model->add_language( compact( 'name', 'slug', 'locale', 'rtl', 'flag', 'no_default_cat', 'term_group' ) );

        $result = empty( $language ) ? 'error' : 'success';

        $this->settings_errors( $result );

        # install core language files
        $this->cli->runcommand(
            "core language install $locale --prompt=0",
            array( 'return' => false, 'launch' => true, 'exit_error' => false )
        );
    }

    /**
     * Update a language.
     *
     * ## OPTIONS
     *
     * <language-code>
     * : Language code (slug) for the language to update. Required.
     *
     * [--name=<name>]
     * : A new name for the language (used only for display). Optional.
     *
     * [--slug=<slug>]
     * : A new language code for the language (ideally 2-letters ISO 639-1 language code). Optional.
     *
     * [--locale=<locale>]
     * : Optional. A new WordPress locale for the language.
     *
     * [--rtl=<bool>]
     * : Optional. RTL or LTR, 1 or 0
     *
     * [--order=<int>]
     * : Optional. A new order (term_group) value for the language.
     *
     * [--flag=<string>]
     * : Optional. A new flag (country code) for the language, see flags.php.
     *
     * ## EXAMPLES
     *
     *     wp pll lang update en --name=English --order=15
     */
    public function update( $args, $assoc_args ) {

        $term_id = $this->get_lang_id_by_slug( $args[0] );

        # check if we have a valid language code
        if ( empty( $term_id ) ) {
            $this->cli->error( sprintf( 'Invalid language code. Run `%s` to get a list of valid language codes.', 'wp pll lang list --field=locale' ) );
        }

        # get the language
        $object = $this->pll->model->get_language( $term_id );

        # modify array item
        if ( isset( $assoc_args['order'] ) ) {
            $assoc_args['term_group'] = $assoc_args['order'];
            unset( $assoc_args['order'] );
        }

        # merge user defined and default args
        $defaults = array(
            'name'       => $object->name,
            'slug'       => $object->slug,
            'locale'     => $object->locale,
            'rtl'        => $object->is_rtl, // @todo check
            'term_group' => $object->term_group,
            'flag'       => $object->flag_code,
        );
        $assoc_args = wp_parse_args( $assoc_args, $defaults );

        # make protected method accessible
        $validate = new \ReflectionMethod( 'PLL_Admin_Model', 'validate_lang' );
        $validate->setAccessible( true );
        $valid = $validate->invokeArgs( $this->pll->model, array( $assoc_args, $object ) );

        # check if language valid
        if ( ! $valid ) {
            $this->settings_errors( 'error' );
        }

        # update the language
        $this->pll->model->update_language( array_merge( array( 'lang_id' => $term_id ), $assoc_args ) );

        # success!
        $this->settings_errors();
    }

    /**
     * Delete one, some or all languages.
     *
     * Deletes Polylang languages and uninstalls core language packs if not in use by other languages.
     *
     * ## OPTIONS
     *
     * [<language-code>]
     * : Comma-separated slugs of the languages to delete.
     *
     * [--all]
     * : Delete all languages
     *
     * [--keep_default]
     * : Whether to keep the default language.
     *
     * ## EXAMPLES
     *
     *     # delete the Afrikaans language and uninstall the `af` WordPress core language pack
     *     $ wp pll lang delete af
     *     Success: Language deleted. af (af)
     *     Success: Language uninstalled.
     *
     *     # delete all languages including the default language
     *     $ wp pll lang delete --all
     *
     *     # delete all languages except the default language
     *     $ wp pll lang delete --all --keep_default
     */
    public function delete( $args, $assoc_args ) {

        if ( ! $this->cli->flag( $assoc_args, 'all' ) && empty( $args ) ) {
            $this->cli->error( "You must specify at least one language or use --all." );
        }

        if ( $this->cli->flag( $assoc_args, 'all' ) ) {
            $args = array();
        }

        $slugs = wp_list_pluck( $this->pll->model->get_languages_list(), 'locale', 'slug' );

        $slugs = ( $this->cli->flag( $assoc_args, 'all' ) )
            ? $slugs
            : array_intersect_key( $slugs, array_flip( explode( ',', $args[0] ) ) );

        if ( empty( $slugs ) ) {
            $this->cli->error( 'Please enter 1 or more valid language codes. Run `wp pll lang list` to get a list of installed languages.' );
        }

        $i = 0;

        foreach ( $slugs as $slug => $locale ) {

            if ( $slug === $this->api->default_language() && $this->cli->flag( $assoc_args, 'keep_default' ) ) {
                $this->cli->log( sprintf( 'Notice: Keeping default language %s (%s).', $slug, $locale ) );
                continue;
            }

            $term_id = $this->get_lang_id_by_slug( $slug );

            if ( empty( $term_id ) ) {
                $this->cli->warning( sprintf( 'Invalid language code: %s (%s).', $slug, $locale ) );
                continue;
            }

            $this->pll->model->delete_language( $term_id );

            foreach ( $this->get_settings_errors()['success'] as $msg ) {
                $this->cli->success( sprintf( '%s %s (%s)', $msg, $slug, $locale ) );
            }

            # We need to clear the settings errors to prevent loop from breaking
            # Polylang uses wp settings errors to display admin messages
            $this->clear_settings_errors();

            # uninstall core language files, if not in use by another language
            $locales = array_count_values( wp_list_pluck( $this->pll->model->get_languages_list(), 'locale' ) );

            if ( ! isset( $locales[$locale] ) ) {

                $this->cli->runcommand(
                    "core language uninstall $locale",
                    array( 'return' => false, 'launch' => true, 'exit_error' => false )
                );
            }

            $i++;
        }

        $func = ( $i === count( $slugs ) ) ? 'success' : ( ( $i > 0 ) ? 'warning' : 'error' );

        $this->cli->$func( sprintf( '%d of %d languages deleted', $i, count( $slugs ) ) );
    }

    /* MISCELLANEOUS METHODS **************************************************/

    /**
     * Generate some languages.
     *
     * ## OPTIONS
     *
     * [--count=<number>]
     * : How many languages to generate. Default: 10
     *
     * ## EXAMPLES
     *
     *     wp pll lang generate --count=25
     */
    public function generate( $args, $assoc_args ) {

        if ( ! defined ( 'PLL_SETTINGS_INC' ) ) {
            $this->cli->error( sprintf( 'The %s constant is not defined.', 'PLL_SETTINGS_INC' ) );
        }

        # get predefined languages
        require( PLL_SETTINGS_INC . '/languages.php' );

        # parse assoc args
        extract( array_merge( array( 'count' => 10 ), $assoc_args ), EXTR_SKIP );

        # check count
        if ( $count > count( $languages ) ) {
            $this->cli->error( sprintf( 'Count value exceeds limit. There are only %d languages available.', count( $languages ) ) );
        }

        global $wpdb;

        $max_term_group = (int) $wpdb->get_var( "SELECT term_group FROM $wpdb->terms ORDER BY term_group DESC LIMIT 1" );
        $max_term_group = $max_term_group + 1;

        # get installed locales
        $installed_locales = wp_list_pluck( $this->pll->model->get_languages_list(), 'locale' );

        # init progress bar
        $notify = $this->cli->progress( 'Generating languages', $count );

        # init checklist
        $checklist = $term_ids = array();

        # init counter
        $i = 0;

        /*
         * Loop through the list of predefined languages
         * filterable by pll_predefined_languages
         *
         * $language contains:
         *
         * [0] => ISO 639-1 language code
         * [1] => WordPress locale
         * [2] => name
         * [3] => text direction
         * [4] => flag code
         */
        foreach ( $languages as $key => $language ) {

            # limit iteration
            if ( $i >= $count )
                break;

            # check if language locale is already in use
            if ( in_array( $language[1], $installed_locales ) )
                continue;

            $slug = $language[0];

            # if slug is in our checklist, try again
            if ( in_array( $slug, $checklist ) )
                $slug = strtolower( str_replace( '_', '-', $key ) );

            # skip if new slug in checklist
            if ( in_array( $slug, $checklist ) )
                continue;

            # skip if language is installed
            if ( false !== get_term_by( 'slug', $slug, $this->taxonomy ) )
                continue;

            # add slug to checklist
            $checklist[] = $slug;

            # add the language
            $language = $this->pll->model->add_language(
                array(
                    'name'       => $language[2],
                    'slug'       => $slug,
                    'locale'     => $language[1],
                    'rtl'        => ( $language[3] == 'rtl' ) ? 1 : 0,
                    'term_group' => $max_term_group++,
                    'flag'       => $language[4],
                )
            );

            # wish PLL()->model->add_language() returned term ID instead of true
            $term_ids[] = $this->get_lang_id_by_slug( $slug );

            // $this->get_settings_errors();

            # We need to clear the settings errors to prevent loop from breaking
            # Polylang uses wp settings errors to display admin messages
            $this->clear_settings_errors();

            # increment counter
            $i++;

            # update progress bar
            $notify->tick();
        }

        # finish progress bar
        $notify->finish();

        # list the newly created languages
        $this->cli->command( array( 'pll', 'lang', 'list'), array( 'include' => $term_ids ) ); // @todo allow list to display selection

        # success message
        $this->cli->success( sprintf( 'Generated %1$d of %2$d languages. New term IDs: %3$s', (int) $i, (int) $count, implode( ',', $term_ids ) ) );
    }

}

}
