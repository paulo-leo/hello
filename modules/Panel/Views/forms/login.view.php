{include 'layout.head'}
<body>
    <div class="container" id="app"> 
        <div class="row justify-content-center mt-5">
            <div class="col-lg-4">
                <div class="card shadow">
                    <div class="card-header bg-white">
                        <h4 class="mb-0">
                        <img src="{{url('img/logo.jpeg')}}" width="32">  
                         Panel | {{env('APP_NAME')}}</h4>
                    </div>
                    <div class="card-body">
                        <form @submit="login">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" id="email" class="form-control" required  v-model="email">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Senha</label>
                                <input type="password" name="password" id="password" class="form-control" required v-model="password">
                            </div>
                            <div v-show="msg" class="alert alert-danger" role="alert">@{{msg}}</div>

                            <button v-show="!loading" type="submit" class="btn btn-primary">Entrar</button>
                            <button v-show="loading" class="btn btn-primary" type="button" disabled>
                                <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>Validando dados de login...
                            </button>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
{include 'layout.footer'}
</body>
</html>
<script>
const app = Vue.createApp({
  data() {
    return{
      email: '',
      password: '',
      loading: false,
      msg:''
    }
  },
  methods: {
     
    login(event){

      event.preventDefault();
      this.loading = true;
      this.msg = '';

      axios.post('{{url("login")}}', {
          _token:'{{csrf_token()}}',
          email: this.email,
          password: this.password,
        })
        .then(response => {
          
           if(response.data.type == 'success'){
              window.location = '{{url("panel")}}'; 
           }else{
              this.msg = response.data.msg ?? '';
           }
             
        })
        .catch(error => {
      
          console.error(error);

        }).finally(() => {
            this.loading = false;
        });
    }
  }
});

app.mount('#app');

</script>