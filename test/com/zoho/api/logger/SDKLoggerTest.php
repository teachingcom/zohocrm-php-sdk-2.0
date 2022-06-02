<?php

namespace test\com\zoho\api\logger;

use com\zoho\api\logger\SDKLogger;
use com\zoho\crm\api\exception\SDKException;
use com\zoho\crm\api\util\Constants;
use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class SDKLoggerTest extends MockeryTestCase
{
    /** @var MockInterface|LoggerInterface */
    private $logger;

    private function initialize()
    {
        $this->logger = Mockery::mock(LoggerInterface::class);
        SDKLogger::initialize($this->logger);
    }

    public function testInfo()
    {
        $this->initialize();
        $this->logger->shouldReceive('info')->with('an info message')->once();

        SDKLogger::info('an info message');
    }

    public function testWarn()
    {
        $this->initialize();
        $this->logger->shouldReceive('warning')->with('a warn message')->once();

        SDKLogger::warn('a warn message');
    }

    public function getExceptionData(): array
    {
        $exception = new Exception('exception message');
        $SDKException = new SDKException(Constants::MANDATORY_VALUE_ERROR, Constants::MANDATORY_KEY_ERROR, Constants::OAUTH_MANDATORY_KEYS);

        return [
            'core exception' => ["{% message %} {$exception->getFile()}- {$exception->getLine()}- {$exception->getMessage()}\n{$exception->getTraceAsString()}", $exception],
            'sdk exception' => ["{% message %} {$SDKException->__toString()}\n{$SDKException->getFile()}- {$SDKException->getLine()}- {$SDKException->getMessage()}\n{$SDKException->getTraceAsString()}", $SDKException],
        ];
    }

    /** @dataProvider getExceptionData */
    public function testSevere(string $expectMsg, Throwable $exception)
    {
        $this->initialize();
        $this->logger->shouldReceive('emergency')
            ->withArgs(function (string $arg1, array $arg2) use ($expectMsg, $exception): bool {
                $this->assertSame(str_replace('{% message %} ', '', $expectMsg), $arg1);
                $this->assertSame(compact('exception'), $arg2);
                return true;
            })
            ->once();

        SDKLogger::severe($exception);
    }

    /** @dataProvider getExceptionData */
    public function testErr(string $expectMsg, Throwable $exception)
    {
        $this->initialize();
        $this->logger->shouldReceive('error')
            ->withArgs(function (string $arg1, array $arg2) use ($expectMsg, $exception): bool {
                $this->assertSame(str_replace('{% message %} ', '', $expectMsg), $arg1);
                $this->assertSame(compact('exception'), $arg2);
                return true;
            })
            ->once();

        SDKLogger::err($exception);
    }

    public function testSevereErrorWithoutException()
    {
        $this->initialize();
        $this->logger->shouldReceive('emergency')->with('a severe message', ['exception' => null])->once();

        SDKLogger::severeError('a severe message');
    }

    /** @dataProvider getExceptionData */
    public function testSevereErrorWithException(string $expectMsg, Throwable $exception)
    {
        $this->initialize();
        $this->logger->shouldReceive('emergency')
            ->withArgs(function (string $arg1, array $arg2) use ($expectMsg, $exception): bool {
                $this->assertSame(str_replace('{% message %}', 'a severe message', $expectMsg), $arg1);
                $this->assertSame(compact('exception'), $arg2);
                return true;
            })
            ->once();

        SDKLogger::severeError('a severe message', $exception);
    }

    public function testError()
    {
        $this->initialize();
        $this->logger->shouldReceive('error')->with('an error message')->once();

        SDKLogger::error('an error message');
    }

    public function testDebug()
    {
        $this->initialize();
        $this->logger->shouldReceive('debug')->with('a debug message')->once();

        SDKLogger::debug('a debug message');
    }
}
