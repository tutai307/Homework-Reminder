<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Không tìm thấy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: radial-gradient(circle at top, #f3f4ff 0%, #ffffff 45%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="card error-card">
                    <div class="card-body text-center p-5">
                        <div class="display-4 text-primary mb-3">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                        <h1 class="h3 fw-bold mb-3">Không tìm thấy trang</h1>
                        <p class="text-muted mb-4">
                            Trang bạn truy cập không tồn tại.
                        </p>
                        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                            <a href="{{ url('/') }}" class="btn btn-primary">
                                <i class="bi bi-house-door me-2"></i>Về trang chính
                            </a>
                            @auth
                                <a href="{{ route('teacher.daily-homework.index') }}" class="btn btn-outline-primary">
                                    <i class="bi bi-journal-text me-2"></i>Về Bài tập
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

