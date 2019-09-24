<?php
interface IRequest
{
    /**
     * Return the request payload
     *
     * @return array | null;
     */
    public function getBody();
}