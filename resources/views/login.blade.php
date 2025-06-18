<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login</title>

  {{-- Bootstrap CSS desde CDN --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

  {{-- Tu CSS personalizado en public/login.css --}}
  <link rel="stylesheet" href="{{ asset('login.css') }}" />

  {{-- Font Awesome para el ícono del logo (opcional) --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>

<section class="vh-100">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6 text-black">

        <div class="px-5 ms-xl-4">
          <i class="fas fa-crow fa-2x me-3 pt-5 mt-xl-4" style="color: #709085;"></i>
          <span class="h1 fw-bold mb-0">Logo</span>
        </div>

        <div class="d-flex align-items-start h-custom-2 px-5 ms-xl-4 mt-4 pt-4">

          <form method="POST" action="{{ route('veterinarian.login.submit') }}"style="width: 23rem;">
            @csrf

            <h3 class="fw-normal mb-3 pb-3" style="letter-spacing: 1px;">Iniciar Sesión</h3>

            <div class="form-outline mb-4">
              <input type="email" name="email" id="email" class="form-control form-control-lg" required />
              <label class="form-label" for="email">Correo electrónico</label>
            </div>

            <div class="form-outline mb-4">
              <input type="password" name="password" id="password" class="form-control form-control-lg" required />
              <label class="form-label" for="password">Contraseña</label>
            </div>

            <div class="pt-1 mb-4">
              <button class="btn btn-lg btn-block mi-boton" type="submit">Acceder</button>
            </div>

            

          </form>

        </div>

      </div>
      <div class="col-sm-6 px-0 d-none d-sm-block">
        <img src="https://i.pinimg.com/736x/d2/aa/1b/d2aa1b03e25fcc07cb85be6589371022.jpg"
             alt="Login image" class="w-100 vh-100"
             style="object-fit: cover; object-position: left;" />
      </div>
    </div>
  </div>
</section>

</body>
</html>
