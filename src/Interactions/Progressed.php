<?php

namespace Nelc\LaravelNelcXapiIntegration\Interactions;

use Illuminate\Support\Facades\App;

class Progressed
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

    public function Send( $actor, $actorEmail, $courseId, $courseTitle, $courseDesc, $instructor, $instructorEmail, $scaled, bool $completion ){

        $data = array(
            'actor' => array(
                        'name' => strval($actor),
                        'mbox'  => 'mailto:'.strval($actorEmail),
                        'objectType' => 'Agent',
                    ),
            'verb' => array(
                        'id' => 'http://adlnet.gov/expapi/verbs/progressed',
                        'display' => array("en-US" => "progressed") 
                    ),
            'object' => array(
                            'id'=> strval($courseId),
                            'definition' => array(
                                'name' => array(strval($this->lang) => strval($courseTitle)),
                                'description' => array(strval($this->lang) => strval($courseDesc)),
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
            'result' => array(
                            "score" => array(
                                "scaled" =>  $scaled
                                ),
                            "completion" => $completion,
                ),                
            'timestamp' => date('Y-m-d\TH:i:s'.substr((string)microtime(), 1, 4).'\Z')
        );

        return $data;
    }
    
}