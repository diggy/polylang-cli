@pll-menu
Feature: Manage WordPress nav menus

  Background:
    Given a WP install
    And I run `wp plugin install polylang`
    And I run `wp plugin activate polylang`
    And I run `wp pll lang create Dutch nl nl_NL`
    And I run `wp pll doctor translate`
    And I run `wp theme install twentysixteen --activate`
    And I run `wp pll lang create Deutsch de de_DE`

  Scenario: create nav menus and assign them to locations

    When I run `wp pll menu create "Primary menu" primary`
    Then STDOUT should contain:
      """
      Success: Assigned location primary to menu 9.
      Success: Assigned location primary___de to menu 10.
      """

    When I run `wp pll menu create "Follow us!" social --porcelain`
    Then STDOUT should contain:
      """
      11 12
      """

    When I run `wp theme mod get nav_menu_locations`
    Then STDOUT should contain:
      """
      key	value
      nav_menu_locations	=>
          primary	9
          primary___de	10
          social	11
          social___de	12
      """
