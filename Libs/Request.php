<?php
include_once 'IRequest.php';

class Request implements IRequest
{
    /**
     * Initialize Request class
     *
     *
     * @return void;
     */
    function __construct()
    {
        $this->bootstrapSelf();
    }

    /**
     * Register the server properties
     *
     * @return void;
     */
    private function bootstrapSelf()
    {
        foreach($_SERVER as $key => $value)
        {
            $this->{$this->toCamelCase($key)} = $value;
        }
    }

    /**
     * Convert string to camel case
     *
     * @param $string
     *
     * @return string;
     */
    private function toCamelCase($string)
    {
        $result = strtolower($string);

        preg_match_all('/_[a-z]/', $result, $matches);
        foreach($matches[0] as $match)
        {
            $c = str_replace('_', '', strtoupper($match));
            $result = str_replace($match, $c, $result);
        }
        return $result;
    }

    /**
     * {inherit doc}
     */
    public function getBody()
    {
        if (in_array($this->requestMethod, ['POST', 'PUT']))
        {
            return $_POST ?: json_decode(file_get_contents("php://input"), true);
        }
        return;
    }
}