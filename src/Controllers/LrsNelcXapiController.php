<?php

namespace Nelc\LaravelNelcXapiIntegration\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Nelc\LaravelNelcXapiIntegration\XapiIntegration;

class LrsNelcXapiController extends Controller
{

    public function __construct()
    {
    }

    public function getIndex(Request $request)
    {

        return view('lrs-nelc-xapi::index');
    }

    public function postIndex(Request $request)
    {

        $request->validate([
            'xapi_statement' => ['required', 'string', 'in:registered,initialized,watched,completed_lesson,completed_unit,progressed,attempted,completed_course,earned,rated']
        ]);

        $xapi = new XapiIntegration();

        switch ( $request->xapi_statement )
        {
            case "registered":
                $response = $xapi->Registered(
                    '123456789', // Student National ID
                    'betalamoud@gmail.com', // Student Email
                    '123', // Course Id OR url Or slug
                    'New Course',
                    'New Course description',
                    'MR Hassan', // instructor Name
                    'mrhassan@mail.com',  // instructor Email
                );
            break;
            case "initialized":
                $response = $xapi->Initialized(
                    '123456789', // Student National ID
                    'betalamoud@gmail.com', // Student Email
                    '123', // Course Id OR url Or slug
                    'New Course',
                    'New Course description',
                    'MR Hassan', // instructor Name
                    'mrhassan@mail.com',  // instructor Email
                );
            break;
            case "watched":
                $response = $xapi->Watched(
                    '123456789', // Student National ID
                    'betalamoud@gmail.com', // Student Email
                    '/url/to/lesson',
                    'Lesson title',
                    'Lesson description',
                    true, // is lesson completed
                    'PT15M', // watching duration
                    '123',
                    'New Course',
                    'New Course description',
                    'MR Hassan', // instructor Name
                    'mrhassan@mail.com',  // instructor Email
                    
                );
            break;
            case "completed_lesson":
                $response = $xapi->CompletedLesson(
                    '123456789', // Student National ID
                    'betalamoud@gmail.com', // Student Email
                    '/url/to/lesson',
                    'Lesson title',
                    'Lesson description',
                    '123',
                    'New Course',
                    'New Course description',
                    'MR Hassan', // instructor Name
                    'mrhassan@mail.com',  // instructor Email                    
                );
            break;
            case "completed_unit":
                $response = $xapi->CompletedUnit(
                    '123456789', // Student National ID
                    'betalamoud@gmail.com', // Student Email
                    '/url/to/unit',
                    'Unit title',
                    'Unit description',
                    '123',
                    'New Course',
                    'New Course description',
                    'MR Hassan', // instructor Name
                    'mrhassan@mail.com',  // instructor Email                 
                );
            break;
            case "progressed":
                $response = $xapi->Progressed(
                    '123456789', // Student National ID
                    'betalamoud@gmail.com', // Student Email
                    '123',
                    'New Course',
                    'New Course description',
                    'MR Hassan', // instructor Name
                    'mrhassan@mail.com',  // instructor Email
                    0.5,
                    true                   
                );
            break;
            case "attempted":
                $response = $xapi->Attempted(
                    '123456789', // Student National ID
                    'betalamoud@gmail.com', // Student Email
                    '/path/to/quiz',
                    'Quiz title',
                    'Quiz description',
                    1, // attempt Number
                    '123',
                    'New Course',
                    'New Course description',
                    'MR Hassan', // instructor Name
                    'mrhassan@mail.com',  // instructor Email
                    0.5, // scaled
                    30, // raw
                    25, // min
                    50, // max
                    true,
                    true                 
                );
            break;
            case "completed_course":
                $response = $xapi->CompletedCourse(
                    '123456789', // Student National ID
                    'betalamoud@gmail.com', // Student Email
                    '123',
                    'New Course',
                    'New Course description',
                    'MR Hassan', // instructor Name
                    'mrhassan@mail.com',  // instructor Email                 
                );
            break;
            case "earned":
                $response = $xapi->Earned(
                    '123456789', // Student National ID
                    'betalamoud@gmail.com', // Student Email
                    'path/to/cert',
                    'cert name',
                    '123',
                    'New Course',
                    'New Course description',            
                );
            break;
            case "rated":
                $response = $xapi->Rated(
                    '123456789', // Student National ID
                    'betalamoud@gmail.com', // Student Email
                    '123',
                    'New Course',
                    'New Course description',    
                    'MR Hassan', // instructor Name
                    'mrhassan@mail.com',  // instructor Email
                    0.8,
                    5,
                    'Thank you' 
                );
            break;
            default:
                $response = $xapi->Registered(
                    '123456789', // Student National ID
                    'betalamoud@gmail.com', // Student Email
                    '123', // Course Id OR url Or slug
                    'New Course',
                    'New Course description',
                    'MR Hassan', // instructor Name
                    'mrhassan@mail.com',  // instructor Email
                );
        }

        return redirect()->back()->with(['success'=> $response, 'st'=> $request->xapi_statement]);
    }

}