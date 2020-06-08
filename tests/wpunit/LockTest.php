<?php

use WPGraphQL\Extensions\Lock\Settings;

class LockTest extends \Codeception\TestCase\WPTestCase
{
    /**
     * @var \WpunitTester
     */
    protected $tester;

    private $recording;

    private function enableRecording(): void
    {
        $recording_option_name = Settings::get_option_name('recording');
        $this->recording = get_option($recording_option_name);
        update_option($recording_option_name, true);
    }

    private function resetRecording(): void
    {
        $recording_option_name = Settings::get_option_name('recording');
        update_option($recording_option_name, $this->recording);
        $this->recording = null;
    }

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

    public function testCanSavePost()
    {
        $this->enableRecording();

        $lockLoader = new \WPGraphQL\Extensions\Lock\Loader();
        $query_id = 'testCanSavePost';
        $query = 'query testCanSavePost {
            posts {
                nodes {
                    title
                }
            }
        }';
        $operation_name = 'testCanSavePost';
        $lockLoader->save($query_id, $query, $operation_name);

        $this->resetRecording();

        $result = $lockLoader->load($query_id, $operation_name);

        $expected = $query;

        $this->assertEquals($expected, $result);
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
