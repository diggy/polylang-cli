<?php

add_action( 'init', 'pll_cli_behat_init_' );
function pll_cli_behat_init_() {

    register_post_type( 'book', array(
        'public' => true,
        'label'  => 'Books'
    ) );

    register_taxonomy( 'genre', 'book', array(
        'label'        => 'Genre',
        'rewrite'      => array( 'slug' => 'genre' ),
        'hierarchical' => true,
    ) );

}
