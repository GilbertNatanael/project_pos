<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
    <div class="card p-4 shadow" style="width: 400px;">
        <h4 class="mb-4 text-center">Login</h4>
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <form method="POST" action="{{ route('login.process') }}">
            @csrf
            <div class="mb-3">
                <label for="username" class="form-label">Pilih Username</label>
                <select class="form-select" id="username" name="username" required>
                    <option value="">-- Pilih Username --</option>
                    @foreach ($karyawans as $karyawan)
                        <option value="{{ $karyawan->username }}">{{ $karyawan->username }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Kata Sandi</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>
</body>
</html>
