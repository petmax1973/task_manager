<?php

namespace tests\unit\controllers;

use app\controllers\SiteController;
use Codeception\Test\Unit;
use Yii;
use yii\web\Cookie;

class SiteControllerTest extends Unit
{
    /**
     * @var SiteController
     */
    protected $controller;

    protected function _before()
    {
        $this->controller = new SiteController('site', Yii::$app);
    }

    protected function _after()
    {
    }

    public function testActionIndex()
    {
        $result = $this->controller->actionIndex();
        
        // Should redirect to task/index
        $this->assertInstanceOf('yii\web\Response', $result);
    }

    public function testActionAbout()
    {
        $result = $this->controller->actionAbout();
        
        // Should return view result
        $this->assertIsString($result);
    }

    public function testActionLanguageValidLanguages()
    {
        // Test Italian
        $result = $this->controller->actionLanguage('it-IT');
        $this->assertInstanceOf('yii\web\Response', $result);
        
        // Check if cookie is set (would need mocking for full test)
        
        // Test English  
        $result = $this->controller->actionLanguage('en-US');
        $this->assertInstanceOf('yii\web\Response', $result);
    }

    public function testActionLanguageInvalidLanguage()
    {
        $result = $this->controller->actionLanguage('invalid-lang');
        
        // Should still redirect but not set cookie for invalid language
        $this->assertInstanceOf('yii\web\Response', $result);
    }

    public function testActionThemeValidThemes()
    {
        // Test light theme
        $result = $this->controller->actionTheme('light');
        $this->assertInstanceOf('yii\web\Response', $result);
        
        // Test dark theme
        $result = $this->controller->actionTheme('dark');
        $this->assertInstanceOf('yii\web\Response', $result);
    }

    public function testActionThemeInvalidTheme()
    {
        $result = $this->controller->actionTheme('invalid-theme');
        
        // Should still redirect but not set cookie for invalid theme
        $this->assertInstanceOf('yii\web\Response', $result);
    }

    public function testActionsConfiguration()
    {
        $actions = $this->controller->actions();
        
        // Should have captcha and error actions configured
        $this->assertIsArray($actions);
        $this->assertArrayHasKey('captcha', $actions);
        $this->assertArrayHasKey('error', $actions);
    }

    public function testActionContact()
    {
        // Test GET request (show form)
        Yii::$app->request->setIsPost(false);
        $result = $this->controller->actionContact();
        
        // Should return view with form
        $this->assertIsString($result);
    }
}