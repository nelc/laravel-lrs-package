<?php

namespace Nelc\LaravelNelcXapiIntegration\Interactions;

use Illuminate\Support\Facades\App;
use cuteminded\Agent\Agent;

class Rated
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
       $this->platform_in_arabic = config('lrs-nelc-xapi.platform_in_arabic');
        $this->platform_in_english = config('lrs-nelc-xapi.platform_in_english');
        $this->lang = App::getLocale() === 'ar' ? 'ar-SA' : 'en-US';
        $agent = new Agent();
        $this->browserName = $agent->browser();
        $this->browserVersion = $agent->version($this->browserName);
        $this->browserCode = $agent->platform();
        $this->platform = App::getLocale() === 'ar' ? $this->platform_in_arabic : $this->platform_in_english;
        $this->lang = App::getLocale() === 'ar' ? 'ar-SA' : 'en-US';
    }

    public function Send( $actor, $actorEmail, $courseId, $courseTitle, $courseDesc, $instructor, $instructorEmail, $scaled, $raw, $comment ){

        $data =     array(
            'actor' => array(
                'name' => strval($actor),
                'mbox'  => 'mailto:'.strval($actorEmail),
                        'objectType' => 'Agent',
                    ),
            'verb' => array(
                        'id' => 'http://id.tincanapi.com/verb/rated',
                        'display' => array('en-US' => 'rated') 
                    ),
            'object' => array(
                            'id'=> strval($courseId),
                            'definition' => array(
                                'name' => array(strval($this->lang) => strval($courseTitle)),
                                'description' => array( strval($this->lang) => strval($courseDesc) ), 
                                'type' => 'https://w3id.org/xapi/cmi5/activitytype/course'
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
                            "extensions" => array(
                                "https://nelc.gov.sa/extensions/platform" => array(
                                    "name" => array(
                                        "ar-SA" => strval($this->platform_in_arabic),
                                        "en-US" => strval($this->platform_in_english)
                                    )
                                )
                            )
                        ),
            "result" => array(
                        "score" => array(
                            "scaled" => $scaled,
                            "raw" => $raw,
                                "min" => 0,
                                "max" => 5
                        ),
                        "response" => strval($comment)
                    ),
            'timestamp' => date('Y-m-d\TH:i:s'.substr((string)microtime(), 1, 4).'\Z')
        );

        return $data;
    }
    
}