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
        $this->setImageUrl('http://carolwatkins.co.uk/wp-content/uploads/2012/06/incoming-email-icon-150x150.jpg');

        $this->setTriggers(array(
            new \Tangler\Module\Imap\NewEmailTrigger()
        ));

        $this->setActions(array(
        ));
    }
}
