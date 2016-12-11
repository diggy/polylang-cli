@pll-taxonomy
Feature: Manage Polylang taxonomies

  Background:
    Given a WP install

  Scenario: Manage Polylang taxonomies

    When I run `wp pll taxonomy list --format=csv`
    Then STDOUT should contain:
      """
      name,_builtin,public,hierarchical,translated
      category,1,1,1,1
      post_tag,1,1,,1
      nav_menu,1,,,
      link_category,1,,,
      post_format,1,1,,
      language,,,,
      post_translations,,,,
      term_language,,,,
      term_translations,,,,
      """
