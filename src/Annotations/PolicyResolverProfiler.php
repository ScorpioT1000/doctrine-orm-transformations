<?php
namespace Indaxia\OTR\Annotations;

use \Doctrine\Common\Annotations\Reader;
use \Indaxia\OTR\Annotations\Policy;
use \Indaxia\OTR\Exceptions\PolicyException;

class PolicyResolverProfiler extends PolicyResolver {
    public $results = [];
    public $timeStart = 0.0;
    
    const PRIORITY_DETAILS = 0x10000;
    
    public function __construct($options = 0x00) {
        $this->timeStart = microtime(true);
        parent::__construct($options);
    }
    
    /** @return Policy\Interfaces\Policy|null */
    public function resolvePropertyPolicyFrom(Policy\Interfaces\Policy $policy = null,
                                              $propertyName,
                                              \ReflectionProperty $p,
                                              Reader $ar) {
        $this->results[] = $this->padding().'[From] ';
        $el = & $this->results[count($this->results)-1];
        $result = parent::resolvePropertyPolicyFrom($policy, $propertyName, $p, $ar);
        $el .= number_format(microtime(true) - $this->timeStart, 6)
            .': '.$p->getDeclaringShortClass()->getName().'.'.$propertyName
            .' -> '.($result ? (new \ReflectionClass($result))->getShortName()
            .' (p '.rtrim(number_format($result->priority, 16),'0').')' : 'null');
        return $result;
    }
    
    /** @return Policy\Interfaces\Policy|null */
    public function resolvePropertyPolicyTo(Policy\Interfaces\Policy $policy = null,
                                            $propertyName,
                                            \ReflectionProperty $p,
                                            Reader $ar) {
        $this->results[] = $this->padding().'[To] ';
        $el = & $this->results[count($this->results)-1];
        $result = parent::resolvePropertyPolicyTo($policy, $propertyName, $p, $ar);
        $el .= number_format(microtime(true) - $this->timeStart, 6)
            .': '.$p->getDeclaringClass()->getShortName().'.'.$propertyName
            .' -> '.($result ? (new \ReflectionClass($result))->getShortName()
            .' (p '.rtrim(number_format($result->priority, 16),'0').')' : 'null');
        return $result;
    }
    
    public function mergeFrom(array $policies) {
        $result = parent::mergeFrom($policies);
        if($this->hasOption(PROFILER_DETAILS)) {
            foreach($policies as $p) {
                $this->addResult($p);
            }
            $this->results[] = '';
        }
        return $result;
    }
    
    public function mergeTo(array $policies) {
        $result = parent::mergeTo($policies);
        if($this->hasOption(static::PRIORITY_DETAILS)) {
            foreach($policies as $p) {
                $this->addResult($p);
            }
            $this->results[] = '';
        }
        return $result;
    }
    
    protected function addResult($policy) {
        $this->results[] = $this->padding().'    - '.(new \ReflectionClass($policy))->getShortName()
            .' (p '.rtrim(number_format($policy->priority, 16),'0').')'
            .($policy->nested ? ' {...}('.count($policy->nested).')' : '');
    }
    
    protected function padding() {
        return str_repeat('    ', $this->currentDepth);
    }
}