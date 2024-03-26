<?php

namespace Nelc\LaravelNelcXapiIntegration\Interactions;

use Illuminate\Support\Facades\App;
use Jenssegers\Agent\Agent;

class Attempted
{

    protected $platform_in_arabic;
    protected $platform_in_english;
    protected $platform;
    protected $lang;
    protected $browserName;
    protected $browserVersion;
    protected $browserCode;

    public function __construct()
    {
        $this->platform_in_arabic = config('platform_in_arabic');
        $this->platform_in_english = config('platform_in_english');
        $this->platform = App::getLocale() === 'ar' ? $this->platform_in_arabic : $this->platform_in_english;
        $this->lang = App::getLocale() === 'ar' ? 'ar-SA' : 'en-US';

        $agent = new Agent();
        $this->browserName = $agent->browser();
        $this->browserVersion = $agent->version($this->browserName);
        $this->browserCode = $agent->platform();

    }

    public function Send( $actor, $actorEmail, $quizUrl, $quizTitle, $quizDesc, $attempNumber, $courseId, $courseTitle, $courseDesc, $instructor, $instructorEmail, $scaled, $raw, $min, $max, bool $completion, bool $success ){

        $data = array(
            'actor' => array(
                'name' => strval($actor),
                'mbox'  => 'mailto:'.strval($actorEmail),
                        'objectType' => 'Agent',
                    ),
            'verb' => array(
                        'id' => 'http://adlnet.gov/expapi/verbs/attempted',
                        'display' => array("en-US" => "attempted") 
                    ),
            'object' => array(
                            'id'=> strval($quizUrl),
                            'definition' => array(
                                'name' => array( strval($this->lang) => strval($quizTitle) ),
                                'description' => array( strval($this->lang) => strval($quizDesc) ),
                                'type' => 'http://id.tincanapi.com/activitytype/unit-test'
                            ),
                            'objectType' => 'Activity',
                        ),
            'context' => array(
                            'instructor' => array(
                                'name' => strval($instructor),
                                'mbox' => 'mailto:'.strval($instructorEmail),
                            ),
                            'platform' => strval($this->platform),
                            'language' => strval($this->lang),
                            'extensions' => array (
                                "http://id.tincanapi.com/extension/attempt-id" => strval($attempNumber),
                                "http://id.tincanapi.com/extension/browser-info" => array(
                                    "code_name" => strval($this->browserCode),
                                    "name" => strval($this->browserName),  
                                    "version" => strval($this->browserVersion)
                                ),
                                "https://nelc.gov.sa/extensions/platform" => array(
                                    "name" => array(
                                        "ar-SA" => strval($this->platform_in_arabic),
                                        "en-US" => strval($this->platform_in_english)
                                    )
                                )
                            ),
                            'contextActivities' => array(
                                'parent' => array(
                                    array (
                                        'id' => strval($courseId),
                                        'definition' => array(  
                                                'name' => array(strval($this->lang) => strval($courseTitle)),
                                                'description' => array( strval($this->lang) => strval($courseDesc) ),
                                                'type' => 'https://w3id.org/xapi/cmi5/activitytype/course'
                                        ),
                                        'objectType' => "Activity"
                                    )
                                )
                            )
                        ),
                        'result' => array(
                            "score" => array(
                                "scaled" => $scaled,
                                "raw" => $raw,
                                "min" => $min,
                                "max" => $max
                            ),
                            'completion' => $completion,
                            "success" => $success,
                        ),
            'timestamp' => date('Y-m-d\TH:i:s'.substr((string)microtime(), 1, 4).'\Z')
        );

        return $data;
    }
    
}