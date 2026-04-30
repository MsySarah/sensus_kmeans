<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Monitoring BPS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7f6; display: flex; align-items: center; height: 100vh; }
        .login-card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card login-card p-4">
                <div class="text-center mb-4">
                    <h4 class="fw-bold text-dark">LOGIN SISTEM</h4>
                    <small class="text-muted">Monitoring SE BPS Sumsel</small>
                </div>
                
                @if($errors->any())
                    <div class="alert alert-danger p-2 small text-center">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Masuk Sebagai</label>
                        <select name="role" class="form-select" required>
                            <option value="">-- Pilih Peran --</option>
                            <option value="admin">Administrator</option>
                            <option value="pml">PML (Pemeriksa Lapangan)</option>
                            <option value="koseka">Koseka</option>
                            <option value="pengawas_kab">Pengawas Kabupaten</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Username</label>
                        <input type="text" name="username" class="form-control" value="{{ old('username') }}" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn btn-dark w-100 fw-bold">MASUK</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>