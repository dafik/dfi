<?php
/**
 * Created by IntelliJ IDEA.
 * User: z.wieczorek
 * Date: 13.10.15
 * Time: 11:39
 */

namespace Dfi\Exception;


class Fake
{

    public $_previous;
    public $_trace;

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @param mixed $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @param mixed $line
     */
    public function setLine($line)
    {
        $this->line = $line;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }
}