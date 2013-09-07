<?php

namespace Tangler\Module\Imap;

use Tangler\Core\AbstractModule;
use Tangler\Core\Interfaces\ModuleInterface;

class Module extends AbstractModule implements ModuleInterface
{
    public function init()
    {
        $this->setKey('imap');
        $this->setLabel('IMAP module');
        $this->setDescription('This is the IMAP module');

        $this->setTriggers(array(
            new \Tangler\Module\Imap\NewEmailTrigger()
        ));

        $this->setActions(array(
        ));
    }
}
