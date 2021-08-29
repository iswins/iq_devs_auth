<?php
/**
 * Created by v.taneev.
 */


namespace App\Clients;


use App\Exceptions\ServiceException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

abstract class AbstractRestClient
{
    /**
     * @var Client
     */
    protected $client;

    abstract protected function getServiceName();

    public static function getInstance() {
        return new static();
    }

    protected function config() {
        return config("api.{$this->getServiceName()}");
    }

    protected function getHttpClient() : Client {
        if ($this->client) {
            return $this->client;
        }

        $baseUrl = $this->config()['url'];
        $timeout = (float)$this->config()['timeout'];

        return $this->client ??
            $this->client = new Client(['base_uri' => $baseUrl, 'timeout' => $timeout]);
    }

    protected function get($url, $params = []) {
        try {
            $response = $this->getHttpClient()->get($url, ['query' => $params]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (ClientException $exception) {
            $this->adaptClientException($exception);
        }
    }

    protected function post($url, $params) {
        try {
            $response = $this->getHttpClient()->post($url, ['form_params' => $params]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (ClientException $exception) {
            $this->adaptClientException($exception);
        }
    }

    protected function adaptClientException(ClientException $exception) {
        $code = $exception->getCode();
        $data = json_decode($exception->getResponse()->getBody()->getContents(), true);
        $message = isset($data['error']) && isset($data['error']['message']) ?
            $data['error']['message'] :
            'Unknown error';

        throw new ServiceException($message, $code);
    }
}
