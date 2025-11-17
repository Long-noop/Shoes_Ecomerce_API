<?php
require_once ROOT_PATH . '/core/Controller.php';

class ContactController extends Controller {
    private $contactModel;

    public function __construct() {
        parent::__construct();
        $this->contactModel = $this->model('Contact');
    }

    // Public: Submit contact form
    public function store() {
        $validator = new Validator();
        $rules = [
            'name' => 'required',
            'email' => 'required|email',
            // 'subject' => 'required',
            'message' => 'required|min:10'
        ];

        if (!$validator->validate($this->request->all(), $rules)) {
            $this->response->error('Validation failed', 422, $validator->getErrors());
        }

        $data = $this->request->all();
        $data['status'] = 'unread';
        $data['created_at'] = date('Y-m-d H:i:s');

        $contactId = $this->contactModel->create($data);

        if ($contactId) {
            // TODO: Send email notification to admin
            $this->response->success('Message sent successfully. We will contact you soon!', null, 201);
        } else {
            $this->response->error('Failed to send message', 500);
        }
    }

    // Admin: Get all contacts
    public function index() {
        $this->requireAdmin();

        $page = $this->request->get('page', 1);
        $perPage = $this->request->get('per_page', 20);
        $offset = ($page - 1) * $perPage;
        $status = $this->request->get('status');

        if ($status) {
            $contacts = $this->contactModel->findByStatus($status, $perPage, $offset);
            $total = $this->contactModel->count("status = '$status'");
        } else {
            $contacts = $this->contactModel->findAll($perPage, $offset);
            $total = $this->contactModel->count();
        }

        $this->response->paginate($contacts, $total, $page, $perPage);
    }

    // Admin: Get single contact
    public function show($id) {
        $this->requireAdmin();

        $contact = $this->contactModel->findById($id);
        if (!$contact) {
            $this->response->error('Contact not found', 404);
        }

        // Mark as read
        if ($contact['status'] === 'unread') {
            $this->contactModel->update($id, ['status' => 'read']);
        }

        $this->response->success('Contact details', $contact);
    }

    // Admin: Update contact status
    public function updateStatus($id) {
        $this->requireAdmin();

        $validator = new Validator();
        $rules = [
            'status' => 'required|in:pending,replied,unread,read,responded'
        ];

        if (!$validator->validate($this->request->all(), $rules)) {
            $this->response->error('Validation failed', 422, $validator->getErrors());
        }

        $contact = $this->contactModel->findById($id);
        if (!$contact) {
            $this->response->error('Contact not found', 404);
        }

        $success = $this->contactModel->update($id, [
            'status' => $this->request->get('status'),
            // 'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($success) {
            $this->response->success('Status updated successfully', null);
        } else {
            $this->response->error('Failed to update status', 500);
        }
    }

    // Admin: Delete contact
    public function destroy($id) {
        $this->requireAdmin();

        $contact = $this->contactModel->findById($id);
        if (!$contact) {
            $this->response->error('Contact not found', 404);
        }

        $success = $this->contactModel->delete($id);

        if ($success) {
            $this->response->success('Contact deleted successfully', null);
        } else {
            $this->response->error('Failed to delete contact', 500);
        }
    }
}
?>