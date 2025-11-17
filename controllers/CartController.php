<?php
require_once ROOT_PATH . '/core/Controller.php';

class CartController extends Controller {
    private $cartModel;

    public function __construct() {
        parent::__construct();
        $this->cartModel = $this->model('Cart');
    }

    public function index() {
        $user = $this->requireAuth();
        
        $cartItems = $this->cartModel->findByUserId($user['user_id']);
        $total = $this->cartModel->getTotal($user['user_id']);

        $this->response->success('Cart items', [
            'items' => $cartItems,
            'total' => $total
        ]);
    }

    public function addItem() {
        $user = $this->requireAuth();

        $validator = new Validator();
        $rules = [
            'product_id' => 'required|numeric',
            'quantity' => 'required|numeric'
        ];

        if (!$validator->validate($this->request->all(), $rules)) {
            $this->response->error('Validation failed', 422, $validator->getErrors());
        }

        // Check product exists and has stock
        $productModel = $this->model('Product');
        $product = $productModel->findById($this->request->get('product_id'));

        if (!$product) {
            $this->response->error('Product not found', 404);
        }

        if ($product['stock'] < $this->request->get('quantity')) {
            $this->response->error('Insufficient stock', 400);
        }

        $result = $this->cartModel->addItem(
            $user['user_id'],
            $this->request->get('product_id'),
            $this->request->get('quantity')
        );

        if ($result) {
            $this->response->success('Item added to cart', null, 201);
        } else {
            $this->response->error('Failed to add item to cart', 500);
        }
    }

    public function updateQuantity($id) {
        $user = $this->requireAuth();

        $validator = new Validator();
        $rules = [
            'quantity' => 'required|numeric'
        ];

        if (!$validator->validate($this->request->all(), $rules)) {
            $this->response->error('Validation failed', 422, $validator->getErrors());
        }

        $success = $this->cartModel->updateQuantity($id, $this->request->get('quantity'));

        if ($success) {
            $this->response->success('Cart updated', null);
        } else {
            $this->response->error('Failed to update cart', 500);
        }
    }

    public function removeItem($id) {
        $user = $this->requireAuth();

        $success = $this->cartModel->removeItem($id, $user['user_id']);

        if ($success) {
            $this->response->success('Item removed from cart', null);
        } else {
            $this->response->error('Failed to remove item', 500);
        }
    }

    public function clear() {
        $user = $this->requireAuth();

        $success = $this->cartModel->clearCart($user['user_id']);

        if ($success) {
            $this->response->success('Cart cleared', null);
        } else {
            $this->response->error('Failed to clear cart', 500);
        }
    }
}
?>