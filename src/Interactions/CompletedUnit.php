<?php

namespace Bzzix\LaravelLrsPackage\Interactions;

class CompletedUnit extends BaseInteraction
{
    public function send(array $data)
    {
        $this->prepareConfig($data);

        $actor = $data['name'];
        $actorEmail = $data['email'];
        $unitUrl = $data['unitUrl'];
        $unitTitle = $data['unitName'];
        $unitDesc = $data['unitDesc'];
        $courseId = $data['courseId'];
        $courseTitle = $data['courseName'];
        $courseDesc = '';
        $instructor = $data['instructor'];
        $instructorEmail = $data['inst_email'];

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
                'id' => strval($unitUrl),
                'definition' => array(
                    'name' => array($this->lang => strval($unitTitle)),
                    'description' => array($this->lang => strval($unitDesc)),
                    'type' => 'http://adlnet.gov/expapi/activities/module'
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
            'timestamp' => $this->getTimestamp()
        );

        return $vars;
    }
}
