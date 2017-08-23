@pll-post
Feature: Manage posts and their translations

  Background:
    Given a WP install
    And I run `wp plugin install polylang`
    And I run `wp plugin activate polylang`
    And I run `wp pll lang create Dutch nl nl_NL`
    And I run `wp pll doctor translate`

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

  @pll-post-update
  Scenario: Update posts and their translations

    When I run `wp pll option sync comment_status`
    And I run `wp pll lang create de de de_DE`
    And I run `wp pll post duplicate 1`
    And I run `wp pll post update 1 --comment_status="closed"`
    And I run `wp post list --fields=ID,comment_status`
    Then STDOUT should be a table containing rows:
    | ID | comment_status |
    | 5  | closed         |
    | 1  | closed         |
    And STDERR should be empty

    When I run `wp pll post update 5 --comment_status="open"`
    And I run `wp post list --fields=ID,comment_status`
    Then STDOUT should be a table containing rows:
    | ID | comment_status |
    | 5  | open           |
    | 1  | open           |
    And STDERR should be empty

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

  @pll-post-duplicate
  Scenario: Duplicate a post to one or more languages

    When I run `wp pll lang create de de de_DE`
    And I run `wp post create --post_type=page --post_title='Just another page' --porcelain`
    And save STDOUT as {POST_ID}
    And I run `wp pll doctor translate`
    And I run `wp pll post duplicate {POST_ID}`
    Then STDOUT should contain:
    """
    Success: Created post 6 (de) < post 5 (nl)
    """
    And the return code should be 0

    When I run `wp pll lang create es es es_ES`
    And I run `wp pll post duplicate {POST_ID}`
    Then STDOUT should contain:
    """
    Success: Updated post 6 (de) < post 5 (nl)
    Success: Created post 9 (es) < post 5 (nl)
    """
    And the return code should be 0

    When I run `wp pll post duplicate {POST_ID} de,es,r2d2`
    Then STDOUT should contain:
    """
    Success: Updated post 6 (de) < post 5 (nl)
    Success: Updated post 9 (es) < post 5 (nl)
    """
    And STDERR should be:
    """
    Warning: r2d2 is not a valid language.
    """
    And the return code should be 0

    When I run `wp pll post duplicate {POST_ID} nl`
    Then STDERR should be:
    """
    Warning: Post 5 (nl) cannot be duplicated to itself.
    """
    And the return code should be 0
