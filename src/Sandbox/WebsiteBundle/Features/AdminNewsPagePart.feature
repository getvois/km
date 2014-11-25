@clean_session @NewsPagePart
Feature: NewsPagePart
  Make use of pages and pageparts
  As an admin user
  User has to create, update, delete pageparts

  Background:
    Given I log in as "admin"

  @javascript
  Scenario: Fully test the pagepart
    Given I am on the admin home page

    # create a BehatTestPage and publish it
    Given I add behattestpage "BehatTestPage"
    And I save the current page
    Then I should see "has been edited"
    Given I publish the current page
    Then I should see "has been published"

    ############### "ContentPage1" - "Content page" - "main" start ###############

    # create a new ContentPage page as sub page of the BehatTestPage
    Then I go to admin page "BehatTestPage"
    And I add contentpage "ContentPage1"
    Then I should see "ContentPage1"

    # fill in page properties

    # change the pagetemplate
    Then I change page template "Content page"
    Then I should see "has been edited"

    # add the pagepart
    And I add pp "News" in section "main"
    And I wait 2 seconds

    And I fill in spaced field "Title" with "doloribus"
    And I fill in spaced field "Sub title" with "dolor"
    And I fill in spaced field "Short desc" with "dolorem"
    And I fill in pp cke field "text" with "<b>Hic ad non fugiat illo commodi magnam voluptas nihil.</b>"
    And I fill in pp image field "Image" with "1"
    And I fill in spaced field "Image alt text" with "et"

    # save an publish the page
    Given I save the current page
    Then I should see "has been edited"
    Given I publish the current page
    Then I should see "has been published"

    # check the public page
    Given I go to page "/behattestpage/contentpage1"
    Then I should not see "page you requested could not be found"

    And I should see "doloribus"
    And I should see "dolor"
    And I should see "dolorem"
    And I should see "Hic ad non fugiat illo commodi magnam voluptas nihil."
    And I should see image "/uploads/media/5463827e1cdbe.png?v1" with alt text "et"

    # edit the pagepart in the admin interface
    Then I go to admin page "BehatTestPage"
    Then I click on admin page "ContentPage1"
    And I edit pagepart "News"
    And I wait 2 seconds

    And I fill in spaced field "Title" with "harum"
    And I fill in spaced field "Sub title" with "ullam"
    And I fill in spaced field "Short desc" with "quo"
    And I fill in pp cke field "text" with "<b>Quam quisquam dolorem esse et vitae non qui.</b>"
    And I fill in pp image field "Image" with "2"
    And I fill in spaced field "Image alt text" with "nulla"

    # save an publish the page
    Given I save the current page
    Then I should see "has been edited"

    # check the public page
    Given I go to page "/behattestpage/contentpage1"
    Then I should not see "page you requested could not be found"

    And I should see "harum"
    And I should see "ullam"
    And I should see "quo"
    And I should see "Quam quisquam dolorem esse et vitae non qui."
    And I should see image "/uploads/media/5463827e1e379.png?v1" with alt text "nulla"

    # delete the pagepart
    Then I go to admin page "BehatTestPage"
    Then I click on admin page "ContentPage1"
    And I delete pagepart "News"
    Given I save the current page
    Then I should see "has been edited"

    # check the public page that the pagepart is deleted
    Given I go to page "/behattestpage/contentpage1"
    Then I should not see "page you requested could not be found"

    And I should not see "harum"
    And I should not see "ullam"
    And I should not see "quo"
    And I should not see "Quam quisquam dolorem esse et vitae non qui."
     #todo

    ############### "ContentPage1" - "Content page" - "main" end ###############


  # delete the BehatTestPage
  @javascript
  Scenario: Delete the BehatTestPage
    Given I delete page "BehatTestPage"
    Then I should see "The page is deleted"