<?php

namespace Bzzix\LaravelLrsPackage\Interactions;

class Earned extends BaseInteraction
{
    public function send(array $data)
    {
        $this->prepareConfig($data);

        $actor = $data['name'];
        $actorEmail = $data['email'];
        $certUrl = $data['certUrl'];
        $certName = $data['certName'];
        $courseId = $data['courseId'];
        $courseTitle = $data['courseName'];
        $courseDesc = '';

        $path = parse_url($certUrl, PHP_URL_PATH);

        $uuid = basename($path);

        $certId = config('app.url') . '/certificates/' . $uuid;


        $vars = array(
            'actor' => array(
                'name' => strval($actor),
                'mbox'  => 'mailto:' . strval($actorEmail),
                'objectType' => 'Agent',
            ),
            'verb' => array(
                'id' => 'http://id.tincanapi.com/verb/earned',
                'display' => array("en-US" => "earned")
            ),
            'object' => array(
                'id' => strval($certId),
                'definition' => array(
                    'name' => array($this->lang => strval($certName)),
                    'type' => 'https://www.opigno.org/en/tincan_registry/activity_type/certificate'
                ),
                'objectType' => 'Activity',
            ),
            'context' => array(
                'extensions' => array(
                    "http://id.tincanapi.com/extension/jws-certificate-location" => strval($certUrl),
                    "https://nelc.gov.sa/extensions/platform" => array(
                        "name" => array(
                            "ar-SA" => strval($this->platform_in_arabic),
                            "en-US" => strval($this->platform_in_english)
                        )
                    )
                ),
                'platform' => strval($this->platform),
                'language' => strval($this->lang),
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
