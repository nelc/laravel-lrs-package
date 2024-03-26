<?php

namespace Nelc\LaravelNelcXapiIntegration\Interactions;

use Illuminate\Support\Facades\App;

class Earned
{

    protected $platform_in_arabic;
    protected $platform_in_english;
    protected $platform;
    protected $lang;

    public function __construct()
    {
        $this->platform_in_arabic = config('platform_in_arabic');
        $this->platform_in_english = config('platform_in_english');
        $this->platform = App::getLocale() === 'ar' ? $this->platform_in_arabic : $this->platform_in_english;
        $this->lang = App::getLocale() === 'ar' ? 'ar-SA' : 'en-US';
    }

    public function Send( $actor, $actorEmail, $certUrl, $certName, $courseId, $courseTitle, $courseDesc ){

        $data = array(
            'actor' => array(
                'name' => strval($actor),
                'mbox'  => 'mailto:'.strval($actorEmail),
                        'objectType' => 'Agent',
                    ),
            'verb' => array(
                        'id' => 'http://id.tincanapi.com/verb/earned',
                        'display' => array("en-US" => "earned") 
                    ),
            'object' => array(
                            'id'=> strval($certUrl),
                            'definition' => array(
                                'name' => array($this->lang => strval($certName)),
                                'type' => 'https://www.opigno.org/en/tincan_registry/activity_type/certificate'
                            ),
                            'objectType' => 'Activity',
                        ),
            'context' => array(
                            'extensions' => array (
                                "http://id.tincanapi.com/extension/jws-certificate-location" => strval($certUrl),
                                "https://nelc.gov.sa/extensions/platform" => array(
                                    "name" => array(
                                        "ar-SA" => strval($this->platform_in_arabic),
                                        "en-US" => strval($this->platform_in_english)
                                    )
                                )
                            ),
                            'platform' => strval($this->platform),
                            'language' => strval($this->lang),
                            'contextActivities' => array(
                                'parent' => array(
                                    array (
                                        'id' => strval($courseId),
                                        'definition' => array(  
                                            'name' => array(strval($this->lang) => strval($courseTitle)),
                                            'description' => array( strval($this->lang) => strval($courseDesc) ),                                            'type' => 'https://w3id.org/xapi/cmi5/activitytype/course'
                                        ),
                                        'objectType' => "Activity"
                                    )
                                )
                            )
                        ),
            'timestamp' => date('Y-m-d\TH:i:s'.substr((string)microtime(), 1, 4).'\Z')
        );

        return $data;
    }
    
}