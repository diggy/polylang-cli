@pll-taxonomy
Feature: Manage Polylang taxonomies

  Background:
    Given a WP install
    And I run `wp plugin install polylang`
    And I run `wp plugin activate polylang`
    And I run `wp pll lang create Dutch nl nl_NL`
    And I run `wp pll doctor translate`

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
