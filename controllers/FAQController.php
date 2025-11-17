<?php
require_once ROOT_PATH . '/core/Controller.php';

class FAQController extends Controller {
    private $faqModel;

    public function __construct() {
        parent::__construct();
        $this->faqModel = $this->model('FAQ');
    }

    // Get all FAQs
    public function index() {
        $category = $this->request->get('category');

        if ($category) {
            $faqs = $this->faqModel->findByCategory($category);
        } else {
            $faqs = $this->faqModel->findAll();
        }

        $this->response->success('FAQs list', $faqs);
    }

    // Get single FAQ
    public function show($id) {
        $faq = $this->faqModel->findById($id);

        if (!$faq) {
            $this->response->error('FAQ not found', 404);
        }

        $this->response->success('FAQ details', $faq);
    }

    // Admin: Create FAQ
    public function store() {
        $this->requireAdmin();

        $validator = new Validator();
        $rules = [
            'question' => 'required',
            'answer' => 'required',
            // 'category' => 'required'
        ];

        if (!$validator->validate($this->request->all(), $rules)) {
            $this->response->error('Validation failed', 422, $validator->getErrors());
        }

        $data = $this->request->all();
        $data['created_at'] = date('Y-m-d H:i:s');

        $faqId = $this->faqModel->create($data);

        if ($faqId) {
            $faq = $this->faqModel->findById($faqId);
            $this->response->success('FAQ created successfully', $faq, 201);
        } else {
            $this->response->error('Failed to create FAQ', 500);
        }
    }

    // Admin: Update FAQ
    public function update($id) {
        $this->requireAdmin();

        $faq = $this->faqModel->findById($id);
        if (!$faq) {
            $this->response->error('FAQ not found', 404);
        }

        $data = $this->request->all();
        // $data['updated_at'] = date('Y-m-d H:i:s');

        $success = $this->faqModel->update($id, $data);

        if ($success) {
            $updated = $this->faqModel->findById($id);
            $this->response->success('FAQ updated successfully', $updated);
        } else {
            $this->response->error('Failed to update FAQ', 500);
        }
    }

    // Admin: Delete FAQ
    public function destroy($id) {
        $this->requireAdmin();

        $faq = $this->faqModel->findById($id);
        if (!$faq) {
            $this->response->error('FAQ not found', 404);
        }

        $success = $this->faqModel->delete($id);

        if ($success) {
            $this->response->success('FAQ deleted successfully');
        } else {
            $this->response->error('Failed to delete FAQ', 500);
        }
    }
}
?>