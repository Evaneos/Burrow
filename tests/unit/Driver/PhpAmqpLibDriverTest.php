<?php


namespace Burrow\Test\Driver;


use Burrow\Driver\PhpAmqpLibDriver;
use Burrow\Message;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;
use PhpAmqpLib\Exception\AMQPIOException;
use PhpAmqpLib\Exception\AMQPSocketException;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use PHPUnit\Framework\TestCase;

class PhpAmqpLibDriverTest extends TestCase
{
    /** @var AbstractConnection|\PHPUnit_Framework_MockObject_MockObject */
    private $connection;
    /** @var PhpAmqpLibDriver */
    private $sut;

    public function setUp()
    {
        $this->connection = $this->getMockBuilder(AbstractConnection::class)->disableOriginalConstructor()->getMock();

        $this->sut = new PhpAmqpLibDriver($this->connection);
    }

    /** @test */
    public function it_publishes_a_message()
    {
        $channel = $this->getMockBuilder(AMQPChannel::class)->disableOriginalConstructor()->getMock();
        $this->connection->method('channel')->willReturn($channel);

        $channel->expects($this->once())->method('basic_publish')->with(
            new AMQPMessage(
                'Test message !',
                [
                    'delivery_mode' => 2,
                    'content_type' => 'text/plain',
                    'application_headers' => new AMQPTable(['header_1' => 'test']),
                    'correlation_id' => 'correlation_123',
                    'reply_to' => 'reply_to_me',
                ]
            ),
            'test_exchange',
            'routing.key'
        );

        $this->sut->publish(
            'test_exchange',
            new Message('Test message !', 'routing.key', ['header_1' => 'test'], 'correlation_123', 'reply_to_me')
        );
    }

    /**
     * @dataProvider provideConnectionFailureExceptionExamples
     * @test
     */
    public function it_retries_to_publish_a_message_on_connection_failure(\Exception $exception)
    {
        $channel = $this->getMockBuilder(AMQPChannel::class)->disableOriginalConstructor()->getMock();
        $this->connection->method('channel')->willReturn($channel);

        $channel->expects($this->exactly(2))->method('basic_publish')->with(
            new AMQPMessage(
                'Test message !',
                [
                    'delivery_mode' => 2,
                    'content_type' => 'text/plain',
                    'application_headers' => new AMQPTable(['header_1' => 'test']),
                    'correlation_id' => 'correlation_123',
                    'reply_to' => 'reply_to_me',
                ]
            ),
            'test_exchange',
            'routing.key'
        )->willReturnOnConsecutiveCalls(new \PHPUnit_Framework_MockObject_Stub_Exception($exception), null);

        $this->connection->expects($this->once())->method('reconnect');


        $this->sut->publish(
            'test_exchange',
            new Message('Test message !', 'routing.key', ['header_1' => 'test'], 'correlation_123', 'reply_to_me')
        );
    }

    /**
     * @dataProvider provideConnectionFailureExceptionExamples
     * @test
     */
    public function it_retries_to_publish_a_message_on_connection_failure_only_once(\Exception $exception)
    {
        $channel = $this->getMockBuilder(AMQPChannel::class)->disableOriginalConstructor()->getMock();
        $this->connection->method('channel')->willReturn($channel);

        $channel->expects($this->exactly(2))->method('basic_publish')->with(
            new AMQPMessage(
                'Test message !',
                [
                    'delivery_mode' => 2,
                    'content_type' => 'text/plain',
                    'application_headers' => new AMQPTable(['header_1' => 'test']),
                    'correlation_id' => 'correlation_123',
                    'reply_to' => 'reply_to_me',
                ]
            ),
            'test_exchange',
            'routing.key'
        )->willReturnOnConsecutiveCalls(
            new \PHPUnit_Framework_MockObject_Stub_Exception($exception),
            new \PHPUnit_Framework_MockObject_Stub_Exception($exception)
        );

        $this->connection->expects($this->once())->method('reconnect');

        $this->setExpectedException(get_class($exception));
        $this->sut->publish(
            'test_exchange',
            new Message('Test message !', 'routing.key', ['header_1' => 'test'], 'correlation_123', 'reply_to_me')
        );
    }

    /**
     * @dataProvider provideConnectionFailureExceptionExamples
     * @test
     */
    public function it_retries_to_publish_a_message_on_connection_failure_consecutively(\Exception $exception)
    {
        $channel = $this->getMockBuilder(AMQPChannel::class)->disableOriginalConstructor()->getMock();
        $this->connection->method('channel')->willReturn($channel);
        $this->connection->expects($this->exactly(2))->method('reconnect');

        $AMQPMessage1 = new AMQPMessage(
            'Test message !',
            [
                'delivery_mode' => 2,
                'content_type' => 'text/plain',
                'application_headers' => new AMQPTable(['header_1' => 'test']),
                'correlation_id' => 'correlation_123',
                'reply_to' => 'reply_to_me',
            ]
        );
        $AMQPMessage2 = new AMQPMessage(
            'Test message 2 !',
            [
                'delivery_mode' => 2,
                'content_type' => 'text/plain',
                'application_headers' => new AMQPTable(['header_1' => 'test']),
                'correlation_id' => 'correlation_123',
                'reply_to' => 'reply_to_me',
            ]
        );
        $channel->expects($this->exactly(4))->method('basic_publish')->withConsecutive(
            [$AMQPMessage1, 'test_exchange', 'routing.key'],
            [$AMQPMessage1, 'test_exchange', 'routing.key'],
            [$AMQPMessage2, 'test_exchange', 'routing.key'],
            [$AMQPMessage2, 'test_exchange', 'routing.key']
        )->willReturnOnConsecutiveCalls(
            new \PHPUnit_Framework_MockObject_Stub_Exception($exception),
            null,
            new \PHPUnit_Framework_MockObject_Stub_Exception($exception),
            null
        );

        $this->sut->publish(
            'test_exchange',
            new Message('Test message !', 'routing.key', ['header_1' => 'test'], 'correlation_123', 'reply_to_me')
        );
        $this->sut->publish(
            'test_exchange',
            new Message('Test message 2 !', 'routing.key', ['header_1' => 'test'], 'correlation_123', 'reply_to_me')
        );
    }

    public function provideConnectionFailureExceptionExamples()
    {
        yield [new AMQPSocketException()];
        yield [new AMQPTimeoutException()];
        yield [new AMQPConnectionClosedException()];
        yield [new AMQPIOException()];
    }
}