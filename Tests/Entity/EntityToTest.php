<?php
namespace Yosimitso\MockDoctrineManager\Tests\Entity;
class EntityToTest
{
    private $nb;

    public function setNb($nb)      // CLASSIC SETTER
    {
        $this->nb = $nb;
    }

    public function getNb()         // CLASSIC GETTER
    {
        return $this->nb;
    }
}
