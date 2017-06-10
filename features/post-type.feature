@pll-post-type
Feature: Inspect and manage post type translation status

  Background:
    Given a WP install
    And I run `wp plugin install polylang`
    And I run `wp plugin activate polylang`
    And I run `wp pll lang create Dutch nl nl_NL`
    And I run `wp pll doctor translate`

  Scenario: List post types with their translation status

    When I run `wp pll post-type list`
    Then STDOUT should be a table containing rows:
      | name          | public | hierarchical | translated |
      | post          | 1      |              | 1          |
      | page          | 1      | 1            | 1          |
      | attachment    | 1      |              | 1          |
      | revision      |        |              |            |
      | nav_menu_item |        |              |            |
      | polylang_mo   |        |              |            |
