<?php
require_once ROOT_PATH . '/core/Controller.php';

class BlogController extends Controller {
    private $blogModel;

    public function __construct() {
        parent::__construct();
        $this->blogModel = $this->model('Blog');
    }

    // Get all blogs (published only for public)
    public function index() {
        $page = $this->request->get('page', 1);
        $perPage = $this->request->get('per_page', DEFAULT_PAGE_SIZE);
        $offset = ($page - 1) * $perPage;
        $keyword = $this->request->get('keyword', '');

        // Check if user is admin
        $isAdmin = false;
        try {
            $user = $this->requireAuth();
            $isAdmin = $user['role'] === 'admin';
        } catch (Exception $e) {
            // Not authenticated, show only published
        }

        if ($isAdmin) {
            // Admin sees all posts
            $blogs = $this->blogModel->search($keyword, $perPage, $offset);
            $total = $this->blogModel->count();
        } else {
            // Public sees only published
            $blogs = $this->blogModel->getPublished($perPage, $offset);
            $total = $this->blogModel->count("status = 'publish'");
        }

        $this->response->paginate($blogs, $total, $page, $perPage);
    }

    // Get single blog
    public function show($id) {
        $blog = $this->blogModel->findById($id);

        if (!$blog) {
            $this->response->error('Blog not found', 404);
        }

        // Check if blog is published or user is admin
        if ($blog['status'] !== 'published') {
            try {
                $user = $this->requireAuth();
                if ($user['role'] !== 'admin') {
                    $this->response->error('Blog not found', 404);
                }
            } catch (Exception $e) {
                $this->response->error('Blog not found', 404);
            }
        }

        // Increment views
        // $this->blogModel->incrementViews($id);

        $this->response->success('Blog details', $blog);
    }

    // Admin: Create blog
    public function store() {
        $this->requireAdmin();

        $validator = new Validator();
        $rules = [
            'title' => 'required',
            // 'slug' => 'required',
            'content' => 'required',
            'status' => 'in:draft,publish'
        ];

        if (!$validator->validate($this->request->all(), $rules)) {
            $this->response->error('Validation failed', 422, $validator->getErrors());
        }

        // Check if slug exists
        // $existing = $this->blogModel->findBySlug($this->request->get('slug'));
        // if ($existing) {
        //     $this->response->error('Slug already exists', 400);
        // }

        $data = $this->request->all();
        $data['author_id'] = $this->requireAuth()['user_id'];
        $data['created_at'] = date('Y-m-d H:i:s');

        $blogId = $this->blogModel->create($data);

        if ($blogId) {
            $blog = $this->blogModel->findById($blogId);
            $this->response->success('Blog created successfully', $blog, 201);
        } else {
            $this->response->error('Failed to create blog', 500);
        }
    }

    // Admin: Update blog
    public function update($id) {
        $this->requireAdmin();

        $blog = $this->blogModel->findById($id);
        if (!$blog) {
            $this->response->error('Blog not found', 404);
        }

        // Check if slug is being changed and already exists
        // if ($this->request->has('slug') && $this->request->get('slug') !== $blog['slug']) {
        //     $existing = $this->blogModel->findBySlug($this->request->get('slug'));
        //     if ($existing) {
        //         $this->response->error('Slug already exists', 400);
        //     }
        // }

        $data = $this->request->all();
        // $data['updated_at'] = date('Y-m-d H:i:s');

        $success = $this->blogModel->update($id, $data);

        if ($success) {
            $updated = $this->blogModel->findById($id);
            $this->response->success('Blog updated successfully', $updated);
        } else {
            $this->response->error('Failed to update blog', 500);
        }
    }

    // Admin: Delete blog
    public function destroy($id) {
        $this->requireAdmin();

        $blog = $this->blogModel->findById($id);
        if (!$blog) {
            $this->response->error('Blog not found', 404);
        }

        $success = $this->blogModel->delete($id);

        if ($success) {
            $this->response->success('Blog deleted successfully', null);
        } else {
            $this->response->error('Failed to delete blog', 500);
        }
    }
}
?>