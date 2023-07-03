<header>
<nav class="navbar fixed-top navbar-expand-lg navbar-light bg-white shadow" style="padding:0px">
  <div class="container-fluid">
    <a class="navbar-brand" href="{{url('panel')}}">{{env('APP_NAME','Panel')}}</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarText">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">

        {if auth('admin')}
           <li class="nav-item">
              <a class="nav-link" href="{{url('panel/users')}}">Usuários</a>
           </li>
           <li class="nav-item">
             <a class="nav-link border border-success" href="{{url('panel/users/create')}}">Adicionar usuário</a>
           </li>
        {/if}

      </ul>
      <span class="navbar-text">
       
      <div class="btn-group">
  <button type="button" class="btn dropdown-toggle p-1" data-bs-toggle="dropdown" aria-expanded="false">
  <i class="bi-person-circle"></i>
      
  </button>
  <ul class="dropdown-menu dropdown-menu-end">
    <li><b class="dropdown-item">{{session()->get('name')}}</b></li>
    <li><hr class="dropdown-divider"></li>
    <li><button class="dropdown-item" type="button">
    <i class="bi-person-fill-gear text-primary"></i> Configurações</button></li>
    <li>
      <a href="{{url('logout')}}" class="dropdown-item"><i class="bi-box-arrow-right text-danger"></i> Encerra sessão</a>
    </li>
  </ul>
</div>

      </span>
    </div>
  </div>
</nav>
  </header>