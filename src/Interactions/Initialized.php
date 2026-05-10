<?php

namespace Bzzix\LaravelLrsPackage\Interactions;

class Initialized extends BaseInteraction
{
    public function send(array $data)
    {
        $this->prepareConfig($data);

        $actor = $data['name'];
        $actorEmail = $data['email'];
        $courseId = $data['courseId'];
        $courseTitle = $data['courseName'];
        $courseDesc = $data['courseDesc'];
        $instructor = $data['instructor'];
        $instructorEmail = $data['inst_email'];

        $vars = array(
            'actor' => array(
                'name' => strval($actor),
                'mbox'  => 'mailto:' . strval($actorEmail),
                'objectType' => 'Agent',
            ),
            'verb' => array(
                'id' => 'http://adlnet.gov/expapi/verbs/initialized',
                'display' => array('en-US' => 'initialized')
            ),
            'object' => array(
                'id' => strval($courseId),
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
                    'mbox' => 'mailto:' . strval($instructorEmail),
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
            'timestamp' => $this->getTimestamp()
        );

        return $vars;
    }
}
