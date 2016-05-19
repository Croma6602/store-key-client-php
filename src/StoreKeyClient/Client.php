<?php

namespace Wadify\StoreKeyClient;

use Aws\DynamoDb\DynamoDbClient;

class Client
{
    /**
     * @var DynamoDbClient
     */
    private $dynamoDbClient;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string
     */
    private $attribute;

    /**
     * StoreKeyClient constructor.
     *
     * @param DynamoDbClient $dynamoDbClient
     * @param string         $tableName
     * @param string         $attribute
     */
    public function __construct(DynamoDbClient $dynamoDbClient, $tableName, $attribute)
    {
        $this->dynamoDbClient = $dynamoDbClient;
        $this->tableName = $tableName;
        $this->attribute = $attribute;
    }

    /**
     * @param $value
     *
     * @return string
     */
    private function getVariableType($value)
    {
        switch (gettype($value)) {
            case 'integer':
                return 'N';
            default:
                return 'S';
        }
    }

    /**
     * @param $key
     * @param $value
     *
     * @return int|string
     */
    private function getCastedResponse($key, $value)
    {
        if ($key === 'N') {
            return (int) $value;
        }

        return (string) $value;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function store($key, $value)
    {
        $this->dynamoDbClient->putItem([
            'TableName' => $this->tableName,
            'Item' => [
                $this->attribute => [$this->getVariableType($key) => (string) $key],
                'status' => [$this->getVariableType($value) => (string) $value],
            ],
        ]);
    }

    /**
     * @deprecated deprecated since version 2.0
     *
     * @param string $key
     *
     * @returns mixed
     */
    public function get($key)
    {
        $response = $this->dynamoDbClient->getItem([
            'TableName' => $this->tableName,
            'Key' => [
                $this->attribute => [$this->getVariableType($key) => (string) $key],
            ],
        ]);

        return $response['Item'];
    }

    /**
     * @param $key
     *
     * @return int|string
     */
    public function getValue($key)
    {
        $response = $this->dynamoDbClient->getItem([
            'TableName' => $this->tableName,
            'Key' => [
                $this->attribute => [$this->getVariableType($key) => (string) $key],
            ],
        ]);
        $array = $response['Item']['status'];
        $value = reset($array);
        $key = key($array);

        return $this->getCastedResponse($key, $value);
    }

    /**
     * @param $key
     */
    public function delete($key)
    {
        $this->dynamoDbClient->deleteItem([
            'TableName' => $this->tableName,
            'Key' => [
                $this->attribute => [$this->getVariableType($key) => (string) $key],
            ],
        ]);
    }
}
