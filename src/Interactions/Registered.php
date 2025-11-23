<?php

namespace Nelc\LaravelNelcXapiIntegration\Interactions;

use Illuminate\Support\Facades\App;

class Registered
{

    protected $platform_in_arabic;
    protected $platform_in_english;
    protected $platform;
    protected $lang;

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
    }

    public function Send($actor, $actorEmail, $actorName, $actorMobile, $actorBirth, $actorNationality, $courseId, $courseTitle, $courseDesc, $instructor, $instructorEmail, $duration, $lms_url, $program_url)
    {
        $this->init();
        $data = array(
            'actor' => array(
                'name' => strval($actor),
                'mbox' => 'mailto:' . strval($actorEmail),
                'objectType' => 'Agent',
            ),
            'verb' => array(
                'id' => 'http://adlnet.gov/expapi/verbs/registered',
                'display' => array('en-US' => 'registered')
            ),
            'object' => array(
                'id' => strval($courseId),
                'definition' => array(
                    'name' => array('en-US' => strval($courseTitle)),
                    'description' => array('en-US' => strval($courseDesc)),
                    'type' => 'https://w3id.org/xapi/cmi5/activitytype/course'
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
                "extensions" => array(
                    "https://nelc.gov.sa/extensions/duration" => strval($duration),
                    "https://nelc.gov.sa/extensions/lms_url" => strval($lms_url),
                    "https://nelc.gov.sa/extensions/program_url" => strval($program_url),
                    "https://nelc.gov.sa/extensions/learner_mobile_no" => strval($actorMobile),
                    "https://nelc.gov.sa/extensions/learner_full_name" => strval($actorName),
                    "https://nelc.gov.sa/extensions/learner_nationality" => strval($actorNationality),
                    "https://nelc.gov.sa/extensions/learner_date_of_birth" => strval($actorBirth),
                    "https://nelc.gov.sa/extensions/platform" => array(
                        "name" => array(
                            "ar-SA" => strval($this->platform_in_arabic),
                            "en-US" => strval($this->platform_in_english)
                        )
                    )
                )
            ),
            'timestamp' => date('Y-m-d\TH:i:s' . substr((string) microtime(), 1, 4) . '\Z')
        );

        return $data;
    }

}