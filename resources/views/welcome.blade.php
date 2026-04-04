<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Homework-Reminder | Smart Academic Management</title>

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
            --primary: #0284c7; /* Blue - Xanh */
            --primary-light: #e0f2fe;
            --primary-dark: #0369a1;
            --secondary: #0ea5e9;
            --accent: #38bdf8;
            --dark: #0f172a;
            --light: #f8fafc;
            --glass: rgba(255, 255, 255, 0.85);
            --gradient: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            --shadow: 0 10px 30px -5px rgba(2, 132, 199, 0.1);
        }

        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            color: var(--dark);
            background-color: #fff;
            overflow-x: hidden;
        }

        /* Glassmorphism Classes */
        .glass-card {
            background: var(--glass);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: var(--shadow);
            border-radius: 24px;
        }

        /* Navbar Customization */
        .navbar {
            padding: 1.25rem 0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background: transparent;
        }

        .navbar.scrolled {
            padding: 0.75rem 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05);
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.4rem;
            color: var(--primary-dark) !important;
            letter-spacing: -0.5px;
        }

        .nav-link {
            font-weight: 600;
            color: var(--dark) !important;
            margin: 0 0.8rem;
            font-size: 0.95rem;
        }

        /* Hero Section */
        .hero {
            padding: 160px 0 100px;
            background: linear-gradient(180deg, #f0f9ff 0%, #ffffff 100%);
            position: relative;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            color: var(--dark);
        }

        .text-gradient {
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Value Prop Cards */
        .value-card {
            padding: 2.5rem;
            border-radius: 24px;
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid #e2e8f0;
            background: #fff;
        }

        .value-card:hover {
            transform: translateY(-10px);
            border-color: var(--primary-light);
            box-shadow: 0 20px 40px -10px rgba(2, 132, 199, 0.1);
        }

        .icon-circle {
            width: 60px;
            height: 60px;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }

        /* Section Headings */
        .section-padding { padding: 100px 0; }
        .section-tag {
            color: var(--primary);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-size: 0.85rem;
            display: block;
            margin-bottom: 1rem;
        }
        .section-title {
            font-weight: 800;
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
        }

        /* Tech Stack Icons */
        .tech-box {
            padding: 2rem;
            border-radius: 20px;
            background: #fff;
            text-align: center;
            border: 1px solid #f1f5f9;
            transition: all 0.3s;
        }
        .tech-box:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        .tech-box i { font-size: 2.5rem; margin-bottom: 1rem; display: block; }

        /* Buttons */
        .btn-custom {
            padding: 0.8rem 2rem;
            border-radius: 12px;
            font-weight: 700;
            transition: all 0.3s;
        }
        .btn-primary-custom {
            background: var(--gradient);
            color: white !important;
            border: none;
            box-shadow: 0 4px 15px rgba(2, 132, 199, 0.3);
            text-decoration: none;
        }
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(2, 132, 199, 0.4);
            color: white !important;
        }

        /* Footer */
        footer { background: #0f172a; color: #94a3b8; padding: 80px 0 40px; }
        .footer-title { color: white; font-weight: 700; margin-bottom: 1.5rem; }
        .footer-link { color: #94a3b8; text-decoration: none; display: block; margin-bottom: 0.75rem; transition: 0.3s; }
        .footer-link:hover { color: var(--primary-light); }

        @media (max-width: 768px) {
            .hero-title { font-size: 2.5rem; }
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top shadow-none" id="mainNav">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi- Mortarboard-fill me-2 text-primary"></i>HW REMINDER
            </a>
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navLinks">
                <i class="bi bi-list fs-1 text-primary"></i>
            </button>
            <div class="collapse navbar-collapse" id="navLinks">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#solution">Giải pháp</a></li>
                    <li class="nav-item"><a class="nav-link" href="#activities">Hoạt động</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tech">Công nghệ</a></li>
                    <li class="nav-item ms-lg-3 mt-3 mt-lg-0">
                        <a href="{{ route('login') }}" class="btn btn-primary-custom btn-custom">
                            Đăng nhập ngay
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <span class="section-tag">Smart Academic Management</span>
                    <h1 class="hero-title">Quản lý bài tập thông minh <br><span class="text-gradient">Hỗ trợ học tập</span></h1>
                    <p class="text-muted mb-4 fs-5">Homework Reminder giúp học sinh bứt phá, giáo viên tối ưu thời gian và phụ huynh luôn an tâm.</p>
                    <div class="d-flex gap-3">
                        <a href="{{ route('login') }}" class="btn btn-primary-custom btn-custom btn-lg">Bắt đầu miễn phí</a>
                        <a href="#solution" class="btn btn-outline-primary btn-custom btn-lg">Khám phá</a>
                    </div>
                </div>
                <div class="col-lg-6 mt-5 mt-lg-0" data-aos="zoom-in">
                    <div class="position-relative">
                        <img src="{{ asset('storage/images/test.jpg') }}" class="img-fluid rounded-4 shadow-lg p-2 bg-white" alt="Dashboard Preview" onerror="this.src='https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80'">
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Value Propositions (Giải pháp giá trị) -->
    <section class="section-padding" id="solution">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <span class="section-tag">Giá trị cốt lõi</span>
                <h2 class="section-title">Giải pháp toàn diện cho mọi đối tượng</h2>
            </div>
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="value-card">
                        <div class="icon-circle"><i class="bi bi-person-lines-fill"></i></div>
                        <h4 class="fw-bold">Cho Học sinh</h4>
                        <p class="text-muted">Không bao giờ bỏ lỡ bài tập. Thông tin hiển thị rõ ràng, khoa học giúp tập trung tối đa vào việc học.</p>
                        <ul class="list-unstyled small mt-3">
                            <li class="mb-2 text-primary-dark fw-medium"><i class="bi bi-check2 me-2 text-primary"></i>Không quên bài tập</li>
                            <li class="mb-2 text-primary-dark fw-medium"><i class="bi bi-check2 me-2 text-primary"></i>Thông tin bài tập rõ ràng</li>
                            <li class="mb-2 text-primary-dark fw-medium"><i class="bi bi-check2 me-2 text-primary"></i>Được AI tư vấn lộ trình học</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="value-card">
                        <div class="icon-circle"><i class="bi bi-shield-check"></i></div>
                        <h4 class="fw-bold">Cho Phụ huynh</h4>
                        <p class="text-muted">Theo dõi tiến độ học tập của con nhanh chóng qua Public Portal mà không cần quy trình đăng nhập phức tạp.</p>
                        <ul class="list-unstyled small mt-3">
                            <li class="mb-2 text-primary-dark fw-medium"><i class="bi bi-check2 me-2 text-primary"></i>Theo dõi qua Portal (No-Login)</li>
                            <li class="mb-2 text-primary-dark fw-medium"><i class="bi bi-check2 me-2 text-primary"></i>Cập nhật 24/7 tức thì</li>
                            <li class="mb-2 text-primary-dark fw-medium"><i class="bi bi-check2 me-2 text-primary"></i>Theo dõi thời khoá biểu của con</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="value-card">
                        <div class="icon-circle"><i class="bi bi-cpu"></i></div>
                        <h4 class="fw-bold">Hệ thống AI</h4>
                        <p class="text-muted">Hệ thống hỗ trợ học tập khoa học, tập trung nguồn lực thông tin. Dữ liệu chuẩn hóa giúp lớp học vận hành chuyên nghiệp.</p>
                        <ul class="list-unstyled small mt-3">
                            <li class="mb-2 text-primary-dark fw-medium"><i class="bi bi-check2 me-2 text-primary"></i>Tư vấn AI lộ trình học</li>
                            <li class="mb-2 text-primary-dark fw-medium"><i class="bi bi-check2 me-2 text-primary"></i>Giao bài thông qua prompt</li>
                            <li class="mb-2 text-primary-dark fw-medium"><i class="bi bi-check2 me-2 text-primary"></i>Quét thời khoá biểu nhanh bằng AI</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Key Activities (Hoạt động chính) -->
    <section class="section-padding bg-light" id="activities">
        <div class="container">
            <div class="row align-items-center mb-5">
                <div class="col-lg-6" data-aos="fade-right">
                    <span class="section-tag">Cách thức vận hành</span>
                    <h2 class="section-title">Nền tảng số hóa mọi <br>hoạt động lớp học</h2>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6" data-aos="fade-up">
                    <div class="glass-card p-4 h-100 border-0">
                        <i class="bi bi-calendar3 fs-2 text-primary mb-3 d-block"></i>
                        <h5 class="fw-bold">Quản lý TKB & Môn học</h5>
                        <p class="small text-muted mb-0">Hệ thống hóa môn học và lịch trình hàng tuần một cách trực quan, chính xác.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="glass-card p-4 h-100 border-0">
                        <i class="bi bi-journal-check fs-2 text-primary mb-3 d-block"></i>
                        <h5 class="fw-bold">Tổng hợp Bài tập</h5>
                        <p class="small text-muted mb-0">Thu thập bài tập hàng ngày tự động, bóc tách môn học và thời hạn nộp.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="glass-card p-4 h-100 border-0">
                        <i class="bi bi-people fs-2 text-primary mb-3 d-block"></i>
                        <h5 class="fw-bold">Quản lý Ban cán sự</h5>
                        <p class="small text-muted mb-0">Hỗ trợ Lớp trưởng, Lớp phó cùng tham gia quản trị và điều phối học tập.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="glass-card p-4 h-100 border-0">
                        <i class="bi bi-chat-dots fs-2 text-primary mb-3 d-block"></i>
                        <h5 class="fw-bold">Công cụ Nhắc bài</h5>
                        <p class="small text-muted mb-0">Soạn tin nhắn nhắc bài chuyên nghiệp để gửi vào các nhóm mạng xã hội lớp.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tech Stack (Tài nguyên chính) -->
    <section class="section-padding" id="tech">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5" data-aos="fade-right">
                    <span class="section-tag">Hạ tầng Công nghệ</span>
                    <h2 class="section-title">Nền tảng vững chắc đạt <br>tiêu chuẩn Enterprise</h2>
                    <p class="text-muted mb-4">Chúng tôi lựa chọn những công nghệ hàng đầu để đảm bảo tính sẵn sàng, bảo mật và tốc độ cho hệ thống giáo dục.</p>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-cpu text-primary"></i>
                                <span class="small fw-bold">Laravel 11</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-database text-primary"></i>
                                <span class="small fw-bold">MySQL</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-cloud text-primary"></i>
                                <span class="small fw-bold">Railway Cloud</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-link text-primary"></i>
                                <span class="small fw-bold">Railway App</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7" data-aos="fade-left">
                    <div class="row g-3 text-center">
                        <div class="col-6 col-md-3">
                            <div class="tech-box">
                                <i class="bi bi-pc-display"></i>
                                <span class="fw-bold small">Website</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="tech-box">
                                <i class="bi bi-grid-1x2"></i>
                                <span class="fw-bold small">Dashboard</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="tech-box">
                                <i class="bi bi-chat-square-dots"></i>
                                <span class="fw-bold small">SMS/Zalo</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="tech-box">
                                <i class="bi bi-infinity"></i>
                                <span class="fw-bold small">Railway</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-5">
                    <a class="navbar-brand text-white mb-4 d-block" href="#">
                        <i class="bi bi- Mortarboard-fill me-2"></i>HW REMINDER
                    </a>
                    <p class="small mb-4">Dự án chuyển đổi số giáo dục phục vụ cộng đồng học sinh, giáo viên và phụ huynh học sinh. Chúng tôi mong muốn đóng góp một phần nhỏ vào sự phát triển của giáo dục Việt Nam.</p>
                </div>
                <div class="col-lg-3 ms-auto text-lg-end">
                    <h6 class="footer-title">Kênh phân phối</h6>
                    <span class="footer-link">Public Class Portal</span>
                    <span class="footer-link">Admin Dashboard</span>
                    <span class="footer-link">Nhóm lớp cộng đồng</span>
                </div>
                <div class="col-lg-3 text-lg-end">
                    <h6 class="footer-title">Đối tác & Phân khúc</h6>
                    <span class="footer-link">Học sinh THCS-THPT</span>
                    <span class="footer-link">Giáo viên & Nhà trường</span>
                    <span class="footer-link">Phụ huynh nhận tin</span>
                </div>
            </div>
            <div class="border-top border-secondary mt-5 pt-4 text-center">
                <p class="small text-muted mb-0">&copy; {{ date('Y') }} Homework-Reminder Project. Phục vụ với sứ mệnh giáo dục không vì lợi nhuận.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });
        
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                document.getElementById('mainNav').classList.add('scrolled', 'shadow-sm');
            } else {
                document.getElementById('mainNav').classList.remove('scrolled', 'shadow-sm');
            }
        });

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({ behavior: 'smooth' });
            });
        });
    </script>
</body>
</html>
