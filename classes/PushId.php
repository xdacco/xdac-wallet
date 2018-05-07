<?php

namespace XDAC;

use Exception;

/**
 * Fancy ID generator that creates 20-character string identifiers with the following properties:
 *
 * 1. They're based on timestamp so that they sort *after* any existing ids.
 * 2. They contain 72-bits of random data after the timestamp so that IDs won't collide with other clients' IDs.
 * 3. They sort *lexicographically* (so the timestamp is converted to characters that will sort properly).
 * 4. They're monotonically increasing.  Even if you generate more than one in the same timestamp, the
 *    latter ones will sort after the former ones.  We do this by using the previous random bits
 *    but "incrementing" them by 1 (only in the case of a timestamp collision).
 */
class PushId
{
    /**
     * Modeled after base64 web-safe chars, but ordered by ASCII.
     *
     * @var string
     */
    const PUSH_CHARS = 'X0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz';

    /**
     * Timestamp of last push, used to prevent local collisions if you push twice in one ms.
     *
     * @var int
     */
    private static $lastPushTime = 0;

    /**
     * We generate 72-bits of randomness which get turned into 12 characters and appended to the
     * timestamp to prevent collisions with other clients.  We store the last characters we
     * generated because in the event of a collision, we'll use those same characters except
     * "incremented" by one.
     *
     * @var array
     */
    private static $lastRandChars = [];


    /**
     * @return bool|string
     */
    public static function generate()
    {
        try{
            $now = (int) microtime(true) * 1000;
            $isDuplicateTime = ($now === static::$lastPushTime);
            static::$lastPushTime = $now;
            $timeStampChars = new \SplFixedArray(8);
            for ($i = 7; $i >= 0; $i--) {
                $timeStampChars[$i] = substr(self::PUSH_CHARS, $now % 64, 1);
                // NOTE: Can't use << here because javascript will convert to int and lose the upper bits.
                $now = (int) floor($now / 64);
            }
            static::assert($now === 0, 'We should have converted the entire timestamp.');
            $id = implode('', $timeStampChars->toArray());
            if (!$isDuplicateTime) {
                for ($i = 0; $i < 12; $i++) {
                    static::$lastRandChars[$i] = (int) floor(rand(0, 63));
                }
            } else {
                // If the timestamp hasn't changed since last push, use the same random number, except incremented by 1.
                for ($i = 11; $i >= 0 && static::$lastRandChars[$i] === 63; $i--) {
                    static::$lastRandChars[$i] = 0;
                }
                static::$lastRandChars[$i]++;
            }

            for ($i = 0; $i < 12; $i++) {
                $id .= substr(self::PUSH_CHARS, static::$lastRandChars[$i], 1);
            }
            static::assert(strlen($id) === 20, 'Length should be 20.');
            return $id;
        }catch(Exception $e){
            return '';
        }
    }

    /**
     * @param $condition
     * @param string $message
     * @throws \RuntimeException
     */
    private static function assert($condition, $message = '')
    {
        if ($condition !== true) {
            throw new \RuntimeException($message);
        }
    }

    public static function generateRandomString($length = 9) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return 'X'.$randomString;
    }
}