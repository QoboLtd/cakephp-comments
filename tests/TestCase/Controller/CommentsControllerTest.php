<?php
namespace Qobo\Comments\Test\TestCase\Controller;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

/**
 * Qobo\Comments\Test\App\Controller\CommentsController Test Case
 */
class CommentsControllerTest extends IntegrationTestCase
{
    public $fixtures = [
        'plugin.CakeDC/Users.users',
        'plugin.Qobo/Comments.comments'
    ];

    public function setUp()
    {
        parent::setUp();

        $this->Comments = TableRegistry::getTableLocator()->get('Qobo/Comments.Comments');

        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest'
            ]
        ]);
    }

    public function tearDown()
    {
        unset($this->Comments);

        parent::tearDown();
    }

    public function testIndexUnauthenticated()
    {
        $this->get('/comments/comments/index/Articles/00000000-0000-0000-0000-000000000001');

        $this->assertResponseCode(403);
    }

    public function testIndex()
    {
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000004']);

        $this->get('/comments/comments/index/Articles/00000000-0000-0000-0000-000000000001');

        $this->assertResponseCode(200);
        $this->assertJson($this->_getBodyAsString());

        $response = json_decode($this->_getBodyAsString());

        $this->assertTrue($response->success);
        $this->assertNotEmpty($response->data);
    }

    public function testAddUnauthenticated()
    {
        $this->post('/comments/comments/add');

        $this->assertResponseCode(403);
    }

    public function testAdd()
    {
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000004']);

        $data = [
            'content' => 'Hello World',
            'related_model' => 'Articles',
            'related_id' => '00000000-0000-0000-0000-000000000001'
        ];

        $this->post('/comments/comments/add', json_encode($data));

        $this->assertResponseCode(200);
        $this->assertJson($this->_getBodyAsString());

        $response = json_decode($this->_getBodyAsString());

        $this->assertTrue($response->success);

        $entity = $this->Comments->get($response->data);

        $this->assertEmpty(array_diff($data, $entity->toArray()));
    }

    public function testAddInvalidData()
    {
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000004']);

        $data = [
            'content' => '',
            'related_id' => true
        ];

        $this->post('/comments/comments/add', json_encode($data));

        $this->assertResponseCode(200);
        $this->assertJson($this->_getBodyAsString());

        $response = json_decode($this->_getBodyAsString());

        $this->assertFalse($response->success);

        $this->assertContains('content', $response->error);
        $this->assertContains('related_model', $response->error);
        $this->assertContains('related_id', $response->error);
    }

    public function testDeleteUnauthenticated()
    {
        $this->delete('/comments/comments/delete/00000000-0000-0000-0000-000000000001');

        $this->assertResponseCode(403);
    }

    public function testDelete()
    {
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000004']);

        $id = '00000000-0000-0000-0000-000000000001';
        $query = $this->Comments->find('all')->where([$this->Comments->getPrimaryKey() => $id]);

        $this->assertFalse($query->isEmpty());

        $this->post('/comments/comments/delete/' . $id);

        $this->assertResponseCode(200);
        $this->assertJson($this->_getBodyAsString());

        $query = $this->Comments->find('all')->where([$this->Comments->getPrimaryKey() => $id]);
        $response = json_decode($this->_getBodyAsString());

        $this->assertTrue($response->success);
        $this->assertTrue($query->isEmpty());
    }

    public function testDeleteFromOtherUser()
    {
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000004']);

        $id = '00000000-0000-0000-0000-000000000002';

        $this->post('/comments/comments/delete/' . $id);

        $this->assertResponseCode(200);
        $this->assertJson($this->_getBodyAsString());

        $query = $this->Comments->find('all')->where([$this->Comments->getPrimaryKey() => $id]);
        $response = json_decode($this->_getBodyAsString());

        $this->assertFalse($response->success);
        $this->assertEquals('Cannot delete comment created by another user', $response->error);
        $this->assertFalse($query->isEmpty());
    }
}
