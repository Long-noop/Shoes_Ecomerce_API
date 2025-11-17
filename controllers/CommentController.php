<?php
require_once ROOT_PATH . '/core/Controller.php';

class CommentController extends Controller {
    private $commentModel;

    public function __construct() {
        parent::__construct();
        $this->commentModel = $this->model('Comment');
    }

    // Get comments for a resource (product, blog, etc.)
    public function index() {
        $resourceType = $this->request->get('resource_type', 'product');
        $resourceId = $this->request->get('resource_id');
        $page = $this->request->get('page', 1);
        $perPage = $this->request->get('per_page', 20);
        $offset = ($page - 1) * $perPage;

        if (!$resourceId) {
            $this->response->error('Resource ID is required', 400);
        }

        $comments = $this->commentModel->findByResource($resourceType, $resourceId, $perPage, $offset);
        $total = $this->commentModel->countByResource($resourceType, $resourceId);

        $this->response->paginate($comments, $total, $page, $perPage);
    }

    // Create comment
    public function store() {
        $user = $this->requireAuth();

        $validator = new Validator();
        $rules = [
            'resource_type' => 'required|in:product,blog',
            'resource_id' => 'required|numeric',
            'content' => 'required|min:3',
            'rating' => 'numeric'
        ];

        if (!$validator->validate($this->request->all(), $rules)) {
            $this->response->error('Validation failed', 422, $validator->getErrors());
        }

        $data = $this->request->all();
        $data['user_id'] = $user['user_id'];
        $data['status'] = 'approved'; // Auto-approve, or set to 'pending'
        $data['created_at'] = date('Y-m-d H:i:s');

        $commentId = $this->commentModel->create($data);

        if ($commentId) {
            $comment = $this->commentModel->findById($commentId);
            $this->response->success('Comment posted successfully', $comment, 201);
        } else {
            $this->response->error('Failed to post comment', 500);
        }
    }

    // Update comment
    public function update($id) {
        $user = $this->requireAuth();

        $comment = $this->commentModel->findById($id);
        if (!$comment) {
            $this->response->error('Comment not found', 404);
        }

        // Only owner or admin can update
        if ($user['user_id'] != $comment['user_id'] && $user['role'] !== 'admin') {
            $this->response->error('Forbidden', 403);
        }

        $data = $this->request->all();
        // $data['updated_at'] = date('Y-m-d H:i:s');

        $success = $this->commentModel->update($id, $data);

        if ($success) {
            $updated = $this->commentModel->findById($id);
            $this->response->success('Comment updated successfully', $updated);
        } else {
            $this->response->error('Failed to update comment', 500);
        }
    }

    // Delete comment
    public function destroy($id) {
        $user = $this->requireAuth();

        $comment = $this->commentModel->findById($id);
        if (!$comment) {
            $this->response->error('Comment not found', 404);
        }

        // Only owner or admin can delete
        if ($user['user_id'] != $comment['user_id'] && $user['role'] !== 'admin') {
            $this->response->error('Forbidden', 403);
        }

        $success = $this->commentModel->delete($id);

        if ($success) {
            $this->response->success('Comment deleted successfully',null);
        } else {
            $this->response->error('Failed to delete comment', 500);
        }
    }

    // Admin: Approve comment
    public function approve($id) {
        $this->requireAdmin();

        $comment = $this->commentModel->findById($id);
        if (!$comment) {
            $this->response->error('Comment not found', 404);
        }

        $success = $this->commentModel->update($id, ['status' => 'approved']);

        if ($success) {
            $this->response->success('Comment approved', null);
        } else {
            $this->response->error('Failed to approve comment', 500);
        }
    }

    // Admin: Reject comment
    public function reject($id) {
        $this->requireAdmin();

        $comment = $this->commentModel->findById($id);
        if (!$comment) {
            $this->response->error('Comment not found', 404);
        }

        $success = $this->commentModel->update($id, ['status' => 'rejected']);

        if ($success) {
            $this->response->success('Comment rejected', null);
        } else {
            $this->response->error('Failed to reject comment', 500);
        }
    }
}
?>