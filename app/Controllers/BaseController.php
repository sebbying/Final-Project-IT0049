<?php
namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\{CLIRequest, IncomingRequest, RequestInterface, ResponseInterface};
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected $helpers = ['url', 'form', 'pos'];

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        helper($this->helpers);
    }

    protected function render(string $view, array $data = []): string
    {
        $data['currentUser'] = session('user_id') ? (new UserModel())->find((int) session('user_id')) : null;
        return view($view, $data);
    }

    protected function invalid(string $path)
    {
        return $this->backWithInput($path)->with('errors', $this->validator->getErrors());
    }

    protected function backWithInput(string $path)
    {
        $post = $this->request->getPost();
        foreach (['password', 'password_confirm', 'setup_token', 'csrf_token'] as $secret) { unset($post[$secret]); }
        return redirect()->to(site_url($path))->with('_ci_old_input', ['get' => [], 'post' => $post]);
    }

    protected function found(?array $record): array
    {
        if (!$record) { throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(); }
        return $record;
    }
}
