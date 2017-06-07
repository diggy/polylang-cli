@pll-post
Feature: Manage posts and their translations

  Background:
    Given a WP install

  Scenario: Create posts and their translations

    When I run `wp pll lang create de de de_DE`
    And I run `wp pll post create --post_type=page --post_title="Blog"`
    Then STDOUT should contain:
    """
    Success: Created and linked 2 posts of the page post type.
    """

    When I run `wp pll post create --post_type=page --post_title="Home" --porcelain`
    Then STDOUT should contain:
    """
    7 8
    """

    When I try `echo '{"de":{"post_title":"German title","post_content":"German content"}}' | wp pll post create --stdin --post_status=publish --post_type=post`
    Then STDERR should be:
    """
    Error: Please provide input for all languages: nl, de
    """
    And the return code should be 1

    When I run `echo '{"nl":{"post_title":"Dutch title","post_content":"Dutch content"},"de":{"post_title":"German title","post_content":"German content"}}' | wp pll post create --stdin --post_status=publish --post_type=post`
    Then STDOUT should contain:
    """
    Success: Created and linked 2 posts of the post post type
    """

    When I run `echo '{"nl":{"post_title":"Dutch title","post_content":"Dutch content"},"de":{"post_title":"German title","post_content":"German content"},"tlh":{"post_title":"Klingon title","post_content":"Klingon content"}}' | wp pll post create --stdin --post_status=publish --post_type=post`
    Then STDOUT should contain:
    """
    Success: Created and linked 2 posts of the post post type.
    """
    And STDERR should be:
    """
    Warning: tlh is not a valid language.
    """

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

    When I run `wp pll lang create de de de_DE`
    And I run `wp pll post generate --count=10`
    And I run `wp pll post delete 10`
    Then STDOUT should contain:
    """
    Success: Trashed post 10 11.
    """
    And the return code should be 0
