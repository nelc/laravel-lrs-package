<?php

namespace Bzzix\LaravelLrsPackage\Interactions;

class Completed extends BaseInteraction
{
    public function send(array $data)
    {
        $this->prepareConfig($data);

        $actor = $data['name'];
        $actorEmail = $data['email'];
        $lessonUrl = $data['lessonUrl'];
        $lessonTitle = $data['lessonName'];
        $lessonDesc = $data['lessonDesc'];
        $instructor = $data['instructor'];
        $instructorEmail = $data['inst_email'];
        $courseId = $data['courseId'];
        $courseTitle = $data['courseName'];
        $courseDesc = $data['courseDesc'];
        $lessonDuration = $data['lessonDuration'] ?? '';

        $vars = array(
            'actor' => array(
                'name' => strval($actor),
                'mbox'  => 'mailto:' . strval($actorEmail),
                'objectType' => 'Agent',
            ),
            'verb' => array(
                'id' => 'http://adlnet.gov/expapi/verbs/completed',
                'display' => array("en-US" => "completed")
            ),
            'object' => array(
                'id' => strval($lessonUrl),
                'definition' => array(
                    'name' => array(strval($this->lang) => strval($lessonTitle)),
                    'description' => array(strval($this->lang) => strval($lessonDesc)),
                    'type' => 'http://adlnet.gov/expapi/activities/lesson'
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
                                'name' => array(strval($this->lang) => strval($courseTitle)),
                                'description' => array(strval($this->lang) => strval($courseDesc)),
                                'type' => 'https://w3id.org/xapi/cmi5/activitytype/course'
                            ),
                            'objectType' => "Activity"
                        )
                    )
                )
            ),
            'result' => array(
                'duration' => strval($lessonDuration)
            ),
            'timestamp' => $this->getTimestamp()
        );

        return $vars;
    }
}
