<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSending;
use Symfony\Component\Mime\Address;

class AlwaysCcNocListener
{
    private const CC_ADDRESS = 'noc@neti.com.ph';
    private const CC_NAME    = 'NOC NETI';

    /**
     * Attach noc@neti.com.ph as CC on every outgoing email,
     * unless it is already present as a TO, CC, or BCC recipient.
     */
    public function handle(MessageSending $event): void
    {
        $message = $event->message; // Symfony\Component\Mime\Email

        // Collect all addresses already on the message
        $existing = array_map(
            fn (Address $a) => strtolower($a->getAddress()),
            array_merge(
                $message->getTo(),
                $message->getCc(),
                $message->getBcc(),
            )
        );

        if (! in_array(strtolower(self::CC_ADDRESS), $existing, true)) {
            $message->addCc(new Address(self::CC_ADDRESS, self::CC_NAME));
        }
    }
}
