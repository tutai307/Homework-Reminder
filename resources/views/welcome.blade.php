<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Homework-Reminder - Hệ thống nhắc bài tập thông minh</title>

    <!-- Favicons -->
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #0d6efd;
            --primary-soft: rgba(13, 110, 253, 0.05);
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --bg-light: #f8fafc;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
            background-color: #fff;
            scroll-behavior: smooth;
        }

        /* Navbar */
        .navbar {
            padding: 1.25rem 0;
            transition: all 0.3s ease;
            background: white;
        }

        .navbar.scrolled {
            padding: 0.75rem 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
            display: flex;
            align-items: center;
        }

        .nav-link {
            font-weight: 500;
            color: var(--text-dark) !important;
            margin: 0 0.5rem;
            transition: color 0.2s;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }

        /* Hero Section */
        .hero-section {
            padding: 100px 0 80px;
            background: linear-gradient(180deg, var(--primary-soft) 0%, #fff 100%);
            overflow: hidden;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            color: var(--text-dark);
        }

        .hero-description {
            font-size: 1.25rem;
            color: var(--text-muted);
            margin-bottom: 2.5rem;
            max-width: 600px;
        }

        .hero-image-container {
            position: relative;
        }

        .hero-image-container img {
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
        }

        /* Feature Section */
        .section-padding {
            padding: 100px 0;
        }

        .section-title {
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .feature-card {
            padding: 2.5rem;
            border-radius: 20px;
            border: 1px solid #f1f5f9;
            background: #fff;
            height: 100%;
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.05);
            border-color: var(--primary-soft);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: var(--primary-soft);
            color: var(--primary-color);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }

        /* Audience Section */
        .audience-section {
            background-color: var(--bg-light);
        }

        .audience-card {
            background: #fff;
            padding: 2rem;
            border-radius: 20px;
            height: 100%;
        }

        .audience-highlight {
            color: var(--primary-color);
            font-weight: 600;
        }

        /* CTA Section */
        .cta-section {
            background-color: var(--primary-color);
            color: #fff;
            border-radius: 30px;
            padding: 60px;
            margin-bottom: 80px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.8rem 2rem;
            font-weight: 600;
            border-radius: 12px;
        }

        .btn-outline-primary {
            border-color: var(--primary-color);
            color: var(--primary-color);
            padding: 0.8rem 2rem;
            font-weight: 600;
            border-radius: 12px;
        }

        .icon-circle-sm {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .x-small {
            font-size: 0.75rem;
        }

        .clickable-img {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .clickable-img:hover {
            transform: scale(1.02);
            filter: brightness(0.9);
        }

        #imageModal .modal-content {
            background-color: transparent;
            border: none;
        }
        
        #imageModal .modal-body {
            padding: 0;
        }

        #imageModal img {
            border-radius: 15px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
            max-height: 90vh;
            object-fit: contain;
        }

        /* Footer */
        footer {
            background-color: #fff;
            border-top: 1px solid #f1f5f9;
            padding: 60px 0 30px;
        }

        /* Animations */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease-out;
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        @media (max-width: 991.98px) {
            .hero-title {
                font-size: 2.5rem;
            }
            .cta-section {
                padding: 40px 20px;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-journal-bookmark-fill me-2"></i>HW Reminder
            </a>
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navLinks">
                <i class="bi bi-list fs-1"></i>
            </button>
            <div class="collapse navbar-collapse" id="navLinks">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#intro">Giới thiệu</a></li>
                    <li class="nav-item"><a class="nav-link" href="#features">Tính năng</a></li>
                    <li class="nav-item"><a class="nav-link" href="#audience">Đối tượng</a></li>
                    <li class="nav-item ms-lg-4 mt-3 mt-lg-0">
                        <a href="{{ route('login') }}" class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section" id="intro">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <span class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill mb-3 fw-semibold">Giao bài tập thông minh</span>
                    <h1 class="hero-title">Nhắc bài tập chuyên nghiệp, giảm áp lực quên bài.</h1>
                    <p class="hero-description">Giải pháp quản lý bài tập hằng ngày giúp học sinh tự giác, giáo viên nhàn nhã và phụ huynh yên tâm hơn mỗi tối.</p>
                    <div class="d-flex flex-column flex-sm-row gap-3">
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg">Bắt đầu ngay</a>
                        <a href="#features" class="btn btn-outline-primary btn-lg">Tìm hiểu thêm</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image-container">
                        <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=800&q=80" alt="Học sinh sử dụng hệ thống" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="section-padding" id="features">
        <div class="container">
            <div class="text-center mb-5 reveal">
                <h2 class="section-title">Tính năng cốt lõi</h2>
                <p class="text-muted">Được thiết kế tinh gọn để đáp ứng nhu cầu thực tế của lớp học.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-3 reveal">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="bi bi-calendar-check"></i></div>
                        <h5>Quản lý theo ngày</h5>
                        <p class="text-muted small mb-0">Theo dõi bài tập hằng ngày theo lịch học cụ thể của từng lớp.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 reveal">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="bi bi-robot"></i></div>
                        <h5>Nhập liệu bằng AI</h5>
                        <p class="text-muted small mb-0">Tự động trích xuất bài tập từ văn bản thô giúp tiết kiệm thời gian.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 reveal">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="bi bi-chat-left-dots"></i></div>
                        <h5>Nhắc bài qua Zalo</h5>
                        <p class="text-muted small mb-0">Tạo mẫu tin nhắn chuyên nghiệp gửi vào nhóm lớp chỉ với 1 click.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 reveal">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="bi bi-lightning-charge"></i></div>
                        <h5>Kế hoạch học tập</h5>
                        <p class="text-muted small mb-0">AI tư vấn lộ trình làm bài tập tối ưu dựa trên độ khó và hạn nộp.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Product Showcase Section -->
    <section class="section-padding bg-light" id="showcase">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-5 mb-5 mb-lg-0 reveal">
                    <h2 class="section-title">Giao diện trực quan</h2>
                    <p class="text-muted mb-4">Khám phá các màn hình quản lý bài tập và lịch học được tối ưu hóa cho giáo viên và ban cán sự lớp.</p>
                    <div class="list-group list-group-flush bg-transparent">
                        <div class="list-group-item bg-transparent border-0 px-0 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle-sm bg-primary text-white me-3"><i class="bi bi-1-circle"></i></div>
                                <div>
                                    <h6 class="fw-bold mb-0">Dashboard Tổng quan</h6>
                                    <p class="text-muted small mb-0">Quản lý toàn bộ lớp học trên một màn hình.</p>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item bg-transparent border-0 px-0 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle-sm bg-primary text-white me-3"><i class="bi bi-2-circle"></i></div>
                                <div>
                                    <h6 class="fw-bold mb-0">Lịch học chi tiết</h6>
                                    <p class="text-muted small mb-0">Hiện thị bài tập theo dòng thời gian.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7 reveal">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                                <img src="{{ asset('storage/images/screenshot-main.png') }}" class="img-fluid clickable-img" alt="Giao diện chính" onerror="this.src='https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80'">
                                <div class="card-body p-2 text-center bg-white">
                                    <span class="text-muted x-small">Màn hình điều khiển chính</span>
                                </div>
                            </div>
                        </div>
                        <!-- <div class="col-5">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                                <img src="{{ asset('storage/images/screenshot-mobile-11.png') }}" class="img-fluid clickable-img" alt="Giao diện Mobile 1" onerror="this.src='https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?auto=format&fit=crop&w=400&q=80'">
                                <div class="card-body p-2 text-center bg-white">
                                    <span class="text-muted x-small">Giao diện Mobile</span>
                                </div>
                            </div>
                        </div> -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                                <img src="{{ asset('storage/images/screenshot-mobile-2.png') }}" class="img-fluid clickable-img" alt="Giao diện Mobile 2" onerror="this.src='https://images.unsplash.com/photo-1555774698-0b77e0d5fac6?auto=format&fit=crop&w=400&q=80'">
                                <div class="card-body p-2 text-center bg-white">
                                    <span class="text-muted x-small">Nhắc bài qua Zalo</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Audience Section -->
    <section class="section-padding" id="audience">
        <div class="container">
            <div class="text-center mb-5 reveal">
                <h2 class="section-title">Phù hợp với tất cả mọi người</h2>
                <p class="text-muted">Hệ sinh thái kết nối Gia đình và Nhà trường hiệu quả hơn.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4 reveal">
                    <div class="audience-card shadow-sm">
                        <h5 class="fw-bold mb-3"><i class="bi bi-mortarboard me-2 text-primary"></i>Học sinh</h5>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><i class="bi bi-check2-circle me-2 text-success"></i>Không lo quên bài tập</li>
                            <li class="mb-2"><i class="bi bi-check2-circle me-2 text-success"></i>Quản lý thời gian hiệu quả</li>
                            <li><i class="bi bi-check2-circle me-2 text-success"></i>Tăng tính tự giác học tập</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-4 reveal">
                    <div class="audience-card shadow-sm">
                        <h5 class="fw-bold mb-3"><i class="bi bi-briefcase me-2 text-primary"></i>Giáo viên / BSC</h5>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><i class="bi bi-check2-circle me-2 text-success"></i>Giao bài tập nhanh chóng</li>
                            <li class="mb-2"><i class="bi bi-check2-circle me-2 text-success"></i>Thống kê bài tập rõ ràng</li>
                            <li><i class="bi bi-check2-circle me-2 text-success"></i>Chuyên nghiệp hóa việc nhắc bài</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-4 reveal">
                    <div class="audience-card shadow-sm">
                        <h5 class="fw-bold mb-3"><i class="bi bi-house-door me-2 text-primary"></i>Phụ huynh</h5>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><i class="bi bi-check2-circle me-2 text-success"></i>Nắm bắt kịp thời bài tập của con</li>
                            <li class="mb-2"><i class="bi bi-check2-circle me-2 text-success"></i>Yên tâm hơn về việc học</li>
                            <li><i class="bi bi-check2-circle me-2 text-success"></i>Dễ dàng đôn đốc con cái</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA Section -->
    <div class="container">
        <section class="cta-section text-center reveal">
            <h2 class="fw-bold mb-4">Sẵn sàng để chuyên nghiệp hóa quản lý lớp học?</h2>
            <p class="mb-5 opacity-75">Bắt đầu trải nghiệm Homework-Reminder hoàn toàn miễn phí ngay hôm nay.</p>
            <a href="{{ route('login') }}" class="btn btn-light btn-lg px-5 py-3 fw-bold text-primary rounded-pill">Truy cập ngay</a>
        </section>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h5 class="fw-bold text-primary mb-3">Homework-Reminder</h5>
                    <p class="text-muted small">Hệ thống nhắc bài tập cá nhân và tập thể giúp tối ưu hóa việc học tập hằng ngày của học sinh Việt Nam.</p>
                </div>
                <div class="col-lg-3 mb-4 mb-lg-0">
                    <h6 class="fw-bold mb-3">Liên kết</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="#intro" class="text-muted text-decoration-none">Giới thiệu</a></li>
                        <li class="mb-2"><a href="#features" class="text-muted text-decoration-none">Tính năng</a></li>
                        <li><a href="{{ route('login') }}" class="text-muted text-decoration-none">Đăng nhập</a></li>
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h6 class="fw-bold mb-3">Hỗ trợ</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Hướng dẫn sử dụng</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Câu hỏi thường gặp</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4 text-muted opacity-25">
            <div class="text-center text-muted small">
                &copy; {{ date('Y') }} Homework-Reminder. Tất cả quyền được bảo lưu.
            </div>
        </div>
    </footer>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-body text-center p-3">
                    <img src="" id="modalImage" class="img-fluid" alt="Phóng to">
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Navbar scrolled state
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                document.querySelector('.navbar').classList.add('scrolled');
            } else {
                document.querySelector('.navbar').classList.remove('scrolled');
            }
        });

        // Reveal animation on scroll
        function reveal() {
            var reveals = document.querySelectorAll(".reveal");
            for (var i = 0; i < reveals.length; i++) {
                var windowHeight = window.innerHeight;
                var elementTop = reveals[i].getBoundingClientRect().top;
                var elementVisible = 150;
                if (elementTop < windowHeight - elementVisible) {
                    reveals[i].classList.add("active");
                }
            }
        }
        window.addEventListener("scroll", reveal);
        // Trigger reveal for elements in view on load
        reveal();

        // Image Modal Logic
        const imageModalElement = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');
        
        if (imageModalElement) {
            const imageModal = new bootstrap.Modal(imageModalElement);

            document.querySelectorAll('.clickable-img').forEach(img => {
                img.addEventListener('click', function() {
                    modalImage.src = this.src;
                    imageModal.show();
                });
            });
        }
    </script>
</body>
</html>
