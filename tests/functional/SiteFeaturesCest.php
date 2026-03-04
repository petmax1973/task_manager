<?php

namespace tests\functional;

use FunctionalTester;

class SiteFeaturesCest
{
    public function _before(FunctionalTester $I)
    {
    }

    public function testLanguageSwitching(FunctionalTester $I)
    {
        // Start on task index (default is Italian)
        $I->amOnPage(['task/index']);
        $I->see('Nuovo Task'); // Should see Italian text
        
        // Switch to English
        $I->click('English');
        $I->see('New Issue'); // Should see English text (if implemented)
        
        // Switch back to Italian
        $I->click('Italiano');
        $I->see('Nuovo Task'); // Should see Italian text again
    }

    public function testLanguagePersistence(FunctionalTester $I)
    {
        // Switch to English
        $I->amOnPage(['/site/language', 'lang' => 'en-US']);
        
        // Navigate to different page
        $I->amOnPage(['task/index']);
        
        // Language should be persisted via cookie
        // This test might need adjustment based on actual implementation
    }

    public function testThemeSwitching(FunctionalTester $I)
    {
        // Start on any page
        $I->amOnPage(['task/index']);
        
        // Check initial theme (should be light by default)
        $I->seeElement('body.theme-light');
        
        // Switch to dark theme
        $I->click('.theme-toggle a'); // Theme toggle button
        
        // Should now be dark theme
        $I->seeElement('body.theme-dark');
        
        // Switch back to light theme
        $I->click('.theme-toggle a');
        
        // Should be light theme again
        $I->seeElement('body.theme-light');
    }

    public function testThemePersistence(FunctionalTester $I)
    {
        // Set dark theme
        $I->amOnPage(['/site/theme', 'theme' => 'dark']);
        
        // Navigate to different page
        $I->amOnPage(['task/index']);
        
        // Theme should be persisted
        $I->seeElement('body.theme-dark');
        
        // Set light theme
        $I->amOnPage(['/site/theme', 'theme' => 'light']);
        $I->amOnPage(['task/index']);
        $I->seeElement('body.theme-light');
    }

    public function testAboutPage(FunctionalTester $I)
    {
        $I->amOnPage(['site/about']);
        $I->see('This is the About page');
        
        // Should have proper layout
        $I->seeElement('nav.navbar'); // Navigation should be present
        $I->seeElement('footer'); // Footer should be present
    }

    public function testHomepageRedirect(FunctionalTester $I)
    {
        // Homepage should redirect to task/index
        $I->amOnPage(['site/index']);
        $I->seeCurrentUrlMatches('/task/');
    }

    public function testContactPage(FunctionalTester $I)
    {
        $I->amOnPage(['site/contact']);
        
        // Should show contact form
        $I->seeElement('form'); // Contact form should be present
        $I->see('Contact'); // Page title/header
        
        // Test form validation
        $I->submitForm('form', [
            'ContactForm[name]' => '',
            'ContactForm[email]' => 'invalid-email',
            'ContactForm[subject]' => '',
            'ContactForm[body]' => ''
        ]);
        
        // Should stay on contact page with validation errors
        $I->seeCurrentUrlMatches('/contact/');
    }

    public function testNavbarElements(FunctionalTester $I)
    {
        $I->amOnPage(['task/index']);
        
        // Check navbar elements
        $I->seeElement('nav.navbar');
        $I->see('Task Manager'); // Brand/title
        
        // Language switcher
        $I->see('Italiano');
        $I->see('English');
        
        // Theme switcher (icon)
        $I->seeElement('.theme-toggle');
        $I->seeElement('i.fas.fa-moon, i.fas.fa-sun'); // Should see moon or sun icon
    }

    public function testFooter(FunctionalTester $I)
    {
        $I->amOnPage(['task/index']);
        
        // Check footer elements
        $I->seeElement('footer');
        $I->see('Powered by'); // Should contain Yii framework attribution
    }

    public function testResponsiveLayout(FunctionalTester $I)
    {
        $I->amOnPage(['task/index']);
        
        // Check Bootstrap classes for responsive layout
        $I->seeElement('.container');
        $I->seeElement('.navbar-brand');
        
        // GridView should be responsive
        $I->seeElement('.table-responsive, .grid-view');
    }

    public function testErrorHandling(FunctionalTester $I)
    {
        // Test 404 error
        $I->amOnPage(['task/view', 'id' => 99999]);
        $I->seeResponseCodeIs(404);
        
        // Test with invalid task ID format
        $I->amOnPage('/task/view?id=invalid');
        $I->seeResponseCodeIsClientError(); // Should handle gracefully
    }
}