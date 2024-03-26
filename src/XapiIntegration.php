<?php

namespace Nelc\LaravelNelcXapiIntegration;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Nelc\LaravelNelcXapiIntegration\Interactions\Attempted;
use Nelc\LaravelNelcXapiIntegration\Interactions\Completed;
use Nelc\LaravelNelcXapiIntegration\Interactions\CompletedCourse;
use Nelc\LaravelNelcXapiIntegration\Interactions\CompletedUnit;
use Nelc\LaravelNelcXapiIntegration\Interactions\Earned;
use Nelc\LaravelNelcXapiIntegration\Interactions\Initialized;
use Nelc\LaravelNelcXapiIntegration\Interactions\Progressed;
use Nelc\LaravelNelcXapiIntegration\Interactions\Rated;
use Nelc\LaravelNelcXapiIntegration\Interactions\Registered;
use Nelc\LaravelNelcXapiIntegration\Interactions\Watched;

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

    public function Registered( $actor, $actorEmail, $courseId, $courseTitle, $courseDesc, $instructor, $instructorEmail)
    {
        $instance = new Registered();
        $data = $instance->Send( $actor, $actorEmail, $courseId, $courseTitle, $courseDesc, $instructor, $instructorEmail);

        return $this->sendXAPIRequest( $data );
    }

    public function Initialized( $actor, $actorEmail, $courseId, $courseTitle, $courseDesc, $instructor, $instructorEmail)
    {
        $instance = new Initialized();
        $data = $instance->Send( $actor, $actorEmail, $courseId, $courseTitle, $courseDesc, $instructor, $instructorEmail);

        return $this->sendXAPIRequest( $data );
    }

    public function Watched( $actor, $actorEmail, $lessonUrl, $lessonTitle, $lessonDesc, bool $completion, $duration, $courseId, $courseTitle, $courseDesc, $instructor, $instructorEmail )
    {
        $instance = new Watched();
        $data = $instance->Send( $actor, $actorEmail, $lessonUrl, $lessonTitle, $lessonDesc, $completion, $duration, $courseId, $courseTitle, $courseDesc, $instructor, $instructorEmail );

        return $this->sendXAPIRequest( $data );
    }

    public function CompletedLesson( $actor, $actorEmail, $lessonUrl, $lessonTitle, $lessonDesc, $courseId, $courseTitle, $courseDesc, $instructor, $instructorEmail )
    {
        $instance = new Completed();
        $data = $instance->Send( $actor, $actorEmail, $lessonUrl, $lessonTitle, $lessonDesc, $courseId, $courseTitle, $courseDesc, $instructor, $instructorEmail );

        return $this->sendXAPIRequest( $data );
    }

    public function CompletedUnit( $actor, $actorEmail, $unitUrl, $unitTitle, $unitDesc, $courseId, $courseTitle, $courseDesc, $instructor, $instructorEmail )
    {
        $instance = new CompletedUnit();
        $data = $instance->Send( $actor, $actorEmail, $unitUrl, $unitTitle, $unitDesc, $courseId, $courseTitle, $courseDesc, $instructor, $instructorEmail );

        return $this->sendXAPIRequest( $data );
    }

    public function CompletedCourse( $actor, $actorEmail, $courseId, $courseTitle, $courseDesc, $instructor, $instructorEmail )
    {
        $instance = new CompletedCourse();
        $data = $instance->Send( $actor, $actorEmail, $courseId, $courseTitle, $courseDesc, $instructor, $instructorEmail );

        return $this->sendXAPIRequest( $data );
    }

    public function Progressed( $actor, $actorEmail, $courseId, $courseTitle, $courseDesc, $instructor, $instructorEmail, $scaled, bool $completion )
    {
        $instance = new Progressed();
        $data = $instance->Send( $actor, $actorEmail, $courseId, $courseTitle, $courseDesc, $instructor, $instructorEmail, $scaled, $completion );

        return $this->sendXAPIRequest( $data );
    }

    public function Attempted( $actor, $actorEmail, $quizUrl, $quizTitle, $quizDesc, $attempNumber, $courseId, $courseTitle, $courseDesc, $instructor, $instructorEmail, $scaled, $raw, $min, $max, bool $completion, bool $success )
    {
        $instance = new Attempted();
        $data = $instance->Send( $actor, $actorEmail, $quizUrl, $quizTitle, $quizDesc, $attempNumber, $courseId, $courseTitle, $courseDesc, $instructor, $instructorEmail, $scaled, $raw, $min, $max, $completion, $success  );

        return $this->sendXAPIRequest( $data );
    }

    public function Earned( $actor, $actorEmail, $certUrl, $certName, $courseId, $courseTitle, $courseDesc )
    {
        $instance = new Earned();
        $data = $instance->Send( $actor, $actorEmail, $certUrl, $certName, $courseId, $courseTitle, $courseDesc );

        return $this->sendXAPIRequest( $data );
    }

    public function Rated( $actor, $actorEmail, $courseId, $courseTitle, $courseDesc, $instructor, $instructorEmail, $scaled, $raw, $comment )
    {
        $instance = new Rated();
        $data = $instance->Send( $actor, $actorEmail, $courseId, $courseTitle, $courseDesc, $instructor, $instructorEmail, $scaled, $raw, $comment );

        return $this->sendXAPIRequest( $data );
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
                'status' => $response->getStatusCode(),
                'message' => $response->getReasonPhrase(),
                'body' => $response->getBody()->getContents(),
            ];
        }

    }

}