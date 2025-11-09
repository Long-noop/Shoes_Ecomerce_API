<?php
class Controller {
    protected $request;
    protected $response;

    public function __construct()
    {
        $this->request = new Request();
        $this->response = new Response() ;
    }

    protected function model($model) {
        $model_path = ROOT_PATH . "/models/$model.php";
        if(file_exists($model_path)){
            require_once $model_path;
            return new $model();
        }
        throw new Exception("Model $model not found");
    }

    protected function requireAuth() {
        $token = $this->request->getBearerToken();
        if(!$token){
            $this->response->error('Unauthorized', 404);
        }

        $user = Auth::validateToken($token);
        if(!$user){
            $this->response->error('Invalid token', 404);
        }
        return $user;
    }

    protected function requireAdmin() {
        $user = $this->requireAuth();   
        if($user['role'] !== 'admin') {
            $this->response->error('Forbidden', 403);
        }
    }
}
?>