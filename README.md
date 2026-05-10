# Laravel Nelc Xapi Integration

Laravel package for integrating with the Saudi National Center for e-Learning (NELC) xAPI system.

---
## 🌐 [English Version](#english-documentation) | [النسخة العربية](#الوثائق-باللغة-العربية)
---

<a name="english-documentation"></a>
## English Documentation

### Features
- Full support for all NELC-required xAPI statements.
- Flexible configuration: Use global config or override per request.
- Automatic browser and OS metadata detection.
- Comprehensive documentation for all use cases.

### Installation

1. **Install Package:** `composer require bzzix/laravel-lrs-package`
2. **Publish Config:** `php artisan vendor:publish --provider="Bzzix\LaravelLrsPackage\NelcXapiServiceProvider"`
3. **Environment (.env):**
```env
LRS_ENDPOINT=https://your-lrs-endpoint.com/xapi/statements
LRS_USERNAME=your_key
LRS_PASSWORD=your_secret
LRS_PLATFORM_AR="Platform Name AR"
LRS_PLATFORM_EN="Platform Name EN"
LRS_PLATFORM="SHORT_ID"
```

### Usage Reference (Statements)

All interactions are called using a single `$data` array.

#### 1. Registered
Sent when a student enrolls in a course.
```php
$xapi->Registered([
    'name' => '1234567890', // Student National ID
    'email' => 'student@email.com',
    'courseId' => 'Course URL',
    'courseName' => 'Course Title',
    'courseDesc' => 'Course Description',
    'instructor' => 'Instructor Name',
    'inst_email' => 'instructor@email.com',
    // Optional NELC Extensions
    'duration' => 'PT10H', // ISO 8601 Duration
    'learnerFullName' => 'Full Name',
    'learnerMobileNo' => '+966...',
    'learnerNationality' => 'Saudi',
    'dateOfBirth' => 'YYYY-MM-DD',
    'lmsUrl' => 'LMS Base URL'
]);
```

#### 2. Initialized
Sent when a student starts an activity.
```php
$xapi->Initialized([
    'name' => '1234567890', // National ID
    'email' => '...',
    'courseId' => '...',
    'courseName' => '...',
    'courseDesc' => '...',
    'instructor' => '...',
    'inst_email' => '...'
]);
```

#### 3. Watched
Sent when a student interacts with video content.
```php
$xapi->Watched([
    'name' => '1234567890',
    'lessonUrl' => 'Video URL',
    'lessonName' => 'Video Title',
    'completion' => true,
    'duration' => 'PT5M',
    'courseId' => '...',
    // ... basic info
]);
```

#### 4. Attempted (Quiz/Assessment)
```php
$xapi->Attempted([
    'name' => '1234567890',
    'quizUrl' => '...',
    'attempNumber' => '1',
    'scaled' => 0.9, // 0 to 1
    'raw' => 90,
    'min' => 0,
    'max' => 100,
    'completion' => true,
    'success' => true,
    // ... basic info
]);
```

#### 5. Full Statement List
The library supports: `Registered`, `Initialized`, `Watched`, `CompletedLesson`, `CompletedUnit`, `CompletedCourse`, `Progressed`, `Attempted`, `Earned`, `Rated`.

---

<a name="الوثائق-باللغة-العربية"></a>
## الوثائق باللغة العربية

### المميزات
- دعم كامل لكافة التفاعلات المطلوبة من NELC.
- مرونة عالية: جلب الإعدادات من الكونفيج أو تجاوزها لكل طلب.
- معالجة تلقائية لبيانات المتصفح ونظام التشغيل.

### الإعداد الأساسي

1. **التثبيت:** `composer require bzzix/laravel-lrs-package`
2. **النشر:** `php artisan vendor:publish --provider="Bzzix\LaravelLrsPackage\NelcXapiServiceProvider"`
3. **البيئة (.env):**
```env
LRS_ENDPOINT=https://your-lrs-endpoint.com/xapi/statements
LRS_USERNAME=your_key
LRS_PASSWORD=your_secret
```

### مرجع الاستخدام (الحالات)

#### 1. التسجيل في دورة (Registered)
```php
$xapi->Registered([
    'name' => '1234567890', // رقم الهوية الوطنية للطالب
    'email' => 'student@email.com',
    'courseId' => 'رابط الدورة',
    'courseName' => 'اسم الدورة',
    'courseDesc' => 'وصف الدورة',
    'instructor' => 'اسم المحاضر',
    'inst_email' => 'بريد المحاضر',
    'duration' => 'PT10H', 
    'learnerFullName' => 'الاسم الرباعي',
    'learnerMobileNo' => '+966...',
    'learnerNationality' => 'Saudi',
    'dateOfBirth' => 'YYYY-MM-DD'
]);
```

#### 2. حل اختبار (Attempted)
```php
$xapi->Attempted([
    'name' => '1234567890',
    'quizUrl' => 'رابط الاختبار',
    'attempNumber' => '1',
    'scaled' => 0.9,
    'raw' => 90,
    'min' => 0,
    'max' => 100,
    'completion' => true,
    'success' => true,
    'courseId' => '...',
    'courseName' => '...',
    // ... البيانات الأساسية
]);
```

---

## Support & Contact | الدعم والتواصل
- **Email:** [info@bzzix.com](mailto:info@bzzix.com)
- **Website:** [bzzix.com](https://bzzix.com)
- **WhatsApp:**
  - [+20 10 62332549](https://wa.me/201062332549)
  - [+20 1000944804](https://wa.me/201000944804)

Developed by **Bzzix** Team.
"# laravel-lrs-package" 
