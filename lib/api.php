<?php
namespace NetworkLeads;

class api extends Requests
{
    public function __construct($ST,$SK)
    {
        parent::__construct($ST,$SK);
    }

    public function getData(){
        $this->a();
        //return $this->request("/repos/$owner/$repo/releases", 'POST', $data, 201);
    }
}