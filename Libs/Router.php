<?php

class Router
{
    private $request;
    private $supportedHttpMethods = array(
        'GET',
        'POST',
        'PUT',
        'DELETE'
    );

    /**
     * Initialize Router class
     *
     * @param $request
     *
     * @return void;
     */
    function __construct(IRequest $request)
    {
        $this->request = $request;
    }

    /**
     * Dynamically call undefined methods
     *
     * @param $name
     * @param $args
     *
     * @return void;
     */
    function __call($name, $args)
    {
        list($route, $controller) = $args;
        if(!in_array(strtoupper($name), $this->supportedHttpMethods))
        {
            $this->invalidMethodHandler();
        }
        $this->{strtolower($name)}[$this->formatRoute($route)] = $controller;
    }

    /**
     * Format route
     *
     * @param $route
     *
     * @return string;
     */
    private function formatRoute($route)
    {
        $result = rtrim($route, '/');
        if ($result === '')
        {
            return '/';
        }
        return $result;
    }

    /**
     * When a invalid method is called this handle the response header
     *
     * @return void;
     */
    private function invalidMethodHandler()
    {
        header("{$this->request->serverProtocol} 405 Method Not Allowed");
    }

    /**
     * When a invalid route is called this handle the response header
     *
     * @return void;
     */
    private function defaultRequestHandler()
    {
        header("{$this->request->serverProtocol} 404 Not Found");
    }

    /**
     * Replace the numeric parameter from the route with $id
     * to match the route config
     *
     * @param $requestUri
     *
     * @return void;
     */
    private function translateUrl($requestUri)
    {
        $urlArray = explode('/', $requestUri);
        $parsedUrl = [];
        foreach($urlArray as $segment)
        {
            if(is_numeric($segment))
            {
                $segment = '$id';
            }
            $parsedUrl[] = $segment;
        }
        return implode('/', $parsedUrl);
    }

    /**
     * Initialize the controller and call the method requested
     *
     * @return void;
     */
    function resolve()
    {
        $methodDictionary = $this->{strtolower($this->request->requestMethod)};
        $formattedRoute = $this->formatRoute($this->request->requestUri);
        $translatedUrl = $this->translateUrl($formattedRoute);
        if(!array_key_exists($translatedUrl, $methodDictionary))
        {
            $this->defaultRequestHandler();
            return;
        }
        $controller = explode(':', $methodDictionary[$translatedUrl]);
        $class = $controller[0];
        $method = $controller[1];
        require_once 'Controllers/'.$class.'.php';
        if(in_array($this->request->requestMethod, ['POST', 'PUT']))
        {
            $params = [$this->request];
        } else {
            $params = $this->getVariablesFromRoute($translatedUrl, $formattedRoute);
        }
        if($this->request->requestUri !== '/'){
            header('Content-Type: application/json');
            echo json_encode(call_user_func_array([$class, $method], $params));
        }else {
            echo call_user_func_array([$class, $method], $params);
        }
    }

    /**
     * Get params from URL
     *
     * @param $route
     * @param $requestUrl
     *
     * @return array;
     */
    function getVariablesFromRoute($route, $requestUrl)
    {
        $segments = explode('/', $route);
        $requestUrlArray = explode('/',  $requestUrl);
        $params = [];
        foreach($segments as $key => $val)
        {
            if(strpos($val, '$') !== FALSE)
            {
                $params[] = $requestUrlArray[$key];
            }
        }
        return $params;
    }

    /**
     * Resolve the request
     *
     * @return void;
     */
    function __destruct()
    {
        $this->resolve();
    }
}