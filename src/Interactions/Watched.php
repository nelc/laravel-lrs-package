<?php

namespace Nelc\LaravelNelcXapiIntegration\Interactions;

use Illuminate\Support\Facades\App;
use Jenssegers\Agent\Agent;

class Watched
{

    protected $platform_in_arabic;
    protected $platform_in_english;
    protected $platform;
    protected $lang;
    protected $browserName;
    protected $browserVersion;
    protected $browserCode;

    /**
     *  tenanat system not work in constructor because in constructor central is work in it
     * @return void
     */
    public function init()
    {
        $this->platform_in_arabic = config('lrs-nelc-xapi.platform_in_arabic');
        $this->platform_in_english = config('lrs-nelc-xapi.platform_in_english');
        $this->platform = config('lrs-nelc-xapi.platform');
        $this->lang = App::getLocale() === 'ar' ? 'ar-SA' : 'en-US';

        $agent = new Agent();
        $this->browserName = $agent->browser();
        $this->browserVersion = $agent->version($this->browserName);
        $this->browserCode = $agent->platform();

    }

    public function Send($actor, $actorEmail, $lessonUrl, $lessonTitle, $lessonDesc, bool $completion, $duration, $courseId, $courseTitle, $courseDesc, $instructor, $instructorEmail)
    {
        $this->init();
        $data = array(
            'actor' => array(
                'name' => strval($actor),
                'mbox' => 'mailto:' . strval($actorEmail),
                'objectType' => 'Agent',
            ),
            'verb' => array(
                'id' => 'https://w3id.org/xapi/acrossx/verbs/watched',
                'display' => array("en-US" => "watched")
            ),
            'object' => array(
                'id' => strval($lessonUrl),
                'definition' => array(
                    'name' => array("en-US" => strval($lessonTitle)),
                    'description' => array("en-US" => strval($lessonDesc)),
                    'type' => 'https://w3id.org/xapi/video/activity-type/video'
                ),
                'objectType' => 'Activity',
            ),
            'context' => array(
                'instructor' => array(
                    'name' => strval($instructor),
                    'mbox' => 'mailto:' . strval($instructorEmail),
                ),
                'platform' => strval($this->platform),
                'language' => strval($this->lang),
                'extensions' => array(
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
                        array(
                            'id' => strval($courseId),
                            'definition' => array(
                                'name' => array("en-US" => strval($courseTitle)),
                                'description' => array("en-US" => strval($courseDesc)),
                                'type' => 'https://w3id.org/xapi/cmi5/activitytype/course'
                            ),
                            'objectType' => "Activity"
                        ),


                    )
                )
            ),
            'result' => array(
                'completion' => $completion,
                'duration' => $duration,
            ),
            'timestamp' => date('Y-m-d\TH:i:s' . substr((string) microtime(), 1, 4) . '\Z')
        );

        return $data;
    }

}