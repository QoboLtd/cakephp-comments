<?php
namespace Qobo\Comments\Test\TestCase\Model\Table;

use Cake\Datasource\EntityInterface;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\RulesChecker;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Validation\Validator;
use Cms\Model\Entity\Site;
use Qobo\Comments\Model\Table\CommentsTable;

/**
 * Qobo\Comments\Model\Table\CommentsTable Test Case
 */
class CommentsTableTest extends TestCase
{
    public $fixtures = [
        'plugin.CakeDC/Users.Users',
        'plugin.Qobo/Comments.Comments',
    ];

    /**
     * Test subject
     *
     * @var \Qobo\Comments\Model\Table\CommentsTable
     */
    public $Comments;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        /**
         * @var \Qobo\Comments\Model\Table\CommentsTable $table
         */
        $table = TableRegistry::getTableLocator()->get('Qobo/Comments.Comments');
        $this->Comments = $table;
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Comments);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize(): void
    {
        $this->assertInstanceOf(CommentsTable::class, $this->Comments);

        $this->assertEquals('qobo_comments', $this->Comments->getTable());
        $this->assertEquals('id', $this->Comments->getPrimaryKey());
        $this->assertEquals('id', $this->Comments->getDisplayField());

        $this->assertTrue($this->Comments->hasBehavior('Timestamp'));
        $this->assertTrue($this->Comments->hasBehavior('Tree'));
        $this->assertTrue($this->Comments->hasBehavior('Trash'));

        $this->assertInstanceOf(BelongsTo::class, $this->Comments->getAssociation('Author'));
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $this->assertInstanceOf(Validator::class, $this->Comments->validationDefault(new Validator()));
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules(): void
    {
        $this->assertInstanceOf(RulesChecker::class, $this->Comments->buildRules(new RulesChecker()));
    }

    public function testSave(): void
    {
        $data = [
            'content' => 'Hello World',
            'related_model' => 'Articles',
            'related_id' => '00000000-0000-0000-0000-000000000001',
            'user_id' => '00000000-0000-0000-0000-000000000001',
        ];

        $entity = $this->Comments->newEntity();
        $entity = $this->Comments->patchEntity($entity, $data);

        $this->assertInstanceOf(EntityInterface::class, $this->Comments->save($entity));
        $this->assertEmpty(array_diff($data, $entity->toArray()));
    }
}
