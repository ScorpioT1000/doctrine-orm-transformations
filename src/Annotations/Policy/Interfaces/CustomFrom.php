<?php
namespace ScorpioT1000\OTR\Annotations\Policy\Interfaces;
interface CustomFrom extends PolicyFrom {
    public function prove(Closure $c);
}