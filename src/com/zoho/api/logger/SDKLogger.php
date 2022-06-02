<?php

namespace com\zoho\api\logger;

use com\zoho\crm\api\exception\SDKException;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * This class to initialize the SDK logger.
 */
class SDKLogger
{
    /** @var LoggerInterface|null */
    private static $logger = null;

    public static function initialize(LoggerInterface $logger)
    {
        self::$logger = $logger;
    }

    /** @return LoggerInterface|null */
    public static function getLogger()
    {
        return self::$logger;
    }

    public static function warn(string $msg)
    {
        self::$logger->warning($msg);
    }

    public static function info(string $msg)
    {
        self::$logger->info($msg);
    }

    public static function severe(Throwable $e)
    {
        $message = self::parseException($e);

        self::$logger->emergency($message, ['exception' => $e]);
    }

    public static function severeError($message, Throwable $e = null)
    {
        $parsedMessage = $message;

        if($e != null)
        {
            $parsedMessage = $parsedMessage . " " . self::parseException($e);
        }

        self::$logger->emergency($parsedMessage, ['exception' => $e]);
    }

    private static function parseException(Throwable $e): string
    {
        $message = "";

        if($e instanceof SDKException)
        {
            $message = $message . $e->__toString() . "\n";
        }

        $message = $message . $e->getFile() . "- " . $e->getLine() . "- " . $e->getMessage() . "\n";

        return $message . $e->getTraceAsString();
    }

    public static function err(Throwable $e)
    {
        $message = self::parseException($e);

        self::$logger->error($message, ['exception' => $e]);
    }

    public static function error(string $message)
    {
        self::$logger->error($message);
    }

    public static function debug(string $msg)
    {
        self::$logger->debug($msg);
    }
}
