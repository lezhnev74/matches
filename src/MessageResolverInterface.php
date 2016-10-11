<?php

namespace Lezhnev74\Matches;

interface MessageResolverInterface
{
    /**
     * Method must return the message template with possible placeholders
     *
     * @param $rule_name
     * @param $message_name
     * @param $locale
     *
     * @return string
     */
    public function getMessage($rule_name, $message_name = "default", $locale = "en"): string;
}