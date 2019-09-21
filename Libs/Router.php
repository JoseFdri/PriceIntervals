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

    function __construct(IRequest $request)
    {
        $this->request = $request;
    }

    function __call($name, $args)
    {
        list($route, $controller) = $args;
        if(!in_array(strtoupper($name), $this->supportedHttpMethods))
        {
            $this->invalidMethodHandler();
        }
        $this->{strtolower($name)}[$this->formatRoute($route)] = $controller;
    }


    private function formatRoute($route)
    {
        $result = rtrim($route, '/');
        if ($result === '')
        {
            return '/';
        }
        return $result;
    }

    private function invalidMethodHandler()
    {
        header("{$this->request->serverProtocol} 405 Method Not Allowed");
    }

    private function defaultRequestHandler()
    {
        header("{$this->request->serverProtocol} 404 Not Found");
    }

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

    function __destruct()
    {
        $this->resolve();
    }
}