@pll-menu
Feature: Manage WordPress nav menus

  Background:
    Given a WP install
    And I run `wp theme install twentysixteen --activate`
    And I run `wp pll lang create Deutsch de de_DE`

  Scenario: create nav menus and assign them to locations

    When I run `wp pll menu create "Primary menu" primary`
    Then STDOUT should contain:
      """
      Success: Assigned location to menu.
      Success: Assigned location to menu.
      """

    When I run `wp pll menu create "Follow us!" social --porcelain`
    Then STDOUT should contain:
      """
      11 12
      """
