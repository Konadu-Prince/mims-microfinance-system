<?php

namespace MIMS\Core\Router;

use MIMS\Core\Factory\ControllerFactory;
use MIMS\Core\Exceptions\NotFoundException;
use MIMS\Core\Exceptions\MethodNotAllowedException;

/**
 * API Router
 * Handles API routing and request dispatching
 */
class ApiRouter
{
    private array $routes = [];
    private string $basePath = '/api/v1';

    /**
     * Add route
     */
    public function addRoute(string $method, string $path, string $controller, string $action): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $this->basePath . $path,
            'controller' => $controller,
            'action' => $action
        ];
    }

    /**
     * Add GET route
     */
    public function get(string $path, string $controller, string $action): void
    {
        $this->addRoute('GET', $path, $controller, $action);
    }

    /**
     * Add POST route
     */
    public function post(string $path, string $controller, string $action): void
    {
        $this->addRoute('POST', $path, $controller, $action);
    }

    /**
     * Add PUT route
     */
    public function put(string $path, string $controller, string $action): void
    {
        $this->addRoute('PUT', $path, $controller, $action);
    }

    /**
     * Add DELETE route
     */
    public function delete(string $path, string $controller, string $action): void
    {
        $this->addRoute('DELETE', $path, $controller, $action);
    }

    /**
     * Dispatch request
     */
    public function dispatch(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Find matching route
        $route = $this->findRoute($requestMethod, $requestPath);

        if (!$route) {
            throw new NotFoundException('Route not found');
        }

        // Get controller instance
        $controller = ControllerFactory::getController($route['controller']);
        
        if (!method_exists($controller, $route['action'])) {
            throw new MethodNotAllowedException('Action not found');
        }

        // Call controller action
        $controller->{$route['action']}();
    }

    /**
     * Find matching route
     */
    private function findRoute(string $method, string $path): ?array
    {
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $path)) {
                return $route;
            }
        }
        return null;
    }

    /**
     * Match path with parameters
     */
    private function matchPath(string $routePath, string $requestPath): bool
    {
        // Convert route path to regex
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        return preg_match($pattern, $requestPath);
    }

    /**
     * Setup default routes
     */
    public function setupDefaultRoutes(): void
    {
        // Customer routes
        $this->get('/customers', 'CustomerController', 'index');
        $this->post('/customers', 'CustomerController', 'create');
        $this->get('/customers/{id}', 'CustomerController', 'show');
        $this->put('/customers/{id}', 'CustomerController', 'update');
        $this->delete('/customers/{id}', 'CustomerController', 'delete');

        // Account routes
        $this->get('/accounts', 'AccountController', 'index');
        $this->post('/accounts', 'AccountController', 'create');
        $this->get('/accounts/{id}', 'AccountController', 'show');
        $this->put('/accounts/{id}', 'AccountController', 'update');

        // Transaction routes
        $this->get('/transactions', 'TransactionController', 'index');
        $this->post('/transactions', 'TransactionController', 'create');
        $this->get('/transactions/{id}', 'TransactionController', 'show');

        // Loan routes
        $this->get('/loans', 'LoanController', 'index');
        $this->post('/loans', 'LoanController', 'create');
        $this->get('/loans/{id}', 'LoanController', 'show');
        $this->put('/loans/{id}/approve', 'LoanController', 'approve');
        $this->put('/loans/{id}/reject', 'LoanController', 'reject');

        // User routes
        $this->get('/users', 'UserController', 'index');
        $this->post('/users', 'UserController', 'create');
        $this->get('/users/{id}', 'UserController', 'show');
        $this->put('/users/{id}', 'UserController', 'update');

        // Auth routes
        $this->post('/auth/login', 'AuthController', 'login');
        $this->post('/auth/logout', 'AuthController', 'logout');
        $this->post('/auth/refresh', 'AuthController', 'refresh');
    }
}
