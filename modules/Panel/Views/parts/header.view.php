<header>
<nav class="navbar fixed-top navbar-expand-lg navbar-light bg-light shadow">
  <div class="container-fluid">
    <a class="navbar-brand" href="{{url('panel')}}">{{env('APP_NAME','Panel')}}</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarText">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">

        {if auth('admin')}
         <li class="nav-item">
           <a class="nav-link" href="#">Criar usuário</a>
         </li>
        {/if}

        <li class="nav-item">
          <a class="nav-link" href="#">Pricing</a>
        </li>
      </ul>
      <span class="navbar-text">
       
      <div class="btn-group">
  <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
      {{session()->get('first_name')}}
  </button>
  <ul class="dropdown-menu dropdown-menu-end">
    <li><button class="dropdown-item" type="button">Editar perfil</button></li>
    <li><hr class="dropdown-divider"></li>
    <li><a href="{{url('logout')}}" class="dropdown-item text-danger">Encerra sessão</a></li>
  </ul>
</div>

      </span>
    </div>
  </div>
</nav>
  </header>