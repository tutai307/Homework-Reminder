<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Homework Reminder System')</title>

    <!-- Favicons -->
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#0d6efd">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <style>
        body {
            background-color: #f0f2f5;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        
        .navbar {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        /* Custom shadows and borders for cards to give a premium feel using Utilities */
        .card {
            border: none;
            transition: all 0.3s cubic-bezier(.25,.8,.25,1);
        }
        
        .card:hover {
            box-shadow: 0 14px 28px rgba(0,0,0,0.1), 0 10px 10px rgba(0,0,0,0.08) !important;
        }

        /* Soft background colors for badges using Bootstrap variables if possible, or simple CSS if not available in standard utility */
        .badge-soft-primary {
            background-color: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
        }
        
        .badge-soft-success {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
        }

        .page-header {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        /* Icon Circle Utilities */
        .icon-circle {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .icon-circle-sm {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .icon-circle-lg {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        /* Preloader Styles */
        #preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(5px);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: opacity 0.3s ease;
        }

        .loader {
            width: 48px;
            height: 48px;
            border: 5px solid #0d6efd;
            border-bottom-color: transparent;
            border-radius: 50%;
            display: inline-block;
            box-sizing: border-box;
            animation: rotation 1s linear infinite;
        }

        @keyframes rotation {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">
    <!-- Preloader -->
    <div id="preloader" style="display: none;">
        <div class="loader"></div>
    </div>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ Auth::check() ? (Auth::user()->isAdmin() ? route('admin.dashboard') : route('teacher.daily-homework.index')) : route('login') }}">
                <i class="bi bi-journal-bookmark-fill me-2"></i>Homework Reminder
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    @auth
                        @if(Auth::user()->isAdmin())
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.dashboard') }}">
                                    <i class="bi bi-speedometer2 me-1"></i>Bảng điều khiển
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-gear me-1"></i>Quản lý
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.classes.index') }}">
                                            <i class="bi bi-people-fill me-2"></i>Lớp học
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.subjects.index') }}">
                                            <i class="bi bi-book-fill me-2"></i>Môn học
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.users.index') }}">
                                            <i class="bi bi-people me-2"></i>Người dùng
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.roles.index') }}">
                                            <i class="bi bi-shield-check me-2"></i>Vai trò
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.permissions.index') }}">
                                            <i class="bi bi-key me-2"></i>Quyền
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        @if(Auth::user()->canCreateTimetable())
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('teacher.timetables.index') }}">
                                    <i class="bi bi-calendar-week me-1"></i>Thời khóa biểu
                                </a>
                            </li>
                        @endif
                        @if(Auth::user()->canCreateHomework())
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('teacher.daily-homework.index') }}">
                                    <i class="bi bi-journal-text me-1"></i>Bài tập hàng ngày
                                </a>
                            </li>
                        @endif
                        @if(Auth::user()->isTeacher() && Auth::user()->classes()->count() > 0)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('teacher.class-monitor.index') }}">
                                    <i class="bi bi-person-badge me-1"></i>Ban cán sự
                                </a>
                            </li>
                        @endif
                    @endauth
                </ul>
                <ul class="navbar-nav">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i>{{ Auth::user()->name }}
                                <!-- <span class="badge bg-light text-dark ms-2">{{ Auth::user()->role }}</span> -->
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Đăng nhập
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4 px-4">
        @yield('content')
    </div>

    <!-- jQuery (Required for Select2) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/vi.js"></script>
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/vn.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Preloader control
        const showLoader = () => $('#preloader').fadeIn('fast');
        const hideLoader = () => $('#preloader').fadeOut('fast');

        $(document).ready(function() {
            // Khởi tạo Select2
            const initSelect2 = () => {
                $('.select2:not(.select2-hidden-accessible)').each(function() {
                    $(this).select2({
                        theme: 'bootstrap-5',
                        language: 'vi',
                        width: '100%',
                        placeholder: $(this).attr('data-placeholder') !== undefined ? $(this).data('placeholder') : 'Chọn một tùy chọn',
                        allowClear: true
                    }).on('select2:unselecting', function() {
                        $(this).data('unselecting', true);
                    }).on('select2:opening', function(e) {
                        if ($(this).data('unselecting')) {
                            $(this).removeData('unselecting');
                            e.preventDefault();
                        }
                    });
                });
            };
            
            initSelect2();

            // Khởi tạo Flatpickr
            $('.datepicker').flatpickr({
                locale: 'vn',
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd/m/Y',
                allowInput: true
            });

            // Bootstrap Validation & Preloader
            $('.needs-validation').on('submit', function(event) {
                const form = this;
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                    hideLoader();
                } else {
                    showLoader();
                }
                $(form).addClass('was-validated');
            });

            // Tự động gán thông báo lỗi cho validation
            $('.needs-validation input, .needs-validation select, .needs-validation textarea').on('invalid', function(e) {
                e.preventDefault();
                
                let feedback = $(this).closest('.mb-3, .mb-4, td').find('.invalid-feedback');
                if (feedback.length === 0) {
                    feedback = $('<div class="invalid-feedback"></div>');
                    $(this).after(feedback);
                }
                
                const label = $(this).closest('div').find('label').text().replace('*', '').trim() || 'Trường này';
                let message = this.validationMessage;

                if (this.validity.valueMissing) {
                    message = `Vui lòng nhập ${label.toLowerCase()}.`;
                }
                
                feedback.text(message).show();
            }).on('input change', function() {
                if (this.checkValidity()) {
                    $(this).closest('.mb-3, .mb-4, td').find('.invalid-feedback').text('').hide();
                }
            });

            // Global ajax loading
            $(document).ajaxStart(showLoader).ajaxStop(hideLoader);

            // Hiển thị thông báo từ session
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công!',
                    text: {!! json_encode(session('success')) !!},
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: {!! json_encode(session('error')) !!},
                    confirmButtonColor: '#0d6efd'
                });
            @endif

            @if($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi nhập liệu!',
                    html: '<ul class="text-start">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>',
                    confirmButtonColor: '#0d6efd'
                });
            @endif

            // Xác nhận xóa
            $('.delete-form').on('submit', function(e) {
                e.preventDefault();
                const form = this;
                
                Swal.fire({
                    title: 'Bạn có chắc chắn?',
                    text: "Hành động này không thể hoàn tác!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#0d6efd',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-trash me-1"></i>Có, xóa!',
                    cancelButtonText: 'Hủy',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        showLoader();
                        form.submit();
                    }
                });
            });
            
            // Đảm bảo preloader ẩn khi trang đã load xong
            hideLoader();
        });
    </script>
    
    @stack('scripts')
</body>
</html>
