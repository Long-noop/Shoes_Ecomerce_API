<?php
require_once ROOT_PATH . '/core/Controller.php';

class OrderController extends Controller {
    private $orderModel;

    public function __construct() {
        parent::__construct();
        $this->orderModel = $this->model('Order');
    }

    public function index() {
        $user = $this->requireAuth();

        $page = $this->request->get('page', 1);
        $perPage = $this->request->get('per_page', 20);
        $offset = ($page - 1) * $perPage;

        if ($user['role'] === 'admin') {
            // Admin sees all orders
            $orders = $this->orderModel->findAll($perPage, $offset);
            $total = $this->orderModel->count();
        } else {
            // Member sees only their orders
            $orders = $this->orderModel->findByUserId($user['user_id'], $perPage, $offset);
            $total = $this->orderModel->count("user_id = {$user['user_id']}");
        }

        $this->response->paginate($orders, $total, $page, $perPage);
    }

    public function show($id) {
        $user = $this->requireAuth();

        $order = $this->orderModel->findWithItems($id);

        if (!$order) {
            $this->response->error('Order not found', 404);
        }

        // Check if user owns the order or is admin
        if ($user['role'] !== 'admin' && $order['user_id'] != $user['user_id']) {
            $this->response->error('Forbidden', 403);
        }

        $this->response->success('Order details', $order);
    }

    public function store() {
        $user = $this->requireAuth();

        $validator = new Validator();
        $rules = [
            'items' => 'required',
            'total_price' => 'required|numeric',
            // 'shipping_address' => 'required',
            // 'payment_method' => 'required'
        ];

        if (!$validator->validate($this->request->all(), $rules)) {
            $this->response->error('Validation failed', 422, $validator->getErrors());
        }

        $orderData = [
            'total_price' => $this->request->get('total_price'),
            // 'shipping_address' => $this->request->get('shipping_address'),
            // 'payment_method' => $this->request->get('payment_method'),
            // 'notes' => $this->request->get('notes', '')
        ];

        $items = $this->request->get('items');

        try {
            $orderId = $this->orderModel->createOrder($user['user_id'], $orderData, $items);
            
            // Clear cart after successful order
            $cartModel = $this->model('Cart');
            $cartModel->clearCart($user['user_id']);

            $order = $this->orderModel->findWithItems($orderId);
            $this->response->success('Order created successfully', $order, 201);
        } catch (Exception $e) {
            $this->response->error('Failed to create order: ' . $e->getMessage(), 500);
        }
    }

    public function updateStatus($id) {
        $this->requireAdmin();

        $validator = new Validator();
        $rules = [
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled'
        ];

        if (!$validator->validate($this->request->all(), $rules)) {
            $this->response->error('Validation failed', 422, $validator->getErrors());
        }

        $order = $this->orderModel->findById($id);
        if (!$order) {
            $this->response->error('Order not found', 404);
        }

        $success = $this->orderModel->updateStatus($id, $this->request->get('status'));

        if ($success) {
            $updated = $this->orderModel->findById($id);
            $this->response->success('Order status updated', $updated);
        } else {
            $this->response->error('Failed to update order status', 500);
        }
    }
}
?>