<?php
require_once ROOT_PATH . '/core/Controller.php';

class CategoryController extends Controller {
    private $categoryModel;

    public function __construct() {
        parent::__construct();
        $this->categoryModel = $this->model('Category');
    }

    // Get all categories
    public function index() {
        $withProductCount = $this->request->get('with_products', false);
        
        if ($withProductCount) {
            $categories = $this->categoryModel->findWithProductCount();
        } else {
            $categories = $this->categoryModel->findAll();
        }

        $this->response->success('Categories list', $categories);
    }

    // Get single category
    public function show($id) {
        $category = $this->categoryModel->findById($id);

        if (!$category) {
            $this->response->error('Category not found', 404);
        }

        // Get products in this category
        if ($this->request->get('with_products', false)) {
            $productModel = $this->model('Product');
            $products = $productModel->search('', $id);
            $category['products'] = $products;
        }

        $this->response->success('Category details', $category);
    }

    // Admin: Create category
    public function store() {
        $this->requireAdmin();

        $validator = new Validator();
        $rules = [
            'name' => 'required',
            // 'slug' => 'required'
        ];

        if (!$validator->validate($this->request->all(), $rules)) {
            $this->response->error('Validation failed', 422, $validator->getErrors());
        }

        // Check if slug exists
        $existing = $this->categoryModel->findBySlug($this->request->get('slug'));
        if ($existing) {
            $this->response->error('Slug already exists', 400);
        }

        $data = $this->request->all();
        // $data['created_at'] = date('Y-m-d H:i:s');

        $categoryId = $this->categoryModel->create($data);

        if ($categoryId) {
            $category = $this->categoryModel->findById($categoryId);
            $this->response->success('Category created successfully', $category, 201);
        } else {
            $this->response->error('Failed to create category', 500);
        }
    }

    // Admin: Update category
    public function update($id) {
        $this->requireAdmin();

        $category = $this->categoryModel->findById($id);
        if (!$category) {
            $this->response->error('Category not found', 404);
        }

        // Check if slug is being changed and if it's already taken
        if ($this->request->has('slug') && $this->request->get('slug') !== $category['slug']) {
            $existing = $this->categoryModel->findBySlug($this->request->get('slug'));
            if ($existing) {
                $this->response->error('Slug already exists', 400);
            }
        }

        $data = $this->request->all();
        // $data['updated_at'] = date('Y-m-d H:i:s');

        $success = $this->categoryModel->update($id, $data);

        if ($success) {
            $updated = $this->categoryModel->findById($id);
            $this->response->success('Category updated successfully', $updated);
        } else {
            $this->response->error('Failed to update category', 500);
        }
    }

    // Admin: Delete category
    public function destroy($id) {
        $this->requireAdmin();

        $category = $this->categoryModel->findById($id);
        if (!$category) {
            $this->response->error('Category not found', 404);
        }

        // Check if category has products
        $productModel = $this->model('Product');
        $productCount = $productModel->count("category_id = $id");

        if ($productCount > 0) {
            $this->response->error('Cannot delete category with products', 400);
        }

        $success = $this->categoryModel->delete($id);

        if ($success) {
            $this->response->success('Category deleted successfully', null  );
        } else {
            $this->response->error('Failed to delete category', 500);
        }
    }
}
?>