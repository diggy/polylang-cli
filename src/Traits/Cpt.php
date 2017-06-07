<?php

namespace Polylang_CLI\Traits;

if ( ! trait_exists( 'Polylang_CLI\Traits\Cpt' ) ) {

trait Cpt {

    private function manage( $action, $type, $data )
    {
        if ( empty( $data ) ) {
            $this->cli->error( 'Specify one or more post types and/or taxonomies you want to enable translation for.' );
        }

        $input = explode( ',', $data );

        # invoke Polylang settings module
        $this->options_cpt = $settings = new \PLL_Settings_CPT( $this->pll );

        # set current module
        $settings->module = 'cpt';

        # populate the $_POST array
        $_POST = array();

        # sanitize post types input
        $post_types = array_map( 'sanitize_key', explode( ',', $data ) );
        $post_types = array_combine( $post_types, array_fill( 1, count( $post_types ), 1 ) );
        $post_types = array_intersect_key( $post_types, $settings->post_types );
        $post_types = array_merge( array_combine( $settings->options['post_types'], array_fill( 1, count( $settings->options['post_types'] ), 1 ) ), $post_types );

        # sanitize taxonomies input
        $taxonomies = array_map( 'sanitize_title', explode( ',', $data ) );
        $taxonomies = array_combine( $taxonomies, array_fill( 1, count( $taxonomies ), 1 ) );
        $taxonomies = array_intersect_key( $taxonomies, $settings->taxonomies );
        $taxonomies = array_merge( array_combine( $settings->options['taxonomies'], array_fill( 1, count( $settings->options['taxonomies'] ), 1 ) ), $taxonomies );

        # disable post types or taxonomies
        if ( $action === 'disable' ) {
            foreach ( array( 'post_types', 'taxonomies' ) as $key ) {
                foreach ( $input as $i ) {
                    if ( isset( ${$key}[$i] ) ) {
                        unset( ${$key}[$i] );
                    }
                }
            }
        }

        $_POST = compact( 'post_types', 'taxonomies' );
        $_POST['action'] = 'pll_save_options';

        # make protected method accessible
        $update = new \ReflectionMethod( 'PLL_Settings_CPT', 'update' );
        $update->setAccessible( true );
        $options = $update->invoke( $settings, $_POST );

        # update Polylang settings
        $settings->options = array_merge( $settings->options, $options );

        # see below, @todo review
        # update_option( 'polylang', $settings->options );

        # set the options
        $this->pll->model->options = $settings->options;

        # update options, default category and nav menu locations
        $this->pll->model->update_default_lang( $this->api->default_language() );

        # refresh language cache in case home urls have been modified
        $settings->model->clean_languages_cache();

        # refresh rewrite rules in case rewrite,  hide_default, post types or taxonomies options have been modified
        # don't use flush_rewrite_rules as we don't have the right links model and permastruct
        delete_option( 'rewrite_rules' );

        $this->cli->success( sprintf( 'Polylang `%s` option updated', $type ) );

/*
        ob_start();

        if ( ! get_settings_errors() ) {
            # send update message
            add_settings_error( 'general', 'settings_updated', __( 'Settings saved.' ), 'updated' );
            settings_errors();
            $response = new \WP_Ajax_Response( array( 'what' => 'success', 'data' => ob_get_clean() ) );
        } else {
            # send error messages
            settings_errors();
            $response = new \WP_Ajax_Response( array( 'what' => 'error', 'data' => ob_get_clean() ) );
        }

        foreach ( $response->responses as $xml ) {
            $object = simplexml_load_string( $xml, null, LIBXML_NOCDATA );
            if ( property_exists( $object, 'success' ) ) {
                \WP_CLI::success( wp_strip_all_tags( $object->success->response_data ) );
            } elseif ( property_exists( $object, 'error' ) ) {
                \WP_CLI::error( wp_strip_all_tags( $object->error->response_data ) );
            } else {
                \WP_CLI::error( 'An unknown error occurred saving the settings.' );
            }
        }
*/

    }

}

}
