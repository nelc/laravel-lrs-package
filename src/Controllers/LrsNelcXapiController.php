<?php

namespace Bzzix\LaravelLrsPackage\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Bzzix\LaravelLrsPackage\XapiIntegration;

class LrsNelcXapiController extends Controller
{
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
        $statement = $request->xapi_statement;

        // Common dummy data for testing
        $commonData = [
            'name' => '1234567890', // National ID
            'email' => 'student@example.com',
            'courseId' => 'http://example.com/course/123',
            'courseName' => 'Advanced Laravel Development',
            'courseDesc' => 'Mastering Laravel and xAPI Integration',
            'instructor' => 'Bzzix Expert',
            'inst_email' => 'expert@bzzix.com',
        ];

        switch ($statement) {
            case "registered":
                $data = array_merge($commonData, [
                    'duration' => 'PT10H',
                    'learnerFullName' => 'Ahmed Mohamed Ali',
                    'learnerMobileNo' => '+966500000000',
                    'learnerNationality' => 'Saudi',
                    'dateOfBirth' => '1995-05-15',
                ]);
                $response = $xapi->Registered($data);
                break;

            case "initialized":
                $response = $xapi->Initialized($commonData);
                break;

            case "watched":
                $data = array_merge($commonData, [
                    'lessonUrl' => 'http://example.com/lesson/1',
                    'lessonName' => 'Introduction to xAPI',
                    'lessonDesc' => 'Basic concepts of xAPI',
                    'completion' => true,
                    'duration' => 'PT15M',
                ]);
                $response = $xapi->Watched($data);
                break;

            case "completed_lesson":
                $data = array_merge($commonData, [
                    'lessonUrl' => 'http://example.com/lesson/1',
                    'lessonName' => 'Introduction to xAPI',
                    'lessonDesc' => 'Basic concepts of xAPI',
                    'lessonDuration' => 'PT20M',
                ]);
                $response = $xapi->CompletedLesson($data);
                break;

            case "completed_unit":
                $data = array_merge($commonData, [
                    'unitUrl' => 'http://example.com/unit/1',
                    'unitName' => 'Module 1: Fundamentals',
                    'unitDesc' => 'Core concepts of the system',
                ]);
                $response = $xapi->CompletedUnit($data);
                break;

            case "progressed":
                $data = array_merge($commonData, [
                    'scaled' => 0.75,
                    'completion' => false,
                ]);
                $response = $xapi->Progressed($data);
                break;

            case "attempted":
                $data = array_merge($commonData, [
                    'quizUrl' => 'http://example.com/quiz/1',
                    'quizName' => 'Final Exam',
                    'quizDesc' => 'Testing your knowledge',
                    'attempNumber' => '1',
                    'scaled' => 0.95,
                    'raw' => 95,
                    'min' => 0,
                    'max' => 100,
                    'completion' => true,
                    'success' => true,
                ]);
                $response = $xapi->Attempted($data);
                break;

            case "completed_course":
                $response = $xapi->CompletedCourse($commonData);
                break;

            case "earned":
                $data = array_merge($commonData, [
                    'certUrl' => 'http://example.com/certificates/123.pdf',
                    'certName' => 'Full Stack Developer Certificate',
                ]);
                $response = $xapi->Earned($data);
                break;

            case "rated":
                $data = array_merge($commonData, [
                    'scaled' => 1.0,
                    'raw' => 5,
                    'comment' => 'This package is amazing! Very easy to use.',
                ]);
                $response = $xapi->Rated($data);
                break;

            default:
                $response = $xapi->Registered($commonData);
        }

        return redirect()->back()->with(['success' => $response, 'st' => $statement]);
    }
}
