<?php

namespace Bzzix\LaravelLrsPackage\Interactions;

use Illuminate\Support\Facades\App;

abstract class BaseInteraction
{
    protected $platform_in_arabic;
    protected $platform_in_english;
    protected $platform;
    protected $lang;
    protected $browserName;
    protected $browserVersion;
    protected $browserCode;

    /**
     * Prepare configuration values from $data or fallback to config file.
     *
     * @param array $data
     * @return void
     */
    protected function prepareConfig(array $data)
    {
        $this->platform_in_arabic = $data['platform_in_arabic'] ?? config('lrs-nelc-xapi.platform_in_arabic');
        $this->platform_in_english = $data['platform_in_english'] ?? config('lrs-nelc-xapi.platform_in_english');
        
        $this->platform = $data['platform'] ?? config('lrs-nelc-xapi.platform');
        
        $this->lang = $data['lang'] ?? (App::getLocale() === 'ar' ? 'ar-SA' : 'en-US');

        $browser = Browser::getBrowser();
        $this->browserName = $browser['name'];
        $this->browserVersion = $browser['version'];
        $this->browserCode = $browser['platform'];
    }

    /**
     * Generate xAPI compliant timestamp.
     *
     * @return string
     */
    protected function getTimestamp()
    {
        return date('Y-m-d\TH:i:s' . substr((string)microtime(), 1, 4) . '\Z');
    }
}
