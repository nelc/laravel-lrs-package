<?php

namespace Bzzix\LaravelLrsPackage\Interactions;

class Registered extends BaseInteraction
{
    public function send(array $data)
    {
        $this->prepareConfig($data);

        $actor = $data['name'];
        $actorEmail = $data['email'];
        $instructor = $data['instructor'];
        $instructorEmail = $data['inst_email'];
        $courseId = $data['courseId'];
        $courseTitle = $data['courseName'];
        $courseDesc = $data['courseDesc'];
        
        $duration = $data['duration'] ?? '';
        $learneMobileNo = $data['learneMobileNo'] ?? '';
        $learnerFullName = $data['learnerFullName'] ?? '';
        $learnerNationality = $data['learnerNationality'] ?? '';
        $dateOfBirth = $data['dateOfBirth'] ?? '';
        $lmsUrl = $data['lmsUrl'] ?? url('/');

        $vars = array(
            'actor' => array(
                'mbox'  => 'mailto:' . strval($actorEmail),
                'name' => strval($actor),
                'objectType' => 'Agent',
            ),
            'verb' => array(
                'id' => 'http://adlnet.gov/expapi/verbs/registered',
                'display' => array('en-US' => 'registered')
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
                    'https://nelc.gov.sa/extensions/duration' => $duration,
                    "https://nelc.gov.sa/extensions/lms_url" => $lmsUrl,
                    "https://nelc.gov.sa/extensions/program_url" => strval($courseId),
                    'https://nelc.gov.sa/extensions/learner_mobile_no' => $learneMobileNo,
                    'https://nelc.gov.sa/extensions/learner_full_name' => $learnerFullName,
                    'https://nelc.gov.sa/extensions/learner_nationality' => $learnerNationality,
                    'https://nelc.gov.sa/extensions/date_of_birth' => $dateOfBirth,
                    'https://nelc.gov.sa/extensions/platform' => array(
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
