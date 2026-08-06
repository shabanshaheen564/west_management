{{-- LOGIN VIEW: resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Smart Waste Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family:'Inter',sans-serif; background:linear-gradient(135deg,#1a3a2a 0%,#0d2218 100%); min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .login-card { background:#fff; border-radius:20px; padding:2.5rem; width:100%; max-width:420px; box-shadow:0 20px 60px rgba(0,0,0,.4); }
        .login-logo { width:70px;height:70px;background:linear-gradient(135deg,#2d8a4e,#1f6438);border-radius:18px;display:flex;align-items:center;justify-content:center;font-size:2rem;color:#fff;margin:0 auto 1.5rem; }
        .form-control { border-radius:10px;border:1.5px solid #e2e8f0;padding:.75rem 1rem;font-size:.9rem; }
        .form-control:focus { border-color:#2d8a4e;box-shadow:0 0 0 3px rgba(45,138,78,.1); }
        .btn-login { background:linear-gradient(135deg,#2d8a4e,#1f6438);border:none;border-radius:10px;padding:.75rem;font-size:.95rem;font-weight:600;width:100%; }
        .input-group-text { background:#fff;border:1.5px solid #e2e8f0;border-right:none;border-radius:10px 0 0 10px; }
        .form-control.has-prefix { border-left:none;border-radius:0 10px 10px 0; }
        .lang-btn { font-size:.78rem;color:#888;text-decoration:none;padding:.25rem .6rem;border-radius:6px;transition:background .2s; }
        .lang-btn:hover { background:#f0f4f8;color:#333; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="text-center mb-4">
        <div class="login-logo"><i class="fas fa-recycle"></i></div>
        <h4 style="font-weight:800;color:#1a2e1e;">Smart Waste Management</h4>
        <p class="text-muted" style="font-size:.85rem;">نظام إدارة النفايات الذكي</p>
    </div>

    @if($errors->any())
    <div class="alert alert-danger" style="border-radius:10px;font-size:.85rem;">
        {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-600" style="font-size:.85rem;">Email</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope text-muted"></i></span>
                <input type="email" name="email" class="form-control has-prefix" value="{{ old('email','admin@waste.local') }}" required autofocus>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-600" style="font-size:.85rem;">Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                <input type="password" name="password" class="form-control has-prefix" required>
            </div>
        </div>
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="form-check"><input type="checkbox" name="remember" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember" style="font-size:.85rem;">Remember me</label></div>
        </div>
        <button type="submit" class="btn btn-login text-white"><i class="fas fa-sign-in-alt me-2"></i>Sign In</button>
    </form>

    <div class="text-center mt-4 d-flex justify-content-center gap-2">
        <a href="{{ url('locale/en') }}" class="lang-btn">🇬🇧 English</a>
        <a href="{{ url('locale/ar') }}" class="lang-btn">🇵🇸 العربية</a>
    </div>

    <div class="text-center mt-3" style="font-size:.75rem;color:#bbb;">
        Demo: admin@waste.local / Admin@123456
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
