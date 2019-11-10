<?php

class LockTest extends \Codeception\TestCase\WPTestCase
{
    /**
     * @var \WpunitTester
     */
    protected $tester;

    public function setUp(): void
    {
        // Before...
        parent::setUp();

        // Your set up methods here.
    }

    public function tearDown(): void
    {
        // Your tear down methods here.

        // Then...
        parent::tearDown();
    }

    public function testQueryPostTypeIsRegistered()
    {
        $post_types = get_post_types();
        $this->assertArrayHasKey('graphql_query', $post_types);
    }


    public function testCanCallFromPHP()
    {
        $post = static::factory()->post->create_and_get(['post_title' => 'test']);
        $result = graphql([
            'query' => ' {
                posts {
                    nodes {
                        title
                    }
                }
            }'
        ]);

        $expected = [
            'data' => [
                'posts' => [
                    'nodes' => [
                        [
                            'title' => 'test',
                        ]
                    ]
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

}
