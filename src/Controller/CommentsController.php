<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Qobo\Comments\Controller;

use App\Controller\AppController;
use Cake\Event\Event;

/**
 * Comments Controller
 *
 * @property \Qobo\Comments\Model\Table\CommentsTable $Comments
 */
class CommentsController extends AppController
{
    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('RequestHandler');

        // allow only ajax requests
        $this->request->allowMethod(['ajax']);
    }

    /**
     * Index method.
     *
     * @return \Cake\Http\Response|void|null
     */
    public function index()
    {
        $this->request->allowMethod(['get']);

        /**
         * @var \Cake\ORM\Query $data
         */
        $data = $this->Comments->find('all')
            ->where([
                'related_model' => $this->request->getParam('pass.0'),
                'related_id' => $this->request->getParam('pass.1'),
            ])
            ->contain('Author');
        $data = $data->all();

        $this->set('success', true);
        $this->set('data', $data);
        $this->set('_serialize', ['success', 'data']);
    }

    /**
     * Add method.
     *
     * @return \Cake\Http\Response|void|null Redirects on successful add, renders view otherwise
     */
    public function add()
    {
        $this->request->allowMethod(['post']);

        $data = $this->request->getData();
        $data = is_array($data) ? $data : [];
        /**
         * @var array $data
         */
        $data = array_merge($data, ['user_id' => $this->Auth->user('id')]);

        $comment = $this->Comments->newEntity();
        $comment = $this->Comments->patchEntity($comment, $data);

        $success = (bool)$this->Comments->save($comment);

        $this->set('success', $success);
        $success ?
            $this->set('data', $comment->get('id')) :
            $this->set('error', sprintf('Failed to save comment: %s', json_encode($comment->getErrors())));

        $this->set('_serialize', ['success', 'data', 'error']);
    }

    /**
     * Delete method.
     *
     * @param string $id Comment id
     * @return \Cake\Http\Response|void|null Redirects to index
     */
    public function delete(string $id)
    {
        $this->request->allowMethod(['post', 'delete']);

        $comment = $this->Comments->get($id, ['contain' => 'Author']);
        if ($this->Auth->user('id') !== $comment->get('author')->get('id')) {
            $this->set('success', false);
            $this->set('error', 'Cannot delete comment created by another user');
            $this->set('_serialize', ['success', 'error']);

            return;
        }

        $success = $this->Comments->delete($comment);

        $this->set('success', $success);
        $success ? $this->set('data', []) : $this->set('error', 'Failed to delete comment');

        $this->set('_serialize', ['success', 'data', 'error']);
    }
}
