<!DOCTYPE html>
<html lang="{{ App::getLocale() }}" dir="{{ App::getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>NELC xAPI Integration Demo</title>
    @NelcXapiStyle
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Noto+Kufi+Arabic:wght@300;400;600;700&display=swap');
        
        :root {
            --primary: #4f46e5;
            --secondary: #ec4899;
            --success: #10b981;
            --dark: #1f2937;
            --light: #f8fafc;
        }

        body {
            font-family: 'Outfit', 'Noto Kufi Arabic', sans-serif;
            background: radial-gradient(circle at top right, #f1f5f9, #ffffff);
            color: var(--dark);
            min-height: 100vh;
            padding: 40px 0;
        }

        .main-card {
            border: none;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.05);
            overflow: hidden;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
        }

        .header-section {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 40px;
            text-align: center;
        }

        .header-section h1 {
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 1.8rem;
        }

        .form-section {
            padding: 40px;
        }

        .instruction-box {
            background: #f1f5f9;
            border-left: 4px solid var(--primary);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        
        [dir="rtl"] .instruction-box {
            border-left: none;
            border-right: 4px solid var(--primary);
        }

        .form-control, .form-select {
            border-radius: 12px;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2);
        }

        .result-card {
            border-radius: 16px;
            border: 2px solid var(--success);
            background: #f0fdf4;
            padding: 24px;
            margin-top: 30px;
        }

        .badge-status {
            background: var(--success);
            color: white;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        pre {
            background: #1e293b;
            color: #f8fafc;
            padding: 20px;
            border-radius: 12px;
            overflow-x: auto;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card main-card">
                    <div class="header-section">
                        <h1>{{ __('NELC xAPI Integration Demo') }}</h1>
                        <p class="mb-0 opacity-75">{{ __('Bzzix Laravel LRS Package Testing Interface') }}</p>
                    </div>

                    <div class="form-section">
                        <div class="instruction-box">
                            <h5 class="fw-bold mb-2">{{ __('Getting Started') }}</h5>
                            <p class="small mb-0">
                                {{ __('1. Configure your endpoint and keys in config/lrs-nelc-xapi.php or .env file.') }}<br>
                                {{ __('2. Select a statement type from the list below.') }}<br>
                                {{ __('3. Click submit to send a test request to the LRS.') }}
                            </p>
                        </div>

                        @if ($errors->any())
                        <div class="alert alert-danger rounded-3" role="alert">
                            {{ __('The given data was invalid.') }}
                        </div>
                        @endif

                        <form action="{{ route('lrs-nelc-xapi.validate_base_route') }}" method="POST">
                            @csrf
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label class="form-label fw-bold small text-muted">{{ __('LRS Endpoint') }}</label>
                                    <input type="text" class="form-control bg-light" value="{{ config('lrs-nelc-xapi.endpoint') ?: 'NOT CONFIGURED' }}" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted">{{ __('Client Key') }}</label>
                                    <input type="text" class="form-control bg-light" value="{{ config('lrs-nelc-xapi.key') ?: 'NOT CONFIGURED' }}" disabled>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">{{ __('Select Statement to Test') }}</label>
                                <select name="xapi_statement" class="form-select form-control-lg">
                                    <option value="registered" {{ Session::get('st') === 'registered' ? 'selected' : '' }}>📌 {{ __('Registered (Enrollment)') }}</option>
                                    <option value="initialized" {{ Session::get('st') === 'initialized' ? 'selected' : '' }}>🚀 {{ __('Initialized (Start)') }}</option>
                                    <option value="watched" {{ Session::get('st') === 'watched' ? 'selected' : '' }}>📺 {{ __('Watched (Video/Content)') }}</option>
                                    <option value="completed_lesson" {{ Session::get('st') === 'completed_lesson' ? 'selected' : '' }}>✅ {{ __('Lesson Completed') }}</option>
                                    <option value="completed_unit" {{ Session::get('st') === 'completed_unit' ? 'selected' : '' }}>📦 {{ __('Unit/Module Completed') }}</option>
                                    <option value="progressed" {{ Session::get('st') === 'progressed' ? 'selected' : '' }}>📈 {{ __('Progressed (Update)') }}</option>
                                    <option value="attempted" {{ Session::get('st') === 'attempted' ? 'selected' : '' }}>📝 {{ __('Attempted (Quiz/Test)') }}</option>
                                    <option value="completed_course" {{ Session::get('st') === 'completed_course' ? 'selected' : '' }}>🎓 {{ __('Course Completed') }}</option>
                                    <option value="earned" {{ Session::get('st') === 'earned' ? 'selected' : '' }}>🏆 {{ __('Earned (Certificate)') }}</option>
                                    <option value="rated" {{ Session::get('st') === 'rated' ? 'selected' : '' }}>⭐ {{ __('Rated (Review)') }}</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                                {{ __('Send xAPI Statement') }}
                            </button>
                        </form>

                        @if ($message = Session::get('success'))
                        <div class="result-card animate__animated animate__fadeIn">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="mb-0 fw-bold">{{ strtoupper(Session::get('st')) }}</h4>
                                <span class="badge-status">Status: {{ $message['status'] }}</span>
                            </div>
                            <div class="alert alert-light border shadow-sm">
                                <strong>Message:</strong> {{ $message['message'] }}
                            </div>
                            <h6 class="fw-bold mb-2">{{ __('LRS Response Body:') }}</h6>
                            <pre><code>{!! $message['body'] !!}</code></pre>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="text-center mt-4 text-muted small">
                    &copy; {{ date('Y') }} Bzzix Team. All rights reserved.
                </div>
            </div>
        </div>
    </div>
    @NelcXapiScript
</body>
</html>
