<?php

namespace Tangler\Module\Imap;

use Tangler\Core\Interfaces\TriggerInterface;
use Tangler\Core\AbstractTrigger;

use ezcMailImapTransportOptions;
use ezcMailImapTransport;
use ezcMailParser;

class NewEmailTrigger extends AbstractTrigger implements TriggerInterface
{
    public function Init()
    {
        $this->setKey('new_email');
        $this->setLabel('New email trigger');
        $this->setDescription('This thing monitors an IMAP box for new emails');

        $this->parameter->defineParameter('username', 'string', 'Username of the email account');
        $this->parameter->defineParameter('password', 'string', 'Password of the email account');
        $this->parameter->defineParameter('hostname', 'string', 'Hostname of the imap server');
        $this->parameter->defineParameter('mailbox', 'string', 'Mailbox to check');

        $this->output->defineParameter('subject', 'string', 'Subject of the new email');
        $this->output->defineParameter('fromemail', 'string', 'Email address of the sender');
        $this->output->defineParameter('fromname', 'string', 'Name of the sender');
        $this->output->defineParameter('sendstamp', 'stamp', 'Timestamp when the email was sent');
        $this->output->defineParameter('content', 'string', 'Contents of the new email');
    }


    public function Poll($channel)
    {
        //$this->pollZend($channel);
        $this->pollEzc($channel);
    }

    public function pollEzc($channel) {
        $hostname = $this->parameter->getValue('hostname');
        $username = $this->parameter->getValue('username');
        $password = $this->parameter->getValue('password');
        $mailbox = $this->parameter->getValue('mailbox');
        if ($mailbox=='') {
            $mailbox = 'Inbox';
        }

        echo "POLLING FOR NEW EMAIL in [" . $hostname . "]\n";

        $options = new ezcMailImapTransportOptions();
        $options->uidReferencing = true;

        $imap = new ezcMailImapTransport($hostname, null, $options);
        $capabilities = $imap->capability();
        //print_r($capabilities);
        $imap->authenticate($username, $password);
        $imap->selectMailbox($mailbox);
        $messages = $imap->listUniqueIdentifiers();
        foreach($messages as $i=>$uid) {
            //echo $i . ":" . $uid . "\n";
            $key = $hostname . '_' . $username . '_' . $mailbox . '_' . $uid;
            if (!$this->isProcessed($key)) {
                $set = $imap->fetchByMessageNr($uid);
                $parser = new ezcMailParser();
                $m = $parser->parseMail($set);
                
                // Documentation about the parseMail output:
                // http://ezcomponents.org/docs/api/trunk/Mail/ezcMail.html
                
                $parts = $m[0]->fetchParts();
                $contentplain = null;
                $contenthtml = null;

                foreach($parts as $part) {

                    // Example display code:
                    // http://ezcomponents.org/docs/api/trunk/Mail_display-example.html
                    if ($part instanceof \ezcMailText) {

                        switch ($part->subType) {
                            case 'plain';
                                $contentplain = $part->text;
                                break;
                            case 'html';
                                $contenthtml = $part->text;
                                break;
                        }
                        $content = $contentplain;
                        if ($content=='') {
                            $content = $contenthtml;
                        }
                    }
                }
                $this->output->setValue('subject', $m[0]->subject);
                $this->output->setValue('fromemail', $m[0]->from->email);
                $this->output->setValue('fromname', $m[0]->from->name);
                $this->output->setValue('sendstamp', $m[0]->timestamp);
                $this->output->setValue('content', $content);

                foreach($channel->getActions() as $action) {
                    $action->Run($this->output);
                }

                $this->setProcessed($key);
            }
        }
    }

    public function pollZend($channel)
    {
        throw new \RuntimeException('pollZend not completely implemented');

        $mail = new \Zend\Mail\Storage\Imap(array('host'     => $hostname,
                                         'user'     => $username,
                                         'password' => $password));

        foreach ($mail as $messageNum => $message) {
            echo $messageNum . "\n";
            print_r($message);
        }
    }
}
