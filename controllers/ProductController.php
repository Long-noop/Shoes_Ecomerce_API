<?php
require_once ROOT_PATH . '/core/Controller.php';

class ProductController extends Controller {
    private $productModel;

    public function __construct() {
        parent::__construct();
        $this->productModel = $this->model('Product');
    }

    public function index() {
        $page = $this->request->get('page', 1);
        $perPage = min($this->request->get('per_page', DEFAULT_PAGE_SIZE), MAX_PAGE_SIZE);
        $offset = ($page - 1) * $perPage;

        $keyword = $this->request->get('keyword', '');
        $categoryId = $this->request->get('category_id');
        $minPrice = $this->request->get('min_price');
        $maxPrice = $this->request->get('max_price');

        $products = $this->productModel->search($keyword, $categoryId, $minPrice, $maxPrice, $perPage, $offset);
        $total = $this->productModel->countSearch($keyword, $categoryId, $minPrice, $maxPrice);

        $this->response->paginate($products, $total, $page, $perPage);
    }

    public function show($id) {
        $product = $this->productModel->findWithCategory($id);

        if (!$product) {
            $this->response->error('Product not found', 404);
        }

        $this->response->success('Product details', $product);
    }

    public function store() {
        $this->requireAdmin();

        $validator = new Validator();
        $rules = [
            'name' => 'required',
            'price' => 'required|numeric',
            'category_id' => 'required|numeric',
            'stock' => 'required|numeric'
        ];

        if (!$validator->validate($this->request->all(), $rules)) {
            $this->response->error('Validation failed', 422, $validator->getErrors());
        }

        $data = $this->request->all();
        $data['created_at'] = date('Y-m-d H:i:s');

        $productId = $this->productModel->create($data);

        if ($productId) {
            $product = $this->productModel->findById($productId);
            $this->response->success('Product created', $product, 201);
        } else {
            $this->response->error('Failed to create product', 500);
        }
    }

    public function update($id) {
        $this->requireAdmin();

        $product = $this->productModel->findById($id);
        if (!$product) {
            $this->response->error('Product not found', 404);
        }

        $data = $this->request->all();
        $data['updated_at'] = date('Y-m-d H:i:s');

        $success = $this->productModel->update($id, $data);

        if ($success) {
            $updated = $this->productModel->findById($id);
            $this->response->success('Product updated', $updated);
        } else {
            $this->response->error('Failed to update product', 500);
        }
    }

    public function destroy($id) {
        $this->requireAdmin();

        $product = $this->productModel->findById($id);
        if (!$product) {
            $this->response->error('Product not found', 404);
        }

        $success = $this->productModel->delete($id);

        if ($success) {
            $this->response->success('Product deleted');
        } else {
            $this->response->error('Failed to delete product', 500);
        }
    }

    public function featured() {
        $limit = $this->request->get('limit', 10);
        $products = $this->productModel->getFeatured($limit);
        $this->response->success('Featured products', $products);
    }
}
?>