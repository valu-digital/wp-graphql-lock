<?php

class LockCest {

    public function testCanSendPOST( FunctionalTester $I ) {
        $I->havePageInDatabase(['post_title' => 'Test page']);

        $query = '
        {
            pages {
                nodes {
                    title
                }
            }
        }';

        $I->haveHttpHeader( 'Content-Type', 'application/json' );
        $I->sendPOST( '/graphql', [
            'query' => $query,
        ] );
        $I->seeResponseCodeIs( 200 );
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'data' => [
                'pages' => [
                    'nodes' => [
                        [
                            'title' => 'Test page'
                        ]
                    ]
                ]
            ],
        ]);

    }

    public function testCannotSendPostWhenLocked( FunctionalTester $I ) {

        $I->haveOptionInDatabase( 'graphql_lock_locked', true );
        $query = '
        {
            pages {
                nodes {
                    title
                }
            }
        }';

        $I->haveHttpHeader( 'Content-Type', 'application/json' );
        $I->sendPOST( '/graphql', [
            'query' => $query,
        ] );
        $I->dontSeeResponseCodeIs( 200 );
        $I->seeResponseContains('is in lock mode');
    }

    public function testCanSendPOSTWhenLockedButIsAdmin( FunctionalTester $I ) {
        $I->havePageInDatabase(['post_title' => 'Test page']);
        $I->haveOptionInDatabase( 'graphql_lock_locked', true );

        $query = '
        {
            pages {
                nodes {
                    title
                }
            }
        }';

        $I->loginAsAdmin();

        $I->haveHttpHeader( 'Content-Type', 'application/json' );
        $I->sendPOST( '/graphql', [
            'query' => $query,
        ] );
        $I->seeResponseCodeIs( 200 );
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'data' => [
                'pages' => [
                    'nodes' => [
                        [
                            'title' => 'Test page'
                        ]
                    ]
                ]
            ],
        ]);

    }
}