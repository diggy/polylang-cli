<?php

namespace Polylang_CLI\Commands;

if ( ! class_exists( 'Polylang_CLI\Commands\PostCommand' ) ) {

/**
 * Manage posts and their translations.
 *
 * @package Polylang_CLI
 */
class PostCommand extends BaseCommand {

    /**
     * List a post and its translations, or get a post for a language.
     *
     * ## OPTIONS
     *
     * <post_id>
     * : Post ID of the post to get. Required.
     *
     * [<language-code>]
     * : The language code (slug) to get the post ID for, when using the --api flag. Optional.
     *
     * [--api]
     * : Use the Polylang API function pll_get_post()
     *
     * ## EXAMPLES
     *
     *     wp pll post get 12
     *     wp pll post get 1 es --api
     */
    public function get( $args, $assoc_args ) {

        list( $post_id ) = $args;

        if ( ! $post = get_post( $post_id ) ) {
            $this->cli->error( sprintf( '%d is not a valid post object', $post_id ) );
        }

        if ( ! $this->api->is_translated_post_type( $post->post_type ) ) {
            $this->cli->error( 'Polylang does not manage languages and translations for this post type.' );
        }

        if ( $this->cli->flag( $assoc_args, 'api' ) ) {

            # second param of pll_get_post() is empty string by default
            $slug = isset( $args[1] ) && $args[1] ? $args[1] : $this->api->default_language();

            if ( empty( $translation = $this->api->get_post( $args[0], $slug ) ) ) {

                $this->cli->error( sprintf( "Post %d has not yet been translated to %s.", $post_id, $slug ) );

            } else {

                $this->cli->runcommand(
                    sprintf( 'post get %d', $translation ),
                    array( 'return' => false, 'launch' => false, 'exit_error' => false )
                );
            }

        } else {

            $this->cli->runcommand(
                sprintf( 'post list --post__in=%s', implode( ',', $this->api->get_post_translations( $post_id ) ) ),
                array( 'return' => false, 'launch' => false, 'exit_error' => false )
            );
        }
    }

    /**
     * Create a new post and its translations.
     *
     * ## OPTIONS
     *
     * --post_type=<type>
     * : The type of the new posts. Required.
     *
     * [--<field>=<value>]
     * : Associative args for the new posts. See wp_insert_post(). These values will take precendence over input from STDIN.
     *
     * [--stdin]
     * : Read structured JSON from STDIN.
     *
     * [--porcelain]
     * : Output just the new post ids.
     *
     * ## EXAMPLES
     *
     *     # Create a post and duplicate it to all languages
     *     $ wp pll post create --post_type=page --post_title="Blog" --post_status=publish
     *     Success: Created and linked 2 posts of the page post type.
     *
     *     # Create a post and its translations using structured JSON
     *     $ echo '{"nl":{"post_title":"Dutch title","post_content":"Dutch content"},"de":{"post_title":"German title","post_content":"German content"}}' | wp pll post create --post_type=post --stdin
     *     Success: Created and linked 2 posts of the post post type.
     */
    public function create( $args, $assoc_args ) {

        $post_type = $this->cli->flag( $assoc_args, 'post_type' );

        if ( ! $this->api->is_translated_post_type( $post_type ) ) {
            $this->cli->error( 'Polylang does not manage languages and translations for this post type.' );
        }

        $languages = $this->api->languages_list();

        $data = $post_ids = array();

        # handle input from STDIN
        if ( $this->cli->flag( $assoc_args, 'stdin' ) ) {

            $stdin = file_get_contents( 'php://stdin' );
            $data  = json_decode( $stdin, true );

            if ( empty( $data ) ) {
                $this->cli->error( 'Invalid JSON.' );
            }

            # check if we have content for all languages
            $diff = array_diff( $languages, array_keys( $data ) );

            if ( ! empty( $diff ) ) {
                $this->cli->error( sprintf( 'Please provide input for all languages: %s', implode( ', ', $languages ) ) );
            }
        }

        # input from $assoc_args
        if ( empty( $data ) ) {
            foreach ( $languages as $slug ) {
                $data[$slug] = array();
            }
        }

        foreach ( $data as $slug => $_assoc_args ) {

            if ( ! in_array( $slug, $languages ) ) {
                $this->cli->warning( sprintf( '%s is not a valid language.', $slug ) );
                continue;
            }

            # prioritize input from $assoc_args
            $_assoc_args = array_merge( $assoc_args, $_assoc_args );
            $_assoc_args['porcelain'] = true;

            ob_start();

            $this->cli->command( array( 'post', 'create' ), $_assoc_args );

            $post_id = $post_ids[$slug] = ob_get_clean();

            $this->api->set_post_language( $post_id, $slug );
        }

        $this->api->save_post_translations( $post_ids );

        if ( ! $this->cli->flag( $assoc_args, 'porcelain' ) ) {
            $this->cli->success( sprintf( "Created and linked %d posts of the %s post type.", count( $post_ids ), $post_type ) );
        }

        echo implode( ' ', array_map( 'absint', $post_ids ) );
    }

    /**
     * Update one or more existing posts and their translations.
     *
     * ## OPTIONS
     *
     * <id>...
     * : One or more IDs of posts to update.
     *
     * [<file>]
     * : Read post content from <file>. If this value is present, the
     *     `--post_content` argument will be ignored.
     *
     *   Passing `-` as the filename will cause post content to
     *   be read from STDIN.
     *
     * --<field>=<value>
     * : One or more fields to update. See wp_update_post().
     *
     * [--defer-term-counting]
     * : Recalculate term count in batch, for a performance boost.
     *
     * ## EXAMPLES
     *
     *     $ wp pll post update 13 --comment_status=closed
     *     Success: Updated post 13.
     */
    public function update( $args, $assoc_args ) {

        $this->pll->filters_post = new \PLL_Admin_Filters_Post( $this->pll );
        $this->pll->sync         = new \PLL_Admin_Sync( $this->pll );

        $GLOBALS['pagenow'] = 'post.php';

        # get around Polylang's capability check
        $current_user = wp_get_current_user();
        $current_user->allcaps = get_role( 'administrator' )->capabilities;

        $_args       = implode( ' ', array_merge( array( 'post', 'update' ), $args ) );
        $_assoc_args = empty( $assoc_args ) ? '' : implode( ' ', array_map( function ( $v, $k ) { return "--{$k}='{$v}'"; }, $assoc_args, array_keys( $assoc_args ) ) );

        $this->cli->runcommand(
            sprintf( '%s %s', $_args, $_assoc_args ),
            array( 'return' => false, 'launch' => false, 'exit_error' => false )
        );
    }

    /**
     * Delete a post and its translations.
     *
     * ## OPTIONS
     *
     * <post_id>
     * : Post ID of the a translated post to delete. Required.
     *
     * [--force]
     * : Skip the trash bin.
     *
     * [--defer-term-counting]
     * : Recalculate term count in batch, for a performance boost.
     *
     * ## EXAMPLES
     *
     *     wp pll post delete 32
     */
    public function delete( $args, $assoc_args ) {

        list( $post_id ) = $args;

        if ( ! $post = get_post( $post_id ) ) {
            $this->cli->error( sprintf( '%d is not a valid post object', $post_id ) );
        }

        if ( ! $this->api->is_translated_post_type( $post->post_type ) ) {
            $this->cli->error( 'Polylang does not manage languages and translations for this post type.' );
        }

        $post_ids = $this->api->get_post_translations( $post_id );

        $this->cli->command( array( 'post', 'delete', implode( ' ', $post_ids ) ), $assoc_args );
    }

    /**
     * Duplicate a post to one or more languages.
     *
     * Syncs metadata and taxonomy terms, based on Polylang settings. Run `wp pll option list` to inspect current settings.
     *
     * ## OPTIONS
     *
     * <post_id>
     * : Post ID of the post to duplicate. Required.
     *
     * [<language-code>]
     * : Language code (slug), or comma-separated list of language codes, to duplicate the post to. Omit to duplicate to all languages. Optional.
     *
     * ## EXAMPLES
     *
     *     # Duplicate post 23 (Dutch) to German
     *     $ wp pll post duplicate 23 de
     *     Success: Created post 68 (de) < post 23 (nl)
     *
     *     # Duplicate post 23 (Dutch) to all languages (Dutch and Spanish)
     *     $ wp pll post duplicate 23
     *     Success: Updated post 68 (de) < post 23 (nl)
     *     Success: Created post 69 (es) < post 23 (nl)
     */
    public function duplicate( $args, $assoc_args ) {

        list( $post_id ) = $args;

        if ( ! $post = get_post( $post_id, ARRAY_A ) ) {
            $this->cli->error( sprintf( '%d is not a valid post object', $post_id ) );
        }

        if ( ! $this->api->is_translated_post_type( $post['post_type'] ) ) {
            $this->cli->error( 'Polylang does not manage languages and translations for this post type.' );
        }

        $slugs = isset( $args[1] ) && $args[1]
            ? array_map( 'sanitize_title_with_dashes', explode( ',', $args[1] ) )
            : array_diff( $this->api->languages_list(), array( pll_get_post_language( $post_id ) ) );

        foreach ( $slugs as $slug ) {

            if ( ! in_array( $slug, $this->api->languages_list() ) ) {

                $this->cli->warning( sprintf( '%s is not a valid language.', $slug ) );
                continue;
            }

            $this->duplicate_post( $post, $slug );
        }
    }

    private function duplicate_post( $post, $slug )
    {
        $post_id           = absint( $post['ID'] );
        $post_language     = pll_get_post_language( $post_id );
        $post_translations = $this->api->get_post_translations( $post_id );

        $post_data = $post;

        if ( $slug === $post_language ) {

            $this->cli->warning( sprintf( 'Post %d (%s) cannot be duplicated to itself.', $post_id, $slug ) );

        } else {

            # check for translated post parent
            if ( ( $post_parent_id = wp_get_post_parent_id( $post_id ) ) && ( $parent = $this->pll->model->post->get_translation( $post_parent_id, $slug ) ) ) {
                $post_data['post_parent'] = absint( $parent );
            }

            # check if translation already exists
            $exists = $this->api->get_post( $post_id, $slug );

            # insert or update translation
            if ( ! empty( $exists ) ) {

                $post_data['ID'] = absint( $exists );
                $duplicate = wp_update_post( wp_slash( $post_data ), true );

            } else {

                unset( $post_data['ID'] );

                if ( class_exists( '\WC_Admin_Duplicate_Product' ) && 'product' === $post_data['post_type'] ) {

                    $this->cli->log( sprintf( 'Duplicating WooCommerce product %d.', $post_id ) );

                    $product    = wc_get_product( $post_id );
                    $duplicator = new \WC_Admin_Duplicate_Product();
                    $duplicate = $duplicator->product_duplicate( $product );

                    $this->cli->log( sprintf( 'Duplicated WooCommerce product ID: %d.', $duplicate->get_id() ) );

                    $this->cli->log( 'Updating product data...' );

                    $duplicate = wp_update_post( wp_slash( array(
                        'ID'          => $duplicate->get_id(),
                        'post_title'  => $post_data['post_title'],
                        'post_status' => $post_data['post_status'],
                        'post_name'   => sprintf( '%s-%s', $post_data['post_name'], $slug )
                    ) ), true );

                } else {

                    $duplicate = wp_insert_post( wp_slash( $post_data ), true );

                }
            }

            if ( empty( $duplicate ) ) {
                $this->cli->warning( sprintf( 'Could not duplicate post %d to %s.', $post_id, $slug ) );
            } elseif ( is_wp_error( $duplicate ) ) {
                $this->cli->warning( $duplicate->get_error_message() );
            } else {

                # set post language
                $this->api->set_post_language( $duplicate, $slug );

                # save post translations
                $this->api->save_post_translations( array_unique( array_merge( array( $post_language => $post_id, $slug => $duplicate ), $post_translations ) ) );

                # sync taxonomies and post meta, if applicable
                $this->pll->filters_post = new \PLL_Admin_Filters_Post( $this->pll );
                $sync = new \PLL_Admin_Sync( $this->pll );
                $sync->pll_save_post( $post_id, get_post( $post_id, 'OBJECT' ), $this->api->get_post_translations( $post_id ) );

                # success message
                $msg = $exists
                    ? 'Updated post %3$d (%4$s) < post %1$d (%2$s)'
                    : 'Created post %3$d (%4$s) < post %1$d (%2$s)';
                $this->cli->success( sprintf( $msg, $post_id, $post_language, $duplicate, $slug ) );
            }
        }
    }

    /**
     * Count posts for a language.
     *
     * ## OPTIONS
     *
     * <language-code>
     * : The language code (slug) to get the post count for. Required.
     *
     * [--post_type=<post_type>]
     * : One or more post types to get the count for for. Default: post. Optional.
     *
     * ## EXAMPLES
     *
     *     wp pll post count nl
     *     wp pll post count es --post_type=page
     */
    public function count( $args, $assoc_args ) {

        $language = $this->pll->model->get_language( $args[0] );

        $this->cli->success( sprintf( 'Post count: %d', $this->api->count_posts( $language, $assoc_args ) ) );
    }

    /**
     * Get a list of posts in a language.
     *
     * NB: Like Polylang, this command passes a `lang` parameter to WP_Query,
     * i.e. `wp post list --lang=<language-code>`.
     *
     * ## OPTIONS
     *
     * <language-code>
     * : The language code (slug) to get the post count for. Required.
     *
     * [--<field>=<value>]
     * : One or more args to pass to WP_Query.
     *
     * [--field=<field>]
     * : Prints the value of a single field for each post.
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
     * These fields will be displayed by default for each post:
     *
     * * ID
     * * post_title
     * * post_name
     * * post_date
     * * post_status
     *
     * These fields are optionally available:
     *
     * * post_author
     * * post_date_gmt
     * * post_content
     * * post_excerpt
     * * comment_status
     * * ping_status
     * * post_password
     * * to_ping
     * * pinged
     * * post_modified
     * * post_modified_gmt
     * * post_content_filtered
     * * post_parent
     * * guid
     * * menu_order
     * * post_type
     * * post_mime_type
     * * comment_count
     * * filter
     * * url
     *
     * ## EXAMPLES
     *
     *     wp pll post list nl
     *
     *     # List post
     *     $ wp pll post list es --field=ID
     *     568
     *     829
     *     1329
     *     1695
     *
     *     # List posts in JSON
     *     $ wp pll post list en-gb --post_type=post --posts_per_page=5 --format=json
     *     [{"ID":1,"post_title":"Hello world!","post_name":"hello-world","post_date":"2015-06-20 09:00:10","post_status":"publish"},{"ID":1178,"post_title":"Markup: HTML Tags and Formatting","post_name":"markup-html-tags-and-formatting","post_date":"2013-01-11 20:22:19","post_status":"draft"}]
     *
     *     # List all pages
     *     $ wp pll post list nl --post_type=page --fields=post_title,post_status
     *     +-------------+-------------+
     *     | post_title  | post_status |
     *     +-------------+-------------+
     *     | Sample Page | publish     |
     *     +-------------+-------------+
     *
     *     # List ids of all pages and posts
     *     $ wp pll post list es --post_type=page,post --format=ids
     *     15 25 34 37 198
     *
     *     # List given posts
     *     $ wp pll post list nl --post__in=1,3
     *     +----+--------------+-------------+---------------------+-------------+
     *     | ID | post_title   | post_name   | post_date           | post_status |
     *     +----+--------------+-------------+---------------------+-------------+
     *     | 1  | Hello world! | hello-world | 2016-06-01 14:31:12 | publish     |
     *     +----+--------------+-------------+---------------------+-------------+
     *
     * @subcommand list
     */
    public function list_( $args, $assoc_args ) {

        $assoc_args['lang'] = $args[0];

        $this->cli->command( array( 'post', 'list' ), $assoc_args );
    }

    /**
     * Generate some posts and their translations.
     *
     * Creates a specified number of sets of new posts with dummy data.
     *
     * ## OPTIONS
     *
     * [--count=<number>]
     * : How many posts to generate?
     * ---
     * default: 5
     * ---
     *
     * [--post_type=<type>]
     * : The type of the generated posts.
     * ---
     * default: post
     * ---
     *
     * [--post_status=<status>]
     * : The status of the generated posts.
     * ---
     * default: publish
     * ---
     *
     * [--post_author=<login>]
     * : The author of the generated posts.
     * ---
     * default:
     * ---
     *
     * [--post_date=<yyyy-mm-dd>]
     * : The date of the generated posts. Default: current date
     *
     * [--post_content]
     * : If set, the command reads the post_content from STDIN.
     *
     * [--max_depth=<number>]
     * : For hierarchical post types, generate child posts down to a certain depth.
     * ---
     * default: 1
     * ---
     *
     * [--format=<format>]
     * : Render output in a particular format.
     * ---
     * default: ids
     * options:
     *   - progress
     *   - ids
     * ---
     *
     * ## EXAMPLES
     *
     *     # Generate posts.
     *     $ wp pll post generate --count=10 --post_type=page --post_date=1999-01-04
     *     Generating posts  100% [================================================] 0:01 / 0:04
     *
     *     # Generate posts with fetched content.
     *     $ curl http://loripsum.net/api/5 | wp pll post generate --post_content --count=10
     *       % Total    % Received % Xferd  Average Speed   Time    Time     Time  Current
     *                                      Dload  Upload   Total   Spent    Left  Speed
     *     100  2509  100  2509    0     0    616      0  0:00:04  0:00:04 --:--:--   616
     *     Generating posts  100% [================================================] 0:01 / 0:04
     *
     *     # Add meta to every generated posts.
     *     $ wp pll post generate --format=ids | xargs -d ' ' -I % wp post meta add % foo bar
     *     Success: Added custom field.
     *     Success: Added custom field.
     *     Success: Added custom field.
     */
    public function generate( $args, $assoc_args ) {

        $languages = $this->api->languages_list();
        $default_language = $this->api->default_language();

        if ( ! $this->api->is_translated_post_type( $this->cli->flag( $assoc_args, 'post_type' ) ) ) {

            $this->cli->error( 'Polylang does not manage languages and translations for this post type.' );
        }

        $assoc_args['count'] = isset( $assoc_args['count'] ) ? intval( $assoc_args['count'] ) : 3;
        $assoc_args['count'] = count( $languages ) * $assoc_args['count'];

        ob_start();

        $this->cli->command( array( 'post', 'generate' ), $assoc_args );

        $post_ids = ob_get_clean();

        $ids = array_chunk( explode( ' ', $post_ids ), count( $languages ) );

        foreach ( $ids as $i => $chunk ) {

            $ids[$i] = array_combine( $languages, $chunk );

            foreach ( $ids[$i] as $lang => $post_id ) {

                $this->api->set_post_language( $post_id, $lang );
            }

            $this->api->save_post_translations( $ids[$i] );
        }

        if ( 'ids' === $this->cli->flag( $assoc_args, 'format' ) ) {
            echo $post_ids; // compare \Post_Command::list_()
        } else {
            $this->cli->success( sprintf( 'Generated %d posts.', $assoc_args['count'] ) );
        }
    }

}

}
