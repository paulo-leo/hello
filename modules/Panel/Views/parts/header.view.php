<header>
<nav class="navbar fixed-top navbar-expand-lg navbar-light bg-light shadow">
  <div class="container-fluid">
    <a class="navbar-brand" href="{{url('panel')}}">{{env('APP_NAME','Panel')}}</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarText">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="#">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Features</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Pricing</a>
        </li>
      </ul>
      <span class="navbar-text">
       
      <div class="btn-group">
  <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
    Opções
  </button>
  <ul class="dropdown-menu dropdown-menu-end">
    <li><button class="dropdown-item" type="button">{{session()->get('name')}}</button></li>
    <li><button class="dropdown-item" type="button">Another action</button></li>
    <li><hr class="dropdown-divider"></li>
    <li><button class="dropdown-item text-danger" type="button">Encerra sessão</button></li>
  </ul>
</div>

      </span>
    </div>
  </div>
</nav>
  </header>