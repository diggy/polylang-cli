@pll-post
Feature: Manage posts and their translations

  Background:
    Given a WP install

  Scenario: Delete posts and their translations

    When I run `wp pll post delete 1`
    Then STDOUT should contain:
    """
    Success: Trashed post 1.
    """

    When I run `wp pll post delete 1`
    Then STDOUT should contain:
    """
    Success: Deleted post 1.
    """

    When I run `wp pll post delete 2 --force`
    Then STDOUT should contain:
    """
    Success: Deleted post 2.
    """

    When I run `wp pll lang create de de de_DE && wp pll post generate --count=10`
    Then STDOUT should not be empty

    When I run `wp pll post delete 10`
    Then STDOUT should contain:
    """
    Success: Trashed post 10 11.
    """
    And the return code should be 0
