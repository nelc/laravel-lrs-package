<?php

namespace Bzzix\LaravelLrsPackage;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Bzzix\LaravelLrsPackage\Interactions\Attempted;
use Bzzix\LaravelLrsPackage\Interactions\Completed;
use Bzzix\LaravelLrsPackage\Interactions\CompletedCourse;
use Bzzix\LaravelLrsPackage\Interactions\CompletedUnit;
use Bzzix\LaravelLrsPackage\Interactions\Earned;
use Bzzix\LaravelLrsPackage\Interactions\Initialized;
use Bzzix\LaravelLrsPackage\Interactions\Progressed;
use Bzzix\LaravelLrsPackage\Interactions\Rated;
use Bzzix\LaravelLrsPackage\Interactions\Registered;
use Bzzix\LaravelLrsPackage\Interactions\Watched;

class XapiIntegration
{
    protected $client;
    protected $headers;
    protected $url;
    protected $key;
    protected $secret;

    public function __construct()
    {
        $this->url = config('lrs-nelc-xapi.endpoint');
        $this->key = config('lrs-nelc-xapi.key');
        $this->secret = config('lrs-nelc-xapi.secret');

        $this->client =  new Client([
            'auth' => [$this->key, $this->secret],
        ]);

        $this->headers = [
            'Content-Type'  => 'application/json',
            'Access-Control-Allow-Origin'   => '*',
        ];
    }

    public function Registered(array $data)
    {
        $instance = new Registered();
        $xapiData = $instance->send($data);

        return $this->sendXAPIRequest($xapiData);
    }

    public function Initialized(array $data)
    {
        $instance = new Initialized();
        $xapiData = $instance->send($data);

        return $this->sendXAPIRequest($xapiData);
    }

    public function Watched(array $data)
    {
        $instance = new Watched();
        $xapiData = $instance->send($data);

        return $this->sendXAPIRequest($xapiData);
    }

    public function CompletedLesson(array $data)
    {
        $instance = new Completed();
        $xapiData = $instance->send($data);

        return $this->sendXAPIRequest($xapiData);
    }

    public function CompletedUnit(array $data)
    {
        $instance = new CompletedUnit();
        $xapiData = $instance->send($data);

        return $this->sendXAPIRequest($xapiData);
    }

    public function CompletedCourse(array $data)
    {
        $instance = new CompletedCourse();
        $xapiData = $instance->send($data);

        return $this->sendXAPIRequest($xapiData);
    }

    public function Progressed(array $data)
    {
        $instance = new Progressed();
        $xapiData = $instance->send($data);

        return $this->sendXAPIRequest($xapiData);
    }

    public function Attempted(array $data)
    {
        $instance = new Attempted();
        $xapiData = $instance->send($data);

        return $this->sendXAPIRequest($xapiData);
    }

    public function Earned(array $data)
    {
        $instance = new Earned();
        $xapiData = $instance->send($data);

        return $this->sendXAPIRequest($xapiData);
    }

    public function Rated(array $data)
    {
        $instance = new Rated();
        $xapiData = $instance->send($data);

        return $this->sendXAPIRequest($xapiData);
    }

    public function sendXAPIRequest($data = [])
    {
        $options = [
            'json' => $data,
            'headers' => $this->headers,
        ];

        try {
            $response = $this->client->post($this->url, $options);

            return [
                'status' => $response->getStatusCode(),
                'message' => $response->getReasonPhrase(),
                'body' => $response->getBody()->getContents(),
            ];
        } catch (RequestException $e) {
            $response = $e->getResponse();

            return [
                'status' => $response ? $response->getStatusCode() : 500,
                'message' => $e->getMessage(),
                'body' => $response ? $response->getBody()->getContents() : '',
            ];
        }
    }
}
