@pll-term
Feature: Manage WordPress taxonomy terms and their translations.

  Background:
    Given a WP install
    And I run `wp plugin install polylang`
    And I run `wp plugin activate polylang`
    And I run `wp pll lang create Dutch nl nl_NL`
    And I run `wp pll doctor translate`

  Scenario: Delete a term and its translations

    When I try `wp pll term delete chewbacca 42`
    Then STDERR should contain:
    """
    Error: chewbacca is not a registered taxonomy.
    """
    And the return code should be 1

    When I try `wp pll term delete link_category 101`
    Then STDERR should be:
    """
    Error: Polylang does not manage languages and translations for this taxonomy.
    """

    When I run `wp pll lang create de de de_DE`
    Then STDOUT should not be empty

    When I run `wp pll term generate post_tag --count=3 --format=ids`
    Then STDOUT should not be empty

    When I run `wp pll term delete post_tag 9`
    Then STDOUT should contain:
    """
    Deleted post_tag 9.
    Deleted post_tag 10.
    Success: Deleted 2 of 2 terms.
    """
    And the return code should be 0

    When I run `wp pll term delete post_tag 12`
    Then STDOUT should contain:
    """
    Deleted post_tag 12.
    Deleted post_tag 11.
    Success: Deleted 2 of 2 terms.
    """
    And the return code should be 0

  @pll-term-list
  Scenario: List taxonomy terms for a language

    When I run `wp pll lang create de de de_DE && wp pll term generate post_tag --count=3 --format=ids`
    Then STDOUT should not be empty

    When I run `wp pll term list post_tag nl --format=ids`
    Then STDOUT should contain:
    """
    11 13 9
    """
    And the return code should be 0

    When I run `wp pll term list post_tag de --format=ids`
    Then STDOUT should contain:
    """
    12 14 10
    """
    And the return code should be 0

  @pll-term-duplicate
  Scenario: Duplicate taxonomy term to one or more languages

    When I run `wp pll lang create de de de_DE`
    And I run `wp term create post_tag "Just a tag" --porcelain`
    And save STDOUT as {TERM_ID}
    And I run `wp pll doctor translate`
    Then STDOUT should not be empty

    When I run `wp pll term duplicate post_tag {TERM_ID}`
    Then STDOUT should contain:
    """
    Success: Created term 11 (de) < term {TERM_ID} (nl)
    """
    And the return code should be 0
