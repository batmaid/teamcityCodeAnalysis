<?php
namespace src;

class HappyClass
{
    /** @var HelloProvider */
    private $provider;

    public function __construct()
    {
        $this->provider = new HelloProvider();
    }

    public function printHello()
    {
        echo $this->provider->sayHello();
    }
}
