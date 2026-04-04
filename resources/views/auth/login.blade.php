<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập | Homework Reminder Ecosystem</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Đăng nhập vào hệ thống Homework Reminder để quản lý bài tập, thời khóa biểu và theo dõi tiến độ học sinh.">
    <meta name="keywords" content="đăng nhập, login homework reminder, quản lý bài tập đăng nhập">
    <meta name="robots" content="index, follow">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="Đăng nhập | Homework Reminder Ecosystem">
    <meta property="og:description" content="Đăng nhập vào hệ thống Homework Reminder để quản lý bài tập, thời khóa biểu và theo dõi tiến độ học sinh.">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary">
    <meta property="twitter:title" content="Đăng nhập | Homework Reminder Ecosystem">
    <meta property="twitter:description" content="Đăng nhập vào hệ thống Homework Reminder để quản lý bài tập, thời khóa biểu và theo dõi tiến độ học sinh.">
    <!-- Favicons -->
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Google Fonts: Be Vietnam Pro -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- AOS (Animate on Scroll) -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --primary: #0284c7;
            --primary-dark: #0369a1;
            --light: #f0f9ff;
            --dark: #0f172a;
            --gradient: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
        }

        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            margin: 0;
        }

        /* Animated Background */
        body::before {
            content: '';
            position: absolute;
            width: 300%;
            height: 300%;
            top: -100%;
            left: -100%;
            background: radial-gradient(circle at 50% 50%, rgba(2, 132, 199, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 20% 80%, rgba(14, 165, 233, 0.08) 0%, transparent 40%),
                        radial-gradient(circle at 80% 20%, rgba(3, 105, 161, 0.05) 0%, transparent 40%);
            animation: bgMove 20s linear infinite;
            z-index: -1;
        }

        @keyframes bgMove {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .login-container {
            width: 100%;
            max-width: 900px;
            padding: 20px;
            z-index: 10;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 40px;
            padding: 0;
            box-shadow: 0 40px 100px -20px rgba(2, 132, 199, 0.15);
            overflow: hidden;
            display: flex;
            flex-direction: row;
        }

        .login-visual {
            background: var(--primary-light);
            width: 45%;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            border-right: 1px solid #e0f2fe;
        }

        .login-visual img {
            width: 100%;
            max-width: 280px;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(2, 132, 199, 0.1);
        }

        .login-form-side {
            width: 55%;
            padding: 3.5rem 3rem;
        }

        .brand-logo {
            width: 60px;
            height: 60px;
            background: var(--gradient);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 20px rgba(2, 132, 199, 0.2);
        }

        .login-title {
            font-weight: 800;
            font-size: 1.8rem;
            color: var(--dark);
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }

        .login-subtitle {
            color: #64748b;
            margin-bottom: 2.5rem;
            font-size: 0.95rem;
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .input-group-custom i.main-icon {
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            transition: 0.3s;
            z-index: 5;
        }

        .form-control-custom {
            height: 56px;
            padding-left: 3.5rem;
            padding-right: 3.5rem;
            border-radius: 16px;
            border: 2px solid #f1f5f9;
            background: #f8fafc;
            font-weight: 500;
            transition: all 0.3s ease;
            width: 100%;
        }

        .form-control-custom:focus {
            background: #fff;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.1);
            outline: none;
        }

        .form-control-custom:focus + i.main-icon {
            color: var(--primary);
        }

        .password-toggle {
            position: absolute;
            right: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            cursor: pointer;
            z-index: 10;
            transition: 0.3s;
            padding: 5px;
        }

        .password-toggle:hover {
            color: var(--primary);
        }

        .btn-login {
            height: 56px;
            background: var(--gradient);
            border: none;
            border-radius: 16px;
            color: white;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s;
            margin-top: 1rem;
            box-shadow: 0 10px 25px -5px rgba(2, 132, 199, 0.4);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(2, 132, 199, 0.5);
            color: white;
        }

        .checkbox-custom .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .back-link {
            margin-top: 2rem;
        }

        .back-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: 0.3s;
        }

        /* Responsive Fixes */
        @media (max-width: 992px) {
            .login-container { max-width: 500px; }
            .login-visual { display: none; }
            .login-form-side { width: 100%; padding: 3rem 2rem; }
            .brand-logo { margin: 0 auto 1.5rem; }
            .login-title, .login-subtitle { text-align: center; }
            .back-link { text-align: center; }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="glass-card" data-aos="zoom-in" data-aos-duration="1000">
            <!-- Sidebar Visual with Static Image -->
            <div class="login-visual">
                <img src="https://images.unsplash.com/photo-1434030216411-0b793f4b4173?auto=format&fit=crop&w=800&q=80" alt="Study Background" class="img-fluid rounded-4 shadow-sm mb-4">
                <h4 class="fw-bold text-primary-dark">Chào mừng đã quay trở lại!</h4>
                <p class="small text-muted px-4">Đăng nhập để giao bài tập và quản lý thời khoá biểu</p>
            </div>

            <!-- Login Form -->
            <div class="login-form-side">
                <div class="brand-logo">
                    <i class="bi bi-journal-bookmark-fill"></i>
                </div>
                <h1 class="login-title">Chào mừng trở lại!</h1>
                <p class="login-subtitle">Đăng nhập để điều hành lớp học thông minh</p>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email Field -->
                    <div class="input-group-custom">
                        <input type="email" 
                               class="form-control-custom @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               placeholder="Địa chỉ Email"
                               required 
                               autofocus>
                        <i class="bi bi-envelope-fill main-icon"></i>
                        @error('email')
                            <div class="invalid-feedback ps-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div class="input-group-custom">
                        <input type="password" 
                               class="form-control-custom @error('password') is-invalid @enderror" 
                               id="password" 
                               name="password" 
                               placeholder="Mật khẩu"
                               required>
                        <i class="bi bi-lock-fill main-icon"></i>
                        <span class="password-toggle" id="togglePassword">
                            <i class="bi bi-eye-fill"></i>
                        </span>
                        @error('password')
                            <div class="invalid-feedback ps-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="form-check checkbox-custom">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label small fw-medium" for="remember">Ghi nhớ đăng nhập</label>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-login">
                            Đăng nhập ngay <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </div>
                </form>

                <div class="back-link">
                    <a href="{{ url('/') }}"><i class="bi bi-arrow-left me-1"></i> Trở lại trang chủ</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Init AOS
        AOS.init();

        // Password Toggle Logic
        const togglePassword = document.querySelector('#togglePassword');
        const passwordInput = document.querySelector('#password');

        togglePassword.addEventListener('click', function (e) {
            // toggle the type attribute
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            // toggle the eye slash icon
            const icon = this.querySelector('i');
            icon.classList.toggle('bi-eye-fill');
            icon.classList.toggle('bi-eye-slash-fill');
        });

        // SweetAlert2 for errors 
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Đăng nhập thất bại!',
                text: '{{ session('error') }}',
                confirmButtonColor: '#0284c7',
                timer: 4000
            });
        @endif

        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Có lỗi xảy ra!',
                html: '<div class="text-start">@foreach($errors->all() as $error) • {{ $error }}<br> @endforeach</div>',
                confirmButtonColor: '#0284c7'
            });
        @endif
    </script>
</body>
</html>
