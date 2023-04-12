<header>
    <nav class="navbar navbar-expand-lg navbar-white shadow bg-white">
      <div class="container">
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar">
          <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand position-relative" href="{{url()}}">
          <img src="{{url('img/logo.jpeg')}}" width="32">
          <span style="position:relative;top:3px">{{env('APP_NAME')}}</span>
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
          Alpha
  </span>
        </a>
  
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item">
              <a class="nav-link" href="{{url()}}">Início</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{url('about')}}">Sobre</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel">
      <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="sidebarLabel">Menu</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <nav>
          <ul class="list-unstyled ps-0">
            <li><a href="#" class="nav-link px-0" href="{{url()}}">Início</a></li>
            <li><a href="{{url('about')}}" class="nav-link px-0">Sobre</a></li>
          </ul>
        </nav>
      </div>
    </div>
  </header>
  <style>
    .sidebar {
      position: fixed;
      top: 0;
      bottom: 0;
      left: 0;
      z-index: 1;
      padding: 48px 16px;
      background-color: #f8f9fa;
      overflow-x: hidden;
      transition: all 0.3s;
    }

    .sidebar nav {
      margin-bottom: 30px;
    }

    .sidebar a {
      display: block;
      color: #212529;
      font-size: 16px;
      font-weight: 500;
      padding: 8px 0;
      text-decoration: none;
      transition: all 0.3s;
    }

    .sidebar a:hover {
      color: #0d6efd;
    }

    .sidebar .close-btn {
      position: absolute;
      top: 16px;
      right: 16px;
      font-size: 24px;
      color: #212529;
      text-decoration: none;
      transition: all 0.3s;
    }

    .sidebar .close-btn:hover {
      color: #0d6efd;
    }

    @media (min-width: 992px) {
      .sidebar {
        display: none;
      }
    }

    /* Adiciona um espaço em branco no final do corpo para garantir que o rodapé seja fixado na parte inferior da página */
    body {
      margin-bottom: 60px;
    }

    .btn-purple {
  color: #fff !important;
  background-color: #6f42c1;
  border-color: #6f42c1;
}

.btn-purple:hover {
  color: #fff;
  background-color: #5a32b0;
  border-color: #4829a9;
}

a {
  color: #6f42c1 !important;
}

a:hover {
  color: #5a32b0;
  text-decoration: none;
}
  </style>