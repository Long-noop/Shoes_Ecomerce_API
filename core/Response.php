<?php
class Response{
    public function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application.json');
        echo json_encode($data);
        exit;
    }

    public function success($message, $data, $statusCode = 200){
        $respone = [
            'success' => true,
            'message' => $message
        ];
        if($data !== null){
            $respone['data'] = $data;
        }
        $this->json($respone, $statusCode);
    }

    public function error($message, $statusCode = 400, $errors = null){
        $respone = [
            'success' => false,
            'message' => $message
        ];
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        $this->json($respone, $statusCode);
    }

    public function paginate($data, $total, $page, $perPage) {
        $response = [
            'success' => true,
            'data' => $data,
            'pagination' => [
                'total' => (int)$total,
                'per_page' => (int)$perPage,
                'current_page' => (int)$page,
                'last_page' => (int)ceil($total / $perPage),
                'from' => (($page - 1) * $perPage) + 1,
                'to' => min($page * $perPage, $total)
            ]
        ];
        
        $this->json($response);
    }
}
?>