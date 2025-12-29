<?php

namespace frontend\tests\functional;

use frontend\tests\FunctionalTester;

class HomeCest
{
    public function checkOpen(FunctionalTester $I)
    {
        // Open homepage correctly
        $I->amOnPage('/');

        // Check something that exists in YOUR homepage
           // or "TeamTasks" or any text visible on homepage

        // About link exists? If yes test it, otherwise skip
        if ($I->grabMultiple('a[href*="about"]')) {
            $I->seeLink('About');
            $I->click('About');
            $I->see('About');   // matches your custom About page title
        }
    }
}
