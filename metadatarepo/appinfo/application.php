<?php

namespace OCA\metadatarepo\AppInfo;

use OCP\AppFramework\App;

class Application extends App {
    public function __construct(array $urlParams=array()){
        parent::__construct('metadatarepo', $urlParams);
/* Nicht mehr notwendig - erfolgt automatisch!
        $container = $this->getContainer();
        $container->registerService('PageController', function($c) {
            return new PageController(
                $c->query('AppName'),
                $c->query('Request')
            );
        });
        */
 
            
    }

}

