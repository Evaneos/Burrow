<?php
namespace Burrow;

class Escaper
{
    const ESCAPE_MODE_NONE      = 'none';
    const ESCAPE_MODE_SERIALIZE = 'serialize';
    const ESCAPE_MODE_JSON      = 'json';
    
    /**
     * Escape the message
     *
     * @param  string $message
     * @param  string $escapeMode
     * @return string
     */
    public static function escape($message, $escapeMode)
    {
        $escapedMessage = $message;
        switch($escapeMode) {
            case self::ESCAPE_MODE_SERIALIZE :
                $escapedMessage = serialize($message);
                break;
            case self::ESCAPE_MODE_JSON :
                $escapedMessage = json_encode($message);
                break;
        }
        return $escapedMessage;
    }
    
    /**
     * Unescape the message
     *
     * @param  string $message
     * @param  string $escapeMode
     * @return string
     */
    public function unescape($message, $escapeMode)
    {
        $unescapedMessage = $message;
        switch($escapeMode) {
            case self::ESCAPE_MODE_SERIALIZE :
                $unescapedMessage = unserialize($message);
                break;
            case self::ESCAPE_MODE_JSON :
                $unescapedMessage = json_decode($message);
                break;
        }
        return $unescapedMessage;
    }
}
