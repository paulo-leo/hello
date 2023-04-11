<style>
     /* Estilo do rodapé */
     footer {
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 60px;
      background-color: #f8f9fa;
      padding: 16px;
      text-align: center;
    }
</style>
<footer class="footer bg-light">
  <div class="container">
    <div class="row">
      <div class="col-lg-12">
        <p>&copy; {{date('Y')}} {{env('APP_NAME')}} | Todos os direitos reservados.</p>
      </div>
    </div>
  </div>
</footer>