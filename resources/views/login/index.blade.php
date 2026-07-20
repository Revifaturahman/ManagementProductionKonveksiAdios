<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AdiosApp</title>

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
                        Login
                    </h4>

                    @if ($errors->has('login'))
                        <div class="alert alert-danger py-2">
                            {{ $errors->first('login') }}
                        </div>
                    @endif

                    <form action="{{ route('login.post') }}" method="POST">
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
                                Password
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

                        <button
                            type="submit"
                            class="btn btn-primary w-100">
                            Login
                        </button>

                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('forgot-password') }}">
                            Lupa Sandi?
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