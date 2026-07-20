<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Sandi - AdiosApp</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center">

    <div class="col-md-5 col-lg-4">

        <div class="text-center mb-4">
            <h1 class="fw-bold">
                AdiosApp
            </h1>
        </div>

        <div class="card shadow-sm">

            <div class="card-body p-4">

                <h4 class="text-center mb-4">
                    Lupa Sandi
                </h4>

                @if(session('success'))
                    <div class="alert alert-success py-2">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->has('forgot'))
                    <div class="alert alert-danger py-2">
                        {{ $errors->first('forgot') }}
                    </div>
                @endif

                <form action="{{ route('forgot-password.post') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">
                            Username
                        </label>

                        <input
                            type="text"
                            name="username"
                            value="{{ old('username') }}"
                            class="form-control @error('username') is-invalid @enderror">

                        @error('username')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Pertanyaan
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="Berapa nomor telepon yang terdaftar pada akun Anda?"
                            readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Jawaban
                        </label>

                        <input
                            type="text"
                            name="phone"
                            value="{{ old('phone') }}"
                            class="form-control @error('phone') is-invalid @enderror">

                        @error('phone')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Password Baru
                        </label>

                        <input
                            type="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror">

                        @error('password')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Konfirmasi Password Baru
                        </label>

                        <input
                            type="password"
                            name="password_confirmation"
                            class="form-control">
                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary w-100">
                        Simpan Password Baru
                    </button>

                </form>

                <div class="text-center mt-3">
                    <a href="{{ route('login') }}">
                        Kembali ke Login
                    </a>
                </div>

            </div>

        </div>

        <div class="text-center mt-4 text-muted">
            © {{ date('Y') }} Konveksi Information System.
        </div>

    </div>

</div>

</body>

</html>