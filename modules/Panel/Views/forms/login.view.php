{include 'layout.head'}
<body>
    <div class="container"> 
        <div class="row justify-content-center mt-5">
            <div class="col-lg-4">
                <div class="card shadow">
                    <div class="card-header bg-white">
                        <h4 class="mb-0">
                        <img src="{{url('img/logo.jpeg')}}" width="32">  
                         Panel | {{env('APP_NAME')}}</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{url('login')}}" method="POST">
                            {{csrf_field}} 
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" id="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Senha</label>
                                <input type="password" name="password" id="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Entrar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
{include 'layout.footer'}
</body>
</html>
