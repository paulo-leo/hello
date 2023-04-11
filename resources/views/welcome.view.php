{include 'layout.head'}
<body>
  {include 'layout.header'}
  <main class="container mt-4">
  <div class="container">
    <div class="jumbotron">
      <h1 class="display-4">Bem-vindo ao {{env('APP_NAME')}}!</h1>
      <hr class="my-4">
      <p class="lead">O microframework para desenvolvimento web rápido, desacoplado e escalável.</p>
      <a href="https://pauloleo.gitbook.io/hello/" target="_blank" class="btn btn-primary">Documentação</a>
    </div>
  </div>
  </main>
  {include 'layout.footer'}
</body>
</html>

