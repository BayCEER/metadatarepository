<?php

namespace OCA\metadatarepo\AppInfo;

use OCP\AppFramework\App;

class Application extends App {
    public function __construct(array $urlParams=array()){
        parent::__construct('metadatarepo', $urlParams);
           
    }

}

