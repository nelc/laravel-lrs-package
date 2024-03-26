<!DOCTYPE html>
<html lang="{{ App::getLocale() }}" dir="{{ App::getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>NELC xAPI</title>
    @NelcXapiStyle
</head>
<body class="bg-light">
    <div class="container">
        <h2>تجربة الربط التقني مع المركز الوطني للتعليم الإلكتروني</h2>
        <p>برجاء التواصل مع المركز الوطني NELC للحصول على ال Endpoins الخاص بكم ثم اتبع الخطوات التالية:</p>
        <ul>
            <li>الذهاب إلى الملف <i>config/lrs-nelc-xapi.php</i></li>
            <li>قم باضافة البيانات التي تم الحصول عليها من المركز الوطني كما موضح في الملف</li>
            <li>تأكد من نجاح عملية الربط من هذه الصفحة</li>
            <li>قم بربط ال statments مع الدورات الخاصة بكم</li>
        </ul>

        @if ($errors->any())
        <div class="alert alert-warning fade show" role="alert">
        {{ __('The given data was invalid.') }}
        </div>
        @endif
    

        <form action="{{ route('lrs-nelc-xapi.validate_base_route') }}" method="POST">
            @csrf
            <div class="form-group mb-3">
                <label for="exampleFormControlInput1">{{ __('Endpoint') }}</label>
                <input type="text" class="form-control" name="xapi_endpoint" value="{{ config('lrs-nelc-xapi.endpoint') }}" disabled>
            </div>

            <div class="form-group mb-3">
                <label for="exampleFormControlInput1">{{ __('Key ') }}</label>
                <input type="text" class="form-control" name="xapi_key" value="{{ config('lrs-nelc-xapi.key') }}" disabled>
            </div>

            <div class="form-group mb-3">
                <label for="exampleFormControlSelect2">{{ __('Select statement') }}</label>
                <select name="xapi_statement" multiple class="form-control" id="exampleFormControlSelect2">
                    <option value="registered" {{ Session::get('st') && Session::get('st') === 'registered' ? 'selected' : '' }}>{{ __('Registered') }}</option>
                    <option value="initialized" {{ Session::get('st') && Session::get('st') === 'initialized' ? 'selected' : '' }}>{{ __('Initialized') }}</option>
                    <option value="watched" {{ Session::get('st') && Session::get('st') === 'watched' ? 'selected' : '' }}>{{ __('Watched') }}</option>
                    <option value="completed_lesson" {{ Session::get('st') && Session::get('st') === 'completed_lesson' ? 'selected' : '' }}>{{ __('Lesson completed') }}</option>
                    <option value="completed_unit" {{ Session::get('st') && Session::get('st') === 'completed_unit' ? 'selected' : '' }}>{{ __('Unit completed') }}</option>
                    <option value="progressed" {{ Session::get('st') && Session::get('st') === 'progressed' ? 'selected' : '' }}>{{ __('Progressed') }}</option>
                    <option value="attempted" {{ Session::get('st') && Session::get('st') === 'attempted' ? 'selected' : '' }}>{{ __('Attempted') }}</option>
                    <option value="completed_course" {{ Session::get('st') && Session::get('st') === 'completed_course' ? 'selected' : '' }}>{{ __('Course completed') }}</option>
                    <option value="earned" {{ Session::get('st') && Session::get('st') === 'earned' ? 'selected' : '' }}>{{ __('Earned') }}</option>
                    <option value="rated" {{ Session::get('st') && Session::get('st') === 'rated' ? 'selected' : '' }}>{{ __('Rated') }}</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
        </form>

        @if ($message = Session::get('success'))

        <h2>{{ Session::get('st') ?? 'Unknown' }}</h2>
            <div class="alert alert-success alert-dismissible fade show my-4" role="alert">
                <p>"status":{{ $message['status'] }}, "message":{{ $message['message'] }}</p>
                <p>{!! $message['body'] !!}</p>
            </div>

        @endif

    </div>
    @NelcXapiScript
</body>
</html>