<?php

namespace Bzzix\LaravelLrsPackage\Interactions;

class Attempted extends BaseInteraction
{
    public function send(array $data)
    {
        $this->prepareConfig($data);

        $actor = $data['name'];
        $actorEmail = $data['email'];
        $quizUrl = $data['quizUrl'];
        $quizTitle = $data['quizName'];
        $quizDesc = $data['quizDesc'];
        $attempNumber = $data['attempNumber'];
        $courseId = $data['courseId'];
        $courseTitle = $data['courseName'];
        $courseDesc = $data['courseDesc'];
        $instructor = $data['instructor'];
        $instructorEmail = $data['inst_email'];
        $scaled = $data['scaled'];
        $raw = $data['raw'];
        $min = $data['min'];
        $max = $data['max'];
        $completion = $data['completion'];
        $success = $data['success'];

        $vars = array(
            'actor' => array(
                'name' => strval($actor),
                'mbox'  => 'mailto:' . strval($actorEmail),
                'objectType' => 'Agent',
            ),
            'verb' => array(
                'id' => 'http://adlnet.gov/expapi/verbs/attempted',
                'display' => array("en-US" => "attempted")
            ),
            'object' => array(
                'id' => strval($quizUrl),
                'definition' => array(
                    'name' => array(strval($this->lang) => strval($quizTitle)),
                    'description' => array(strval($this->lang) => strval($quizDesc)),
                    'type' => 'http://id.tincanapi.com/activitytype/unit-test'
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
                "score" => array(
                    "scaled" => $scaled,
                    "raw" => $raw,
                    "min" => $min,
                    "max" => $max
                ),
                'completion' => $completion,
                "success" => $success,
            ),
            'timestamp' => $this->getTimestamp()
        );

        return $vars;
    }
}
