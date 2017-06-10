@pll-api
Feature: Inspect the Polylang API

  Background:
    Given a WP install
    And I run `wp plugin install polylang`
    And I run `wp plugin activate polylang`
    And I run `wp pll lang create Dutch nl nl_NL`
    And I run `wp pll doctor translate`

  Scenario: List Polylang API functions

    When I run `wp pll api list`
    Then STDOUT should contain:
      """
      function
      the_languages
      current_language
      default_language
      get_post
      get_term
      home_url
      register_string
      translate_string
      is_translated_post_type
      is_translated_taxonomy
      languages_list
      set_post_language
      set_term_language
      save_post_translations
      save_term_translations
      get_post_language
      get_term_language
      get_post_translations
      get_term_translations
      count_posts
      pll__
      pll_e
      pll_esc_html__
      pll_esc_attr__
      pll_esc_html_e
      pll_esc_attr_e
      PLL
      """
