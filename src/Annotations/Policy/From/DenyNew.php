<?php
namespace ScorpioT1000\OTR\Annotations\Policy\From;

use \ScorpioT1000\OTR\Annotations\Policy\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Don't create a new sub-Entity when it needed, skip instead in ITransformabe::fromArray.
 * It's applicable to Collection too.
 * It's applicable to scalar fields: it denies to set the new value if the value is empty.
 * It's not applicable to non-nullable numbers.
 * @ORM\Annotation */
class DenyNew
    extends \ScorpioT1000\OTR\Annotations\Annotation
    implements Interfaces\DenyNewFrom {

    public $priority = 0.100;
}