<?php

namespace ScorpioT1000\OTR\Demo\Symfony;

use \Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\JsonResponse;
use \Symfony\Bundle\FrameworkBundle\Controller\Controller as SymfonyController;
use \Symfony\Component\Serializer\Encoder\JsonEncoder;

use \ScorpioT1000\OTR\ITransformable;
use \ScorpioT1000\OTR\Traits\Transformable;
use \ScorpioT1000\OTR\Policy;

use \ScorpioT1000\OTR\Demo\Entity\THead;
use \ScorpioT1000\OTR\Demo\Entity\TSub;
use \ScorpioT1000\OTR\Demo\Entity\TSubCol;

class Controller extends SymfonyController
{
    /**
     * @Route("/to-array")
     */
    public function toarrayAction() {        
        $th = new THead();
        for($i=0; $i<5; ++$i) { $th->getMany2many()->add(new TSubCol()); }
        $ts = new TSub();
        for($i=0; $i<2; ++$i) { $ts->getOne2many()->add(new TSubCol()); }
        $th->setMany2one($ts);
        $th->setOne2one(new TSub());
        
        $this->getEM()->persist($th);
        $this->getEM()->flush();
        
        return $this->success($th->toArray());
    }
    
    /**
     * @Route("/from-array")
     */
    public function fromarrayAction() {
        try {
            $data = $this->getRequestContentJson();

            if(empty($data)) { return $this->fail('Input must be a JSON object representing Transformable Entity'); }
            $th = empty($data['id'])
                ? null
                : $this->getRepository('THead')->findOneBy(['id' => $data['id']]);
            if(! $th) {
                $th = new THead();
            }
        
            $th->fromArray($data, $this->getEM());
            $this->getEM()->persist($th);
            $this->getEM()->flush();
        } catch(\Exception $e) {
            return $this->fail($e->getMessage());
        }
        
        return $this->success($th->toArray());
    }
    
    
    // ======================= Utils ======================= 
    
    /** @param array $data is optional
	 * @param array|null additional data
      * @return JsonResponse */
    public function success($data = array()) {
        return new JsonResponse(['success' => true, 'data' => $data]);
    }
    
    public function fail($error) {
        return new JsonResponse(['success' => false, 'message' => $error]);
    }
    
    /** @return \Doctrine\ORM\EntityManager */
    public function getEM() {
        return $this->get('doctrine.orm.entity_manager');
    }
    
    /** Returns request content (in json) as assoc array
      * @return array */
    public function getRequestContentJson() {
        $enc = new JsonEncoder();
        return $enc->decode($this->container->get('request_stack')->getCurrentRequest()->getContent(), 'json');
    }
    
    /** @return \Doctrine\ORM\EntityRepository */
    public function getRepository($name, $ns = "ScorpioT1000\\OTR\\Demo\\Entity") {
        return $this->getEM()->getRepository($ns.$name);
    }
}