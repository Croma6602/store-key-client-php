<?php

namespace Wadify\Test\StoreKeyClient\Client;

use Aws\Result;
use Wadify\StoreKeyClient\Client;
use Aws\DynamoDb\DynamoDbClient;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DynamoDbClient
     */
    private $mockDynamoDbClient;

    /**
     * @var Client
     */
    private $storeKeyClient;

    /**
     * @var string
     */
    private $storeKeyTableName = 'table_name';

    /**
     * @var string
     */
    private $storeKeyAttribute = 'foo_attribute';

    public function testStoreAValueByKey()
    {
        // Arrange.
        $key = 'foo';
        $value = 7;
        $this->mockDynamoDbClient
            ->expects($this->once())
            ->method('putItem')
            ->with([
                'TableName' => $this->storeKeyTableName,
                'Item' => [
                    $this->storeKeyAttribute => ['S' => $key],
                    'status' => ['N' => $value],
                ],
            ]);

        // Act.
        $this->storeKeyClient->store($key, $value);

        // Assert in arrange.
    }

    public function testGetItemCallsGetItemAndReturnsAValue()
    {
        // Arrange.
        $expected = 'foo';
        $key = 'bar';
        $result = new Result(['Item' => $expected]);
        $this->mockDynamoDbClient
            ->expects($this->once())
            ->method('getItem')
            ->with([
                'TableName' => $this->storeKeyTableName,
                'Key' => [
                    $this->storeKeyAttribute => ['S' => $key],
                ],
            ])
            ->willReturn($result);

        // Act.
        $actual = $this->storeKeyClient->get($key);

        // Assert.
        $this->assertEquals($expected, $actual);
    }

    public function testGetWithNonExistentKey()
    {
        // Arrange.
        $stringKey = 'foo';
        $result = new Result([]);
        $this->mockDynamoDbClient
            ->expects($this->once())
            ->method('getItem')
            ->with([
                'TableName' => $this->storeKeyTableName,
                'Key' => [
                    $this->storeKeyAttribute => ['S' => $stringKey],
                ],
            ])
            ->willReturn($result);

        // Act.
        $actual = $this->storeKeyClient->get($stringKey);

        // Assert.
        $this->assertFalse($actual);
    }

    public function testStoreKeyAndValueSentByIntegerHaveToBeSentAsString()
    {
        // Arrange.
        $integerKey = 1;
        $integerValue = 2;
        $stringKey = (string) $integerKey;
        $stringValue = (string) $integerValue;

        $this->mockDynamoDbClient
            ->expects($this->once())
            ->method('putItem')
            ->with([
                'TableName' => $this->storeKeyTableName,
                'Item' => [
                    $this->storeKeyAttribute => ['N' => $stringKey],
                    'status' => ['N' => $stringValue],
                ],
            ]);

        // Act.
        $this->storeKeyClient->store($integerKey, $integerValue);

        // Assert in arrange.
    }

    public function testGetKeyAndValueSentByIntegerHasToBeSentAsString()
    {
        // Arrange.
        $integerKey = 1;
        $stringKey = (string) $integerKey;
        $expected = 1;
        $result = new Result(['Item' => $expected]);
        $this->mockDynamoDbClient
            ->expects($this->once())
            ->method('getItem')
            ->with([
                'TableName' => $this->storeKeyTableName,
                'Key' => [
                    $this->storeKeyAttribute => ['N' => $stringKey],
                ],
            ])
            ->willReturn($result);

        // Act.
        $actual = $this->storeKeyClient->get($integerKey);

        // Assert.
        $this->assertEquals($expected, $actual);
    }

    public function testGetValueAndValueHasToBeAnInteger()
    {
        // Arrange.
        $integerKey = 1;
        $stringKey = (string) $integerKey;
        $expected = 1;
        $result = new Result(['Item' => [
            'status' => ['N' => $expected],
        ]]);
        $this->mockDynamoDbClient
            ->expects($this->once())
            ->method('getItem')
            ->with([
                'TableName' => $this->storeKeyTableName,
                'Key' => [
                    $this->storeKeyAttribute => ['N' => $stringKey],
                ],
            ])
            ->willReturn($result);

        // Act.
        $actual = $this->storeKeyClient->getValue($integerKey);

        // Assert.
        $this->assertEquals($expected, $actual);
    }

    public function testGetValueAndValueHasToBeAnString()
    {
        // Arrange.
        $stringKey = 'foo';
        $expected = 'bar';
        $result = new Result(['Item' => [
            'status' => ['S' => $expected],
        ]]);
        $this->mockDynamoDbClient
            ->expects($this->once())
            ->method('getItem')
            ->with([
                'TableName' => $this->storeKeyTableName,
                'Key' => [
                    $this->storeKeyAttribute => ['S' => $stringKey],
                ],
            ])
            ->willReturn($result);

        // Act.
        $actual = $this->storeKeyClient->getValue($stringKey);

        // Assert.
        $this->assertEquals($expected, $actual);
    }

    public function testGetValueWithNonExistentKey()
    {
        // Arrange.
        $stringKey = 'foo';
        $result = new Result([]);
        $this->mockDynamoDbClient
            ->expects($this->once())
            ->method('getItem')
            ->with([
                'TableName' => $this->storeKeyTableName,
                'Key' => [
                    $this->storeKeyAttribute => ['S' => $stringKey],
                ],
            ])
            ->willReturn($result);

        // Act.
        $actual = $this->storeKeyClient->getValue($stringKey);

        // Assert.
        $this->assertFalse($actual);
    }

    public function testRemoveAKey()
    {
        // Arrange.
        $key = 'bar';
        $this->mockDynamoDbClient
            ->expects($this->once())
            ->method('deleteItem')
            ->with([
                'TableName' => $this->storeKeyTableName,
                'Key' => [
                    $this->storeKeyAttribute => ['S' => $key],
                ],
            ]);

        // Act.
        $this->storeKeyClient->delete($key);

        // Assert in arrange.
    }

    public function setUp()
    {
        $this->mockDynamoDbClient = $this->getMockBuilder(DynamoDbClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['putItem', 'getItem', 'deleteItem'])
            ->getMock();

        $this->storeKeyClient = new Client(
            $this->mockDynamoDbClient,
            $this->storeKeyTableName,
            $this->storeKeyAttribute
        );
    }
}
