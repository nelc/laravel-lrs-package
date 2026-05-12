<?php

namespace Bzzix\LaravelLrsPackage\Interactions;

class Rated extends BaseInteraction
{
    public function send(array $data)
    {
        $this->prepareConfig($data);

        $actor = $data['name'];
        $actorEmail = $data['email'];
        $courseId = $data['courseId'];
        $courseTitle = $data['courseName'];
        $courseDesc = '';
        $instructor = $data['instructor'];
        $instructorEmail = $data['inst_email'];
        $scaled = $data['scaled'];
        $raw = $data['raw'];
        $comment = $data['comment'] ?? '';

        $vars = array(
            'actor' => array(
                'name' => strval($actor),
                'mbox'  => 'mailto:' . strval($actorEmail),
                'objectType' => 'Agent',
            ),
            'verb' => array(
                'id' => 'http://id.tincanapi.com/verb/rated',
                'display' => array('en-US' => 'rated')
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
            "result" => array(
                "score" => array(
                    "scaled" => $scaled,
                    "raw" => $raw,
                    "min" => 0,
                    "max" => 5
                ),
                "response" => strval($comment)
            ),
            'timestamp' => $this->getTimestamp()
        );

        return $vars;
    }
}
