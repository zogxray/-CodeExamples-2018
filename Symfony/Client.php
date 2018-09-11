<?php
/**
 * Created by PhpStorm.
 * User: zogxray
 * Date: 23.07.18
 * Time: 14:42
 */

namespace AsteriskBundle\ARI;

use AsteriskBundle\ARIEntity\Endpoint;
use Psr\Http\Message\MessageInterface;
use AsteriskBundle\Exception\AriEntityException;
use GuzzleHttp\Client as GuzzleHttpClient;
use JMS\Serializer\SerializerInterface;

/**
 * Class Client
 * @package AsteriskBundle\ARI
 */
class Client
{
    /**
     * @var Client
     */
    private $guzzleHttpClient;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var string
     */
    private $apiUrl;
    /**
     * @var string
     */
    private $apiKey;

    /**
     * Request constructor.
     * @param GuzzleHttpClient $guzzleHttpClient
     * @param SerializerInterface $serializer
     * @param string $apiUrl
     * @param string $apiKey
     */
    public function __construct(
        GuzzleHttpClient $guzzleHttpClient,
        SerializerInterface $serializer,
        string $apiUrl,
        string $apiKey
    ) {
        $this->guzzleHttpClient = $guzzleHttpClient;
        $this->serializer = $serializer;
        $this->apiUrl = $apiUrl;
        $this->apiKey = $apiKey;
    }

    /**
     * @param string $deserializeTo
     * @param array $data
     * @param bool $asArray
     * @return mixed
     * @throws AriEntityException
     */
    public function post(string $deserializeTo, array $data = [], bool $asArray = true)
    {
        if (false === class_exists($deserializeTo)) {
            throw new AriEntityException("Class {$deserializeTo} not exist");
        }

        $url = $this->constructRoute($deserializeTo);

        /** @var MessageInterface $response */
        $response = $this->guzzleHttpClient->post(
            $url,
            $data
        );

        return $this->deserialize($response, $deserializeTo, $asArray);
    }

    /**
     * @param string $deserializeTo
     * @param bool $asArray
     * @return mixed
     * @throws AriEntityException
     */
    public function get(string $deserializeTo, bool $asArray = true)
    {
        if (false === class_exists($deserializeTo)) {
            throw new AriEntityException("Class {$deserializeTo} not exist");
        }

        $url = $this->constructRoute($deserializeTo);

        /** @var MessageInterface $response */
        $response = $this->guzzleHttpClient->get($url);


        return $this->deserialize($response, $deserializeTo, $asArray);
    }

    /**
     * @return Endpoint[]
     *
     * @throws AriEntityException
     */
    public function getOnlineEndpoints(): array
    {
        return $this->get(Endpoint::class, true);
    }

    /**
     * @param MessageInterface $response
     * @param string $deserializeTo
     * @param bool $asArray
     *
     * @return array|\JMS\Serializer\scalar|object
     */
    private function deserialize(MessageInterface $response, string $deserializeTo, bool $asArray = true)
    {
        if ($asArray) {
            return $this->serializer->deserialize($content = $response->getBody()->getContents(), 'array<'.$deserializeTo.'>', 'json');
        }

        return $this->serializer->deserialize($content = $response->getBody()->getContents(), $deserializeTo, 'json');
    }

    /**
     * @param string $deserializeTo
     *
     * @return string
     */
    private function constructRoute(string $deserializeTo): string
    {
        return $this->apiUrl.$deserializeTo::ROUTE.'?api_key='.$this->apiKey;
    }
}
